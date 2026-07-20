<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class AttendanceController extends Controller
{
    /**
     * Handle intern check-in (absen masuk).
     */
    public function checkIn(Request $request)
    {
        $user = Auth::user();

        $now = Carbon::now();

        // [MODE TEST] Khusus akun yogi.sutana@gmail.com: gunakan waktu lokal HP yang dikirim client (param atau cookie)
        if ($user->email === 'yogi.sutana@gmail.com') {
            $testTime = $request->input('client_time') ?: $request->cookie('client_time');
            if ($testTime) {
                try {
                    $now = Carbon::parse($testTime)->timezone('Asia/Jakarta');
                    Log::info('[TEST MODE] check-in menggunakan waktu lokal HP', [
                        'user_id' => $user->id,
                        'source' => $request->has('client_time') ? 'request_param' : 'cookie',
                        'resolved_now' => $now->toDateTimeString(),
                    ]);
                } catch (\Exception $e) {
                    $now = Carbon::now();
                }
            }
        }

        // $today mengikuti tanggal dari $now (HP untuk test user, server untuk user biasa)
        $today = $now->copy()->startOfDay();

        $pembimbing = $user->pembimbing;
        $requirePhoto = true;
        if ($pembimbing) {
            if (!$pembimbing->require_photo_attendance_global && !$user->require_photo_attendance) {
                $requirePhoto = false;
            }
        } else {
            if (!$user->require_photo_attendance) {
                $requirePhoto = false;
            }
        }

        $request->validate([
            'koordinat' => ['required', 'regex:/^[-+]?[0-9]{1,2}(?:\.[0-9]+)?,\s*[-+]?[0-9]{1,3}(?:\.[0-9]+)?$/'],
            'foto' => $requirePhoto ? ['required', 'string', 'max:21000000'] : ['nullable', 'string', 'max:21000000'],
            'akurasi_gps' => ['sometimes', 'numeric', 'min:0', 'max:100'],
        ]);

        // Server-side Geofencing validation (multi-location aware)
        $locations = \App\Models\OfficeLocation::all();
        $koordinat = $request->input('koordinat');
        $akurasiGps = (double) $request->input('akurasi_gps', 0);
        $parts = explode(',', $koordinat);

        if (count($parts) !== 2) {
            return response()->json([
                'success' => false,
                'message' => 'Format koordinat tidak valid.',
            ], 400);
        }

        $userLat = (double) trim($parts[0]);
        $userLng = (double) trim($parts[1]);

        $allowed = false;
        $minDistance = null;
        $closestLocation = null;

        // If no locations configured, fallback to GeneralSettings coordinates
        if ($locations->isEmpty()) {
            $settings = app(\App\Settings\GeneralSettings::class);
            $officeLat = (double) $settings->latitude_kantor;
            $officeLng = (double) $settings->longitude_kantor;
            $radiusLimit = (double) $settings->radius_meter;

            $distance = $this->calculateDistance($userLat, $userLng, $officeLat, $officeLng);
            $minDistance = $distance;
            $closestLocation = (object)[
                'name' => 'Kantor Utama',
                'radius' => $radiusLimit
            ];

            if ($distance <= $radiusLimit || ($distance - $akurasiGps) <= $radiusLimit) {
                $allowed = true;
            }
        } else {
            foreach ($locations as $loc) {
                $distance = $this->calculateDistance($userLat, $userLng, (double)$loc->latitude, (double)$loc->longitude);
                if ($minDistance === null || $distance < $minDistance) {
                    $minDistance = $distance;
                    $closestLocation = $loc;
                }
                if ($distance <= (double)$loc->radius || ($distance - $akurasiGps) <= (double)$loc->radius) {
                    $allowed = true;
                }
            }
        }

        if (!$allowed) {
            $closestName = $closestLocation ? $closestLocation->name : 'Kantor';
            $closestRadius = $closestLocation ? $closestLocation->radius : 380;
            return response()->json([
                'success' => false,
                'message' => 'Absensi ditolak! Posisi Anda berada di luar radius kantor terdekat: ' . $closestName . ' (' . round($minDistance) . ' meter dari kantor, batas radius: ' . $closestRadius . ' meter).',
            ], 400);
        }

        // Double check if already checked in today
        // Gunakan $now->toDateString() agar konsisten dengan tanggal HP (test user)
        $todayAttendance = Attendance::where('user_id', $user->id)
            ->whereDate('tanggal', $now->toDateString())
            ->first();

        if ($todayAttendance) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah melakukan absen masuk hari ini.',
            ], 400);
        }

        // Process Base64 photo
        $fotoPath = null;
        $fotoData = $request->input('foto');
        if ($fotoData) {
            if (preg_match('/^data:image\/(\w+);base64,/', $fotoData, $type)) {
                $fotoData = substr($fotoData, strpos($fotoData, ',') + 1);
                $type = strtolower($type[1]); // png, jpg, jpeg

                if (!in_array($type, ['jpg', 'jpeg', 'png', 'webp'])) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Format foto tidak valid. Gunakan JPG, JPEG, PNG, atau WEBP.',
                    ], 400);
                }

                $fotoData = base64_decode($fotoData);

                if ($fotoData === false) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Dekode foto gagal.',
                    ], 400);
                }
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Data foto tidak valid.',
                ], 400);
            }

            // Save photo file
            $folderPath = 'attendance/' . $user->user_code;
            $randomSuffix = substr(uniqid(), -5);
            $fileName = 'selfie_masuk_' . $now->format('dmyHis') . '_' . $randomSuffix . '.' . $type;
            Storage::disk('public')->put($folderPath . '/' . $fileName, $fotoData);
            $fotoPath = 'storage/' . $folderPath . '/' . $fileName;
        } elseif ($requirePhoto) {
            return response()->json([
                'success' => false,
                'message' => 'Foto selfie wajib diunggah.',
            ], 400);
        }

        // Determine status based on schedule priority: specific date > day-of-week > GeneralSettings default

        $schedule = \App\Models\WorkSchedule::getScheduleForDate($now);

        // If today is a holiday, reject check-in
        if ($schedule && $schedule->is_holiday) {
            return response()->json([
                'success' => false,
                'message' => 'Hari ini adalah hari libur (' . ($schedule->keterangan ?? 'Libur') . '). Absensi tidak tersedia.',
            ], 400);
        }

        // Get the applicable jam masuk and late limit (batas absensi)
        $settings = app(\App\Settings\GeneralSettings::class);

        $jamMasukStr = ($schedule && $schedule->jam_masuk)
            ? $schedule->jam_masuk
            : $settings->jam_masuk;

        $limitStr = ($schedule && $schedule->batas_keterlambatan)
            ? $schedule->batas_keterlambatan
            : $settings->batas_keterlambatan;

        // 1. Check if past the late limit (batas absensi)
        $limitParts = explode(':', $limitStr);
        $limitHour = isset($limitParts[0]) ? (int) $limitParts[0] : 8;
        $limitMinute = isset($limitParts[1]) ? (int) $limitParts[1] : 15;
        $limitSecond = isset($limitParts[2]) ? (int) $limitParts[2] : 0;
        $limitTime = $today->copy()->setTime($limitHour, $limitMinute, $limitSecond);

        if ($now->greaterThan($limitTime)) {
            return response()->json([
                'success' => false,
                'message' => 'Batas waktu absensi masuk hari ini telah berakhir pada pukul ' . Carbon::parse($limitStr)->format('H:i') . '. Anda tidak dapat melakukan absensi masuk lagi.',
            ], 400);
        }

        // 2. Determine status based on jam masuk
        $masukParts = explode(':', $jamMasukStr);
        $masukHour = isset($masukParts[0]) ? (int) $masukParts[0] : 8;
        $masukMinute = isset($masukParts[1]) ? (int) $masukParts[1] : 0;
        $masukSecond = isset($masukParts[2]) ? (int) $masukParts[2] : 0;
        $masukTime = $today->copy()->setTime($masukHour, $masukMinute, $masukSecond);

        $status = $now->greaterThan($masukTime) ? 'Terlambat' : 'Hadir';

        // Create attendance record
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'tanggal' => $today->toDateString(),
            'jam_masuk' => $now->toTimeString(),
            'koordinat_masuk' => $request->input('koordinat'),
            'foto_masuk' => $fotoPath,
            'status' => $status,
        ]);

        Log::info('Attendance check-in', [
            'user_id' => $user->id,
            'ip' => $request->ip(),
            'status' => $status,
            'timestamp' => now(),
        ]);

        // Notify supervisor
        $pembimbing = $user->pembimbing;
        if ($pembimbing) {
            $msgType = $status === 'Terlambat' ? 'warning' : 'success';
            $pembimbing->notify(new \App\Notifications\AbsenNotification(
                'Kehadiran Intern ' . ($status === 'Terlambat' ? 'Terlambat' : 'Masuk'),
                $user->nama_lengkap . ' telah melakukan absen masuk pada pukul ' . $now->format('H:i') . ' (Status: ' . $status . ').',
                $msgType
            ));
        }

        return response()->json([
            'success' => true,
            'message' => 'Absen masuk berhasil dilakukan!',
            'data' => [
                'jam_masuk' => Carbon::parse($attendance->jam_masuk)->format('H:i'),
                'status' => $attendance->status,
            ]
        ]);
    }

    /**
     * Handle intern check-out (absen pulang).
     */
    public function checkOut(Request $request)
    {
        $user = Auth::user();
        $pembimbing = $user->pembimbing;
        $requirePhoto = true;
        if ($pembimbing) {
            if (!$pembimbing->require_photo_attendance_global && !$user->require_photo_attendance) {
                $requirePhoto = false;
            }
        } else {
            if (!$user->require_photo_attendance) {
                $requirePhoto = false;
            }
        }

        $request->validate([
            'koordinat' => ['required', 'regex:/^[-+]?[0-9]{1,2}(?:\.[0-9]+)?,\s*[-+]?[0-9]{1,3}(?:\.[0-9]+)?$/'],
            'foto'      => $requirePhoto ? ['required', 'string', 'max:21000000'] : ['nullable', 'string', 'max:21000000'],
            'alasan'    => 'nullable|string',
            'akurasi_gps' => ['sometimes', 'numeric', 'min:0', 'max:100'],
        ]);

        // Server-side Geofencing validation (multi-location aware)
        $locations = \App\Models\OfficeLocation::all();
        $koordinat = $request->input('koordinat');
        $akurasiGps = (double) $request->input('akurasi_gps', 0);
        $parts = explode(',', $koordinat);

        if (count($parts) !== 2) {
            return response()->json([
                'success' => false,
                'message' => 'Format koordinat tidak valid.',
            ], 400);
        }

        $userLat = (double) trim($parts[0]);
        $userLng = (double) trim($parts[1]);

        $allowed = false;
        $minDistance = null;
        $closestLocation = null;

        // If no locations configured, fallback to GeneralSettings coordinates
        if ($locations->isEmpty()) {
            $settings = app(\App\Settings\GeneralSettings::class);
            $officeLat = (double) $settings->latitude_kantor;
            $officeLng = (double) $settings->longitude_kantor;
            $radiusLimit = (double) $settings->radius_meter;

            $distance = $this->calculateDistance($userLat, $userLng, $officeLat, $officeLng);
            $minDistance = $distance;
            $closestLocation = (object)[
                'name' => 'Kantor Utama',
                'radius' => $radiusLimit
            ];

            if ($distance <= $radiusLimit || ($distance - $akurasiGps) <= $radiusLimit) {
                $allowed = true;
            }
        } else {
            foreach ($locations as $loc) {
                $distance = $this->calculateDistance($userLat, $userLng, (double)$loc->latitude, (double)$loc->longitude);
                if ($minDistance === null || $distance < $minDistance) {
                    $minDistance = $distance;
                    $closestLocation = $loc;
                }
                if ($distance <= (double)$loc->radius || ($distance - $akurasiGps) <= (double)$loc->radius) {
                    $allowed = true;
                }
            }
        }

        if (!$allowed) {
            $closestName = $closestLocation ? $closestLocation->name : 'Kantor';
            $closestRadius = $closestLocation ? $closestLocation->radius : 380;
            return response()->json([
                'success' => false,
                'message' => 'Absensi ditolak! Posisi Anda berada di luar radius kantor terdekat: ' . $closestName . ' (' . round($minDistance) . ' meter dari kantor, batas radius: ' . $closestRadius . ' meter).',
            ], 400);
        }

        // Get today's schedule to get jam_masuk, batas_keterlambatan, and jam_pulang
        $now = Carbon::now();

        // [MODE TEST] Khusus akun yogi.sutana@gmail.com: gunakan waktu lokal HP yang dikirim client (param atau cookie)
        if ($user->email === 'yogi.sutana@gmail.com') {
            $testTime = $request->input('client_time') ?: $request->cookie('client_time');
            if ($testTime) {
                try {
                    $now = Carbon::parse($testTime)->timezone('Asia/Jakarta');
                    Log::info('[TEST MODE] check-out menggunakan waktu lokal HP', [
                        'user_id' => $user->id,
                        'source' => $request->has('client_time') ? 'request_param' : 'cookie',
                        'resolved_now' => $now->toDateTimeString(),
                    ]);
                } catch (\Exception $e) {
                    $now = Carbon::now();
                }
            }
        }

        // $today mengikuti tanggal dari $now (HP untuk test user, server untuk user biasa)
        $today = $now->copy()->startOfDay();

        $schedule = \App\Models\WorkSchedule::getScheduleForDate($now);
        $settings = app(\App\Settings\GeneralSettings::class);

        $jamMasukStr = ($schedule && $schedule->jam_masuk)
            ? $schedule->jam_masuk
            : $settings->jam_masuk;

        $limitStr = ($schedule && $schedule->batas_keterlambatan)
            ? $schedule->batas_keterlambatan
            : $settings->batas_keterlambatan;

        $jamPulangStr = ($schedule && $schedule->jam_pulang)
            ? $schedule->jam_pulang
            : $settings->jam_pulang;

        // Get today's attendance record (gunakan $today agar konsisten dengan tanggal HP)
        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('tanggal', $today->toDateString())
            ->first();

        $isPastLateLimit = false;
        if ($limitStr) {
            $limitParts = explode(':', $limitStr);
            $limitHour = isset($limitParts[0]) ? (int) $limitParts[0] : 8;
            $limitMinute = isset($limitParts[1]) ? (int) $limitParts[1] : 15;
            $limitSecond = isset($limitParts[2]) ? (int) $limitParts[2] : 0;
            $limitTime = $today->copy()->setTime($limitHour, $limitMinute, $limitSecond);
            if ($now->greaterThan($limitTime)) {
                $isPastLateLimit = true;
            }
        }

        if (!$attendance) {
            // User forgot to check-in. They can only check-out if the check-in time limit has passed.
            if (!$isPastLateLimit) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda harus melakukan absen masuk terlebih dahulu.',
                ], 400);
            }
        }

        if ($attendance && $attendance->jam_pulang) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah melakukan absen pulang hari ini.',
            ], 400);
        }

        // Verify that the user has filled in at least 1 logbook entry for today (gunakan $today)
        $hasLogbook = \App\Models\Logbook::where('user_id', $user->id)
            ->whereDate('tanggal', $today->toDateString())
            ->exists();

        if (!$hasLogbook) {
            return response()->json([
                'success' => false,
                'message' => 'Anda wajib mengisi minimal 1 logbook kegiatan hari ini sebelum melakukan absen pulang.',
            ], 400);
        }

        // Process Base64 photo
        $fotoPath = null;
        $fotoData = $request->input('foto');
        if ($fotoData) {
            if (preg_match('/^data:image\/(\w+);base64,/', $fotoData, $type)) {
                $fotoData = substr($fotoData, strpos($fotoData, ',') + 1);
                $type = strtolower($type[1]);

                if (!in_array($type, ['jpg', 'jpeg', 'png', 'webp'])) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Format foto tidak valid.',
                    ], 400);
                }

                $fotoData = base64_decode($fotoData);
                if ($fotoData === false) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Dekode foto gagal.',
                    ], 400);
                }
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Data foto tidak valid.',
                ], 400);
            }

            // Save photo file
            $folderPath = 'attendance/' . $user->user_code;
            $randomSuffix = substr(uniqid(), -5);
            $fileName = 'selfie_pulang_' . $now->format('dmyHis') . '_' . $randomSuffix . '.' . $type;
            Storage::disk('public')->put($folderPath . '/' . $fileName, $fotoData);
            $fotoPath = 'storage/' . $folderPath . '/' . $fileName;
        } elseif ($requirePhoto) {
            return response()->json([
                'success' => false,
                'message' => 'Foto selfie wajib diunggah.',
            ], 400);
        }

        $isEarly = false;
        if (!$attendance) {
            // User did not check in. Calculate checkout threshold: jam_pulang + (batas_absensi - jam_masuk)
            $masukCarbon = Carbon::parse($jamMasukStr);
            $limitCarbon = Carbon::parse($limitStr);
            $diffMinutes = $limitCarbon->diffInMinutes($masukCarbon, true);

            $pulangParts = explode(':', $jamPulangStr);
            $pulangHour = isset($pulangParts[0]) ? (int) $pulangParts[0] : 16;
            $pulangMinute = isset($pulangParts[1]) ? (int) $pulangParts[1] : 0;
            $pulangSecond = isset($pulangParts[2]) ? (int) $pulangParts[2] : 0;
            $batasJamPulang = $today->copy()->setTime($pulangHour, $pulangMinute, $pulangSecond)->addMinutes($diffMinutes);

            if ($now->lessThan($batasJamPulang)) {
                $status = 'Lupa Absen Masuk dan Izin';
                $isEarly = true;
            } else {
                $status = 'Lupa Absen Masuk';
            }

            $attendance = Attendance::create([
                'user_id' => $user->id,
                'tanggal' => $today->toDateString(),
                'jam_masuk' => null,
                'koordinat_masuk' => null,
                'foto_masuk' => null,
                'jam_pulang' => $now->toTimeString(),
                'koordinat_pulang' => $request->input('koordinat'),
                'foto_pulang' => $fotoPath,
                'status' => $status,
            ]);

            Log::info('Attendance check-out (no prior check-in)', [
                'user_id' => $user->id,
                'ip' => $request->ip(),
                'status' => $status,
                'timestamp' => now(),
            ]);
        } else {
            // Calculate late compensation
            $jamMasukCarbon = Carbon::parse($attendance->jam_masuk);
            $standardMasukCarbon = Carbon::parse($jamMasukStr);
            
            $lateMinutes = 0;
            if ($jamMasukCarbon->greaterThan($standardMasukCarbon)) {
                $lateMinutes = $jamMasukCarbon->diffInMinutes($standardMasukCarbon, true);
            }
            
            $targetPulangCarbon = Carbon::parse($jamPulangStr)->addMinutes($lateMinutes);
            
            $newStatus = $attendance->status;
            if ($now->greaterThanOrEqualTo($targetPulangCarbon)) {
                $newStatus = 'Hadir'; // late check-in compensated by staying late
            } else {
                $isEarly = true;
                if ($lateMinutes > 0) {
                    $newStatus = 'Terlambat dan Izin';
                } else {
                    $newStatus = 'Pulang Cepat / Izin';
                }
            }
            
            // User checked in normally, update checkout info and status
            $attendance->update([
                'jam_pulang'       => $now->toTimeString(),
                'koordinat_pulang' => $request->input('koordinat'),
                'foto_pulang'      => $fotoPath,
                'status'           => $newStatus,
            ]);

            Log::info('Attendance check-out', [
                'user_id' => $user->id,
                'ip' => $request->ip(),
                'status' => $newStatus,
                'timestamp' => now(),
            ]);
        }

        // Handle automated early checkout leave request
        if ($isEarly) {
            if (!$request->filled('alasan')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda terdeteksi pulang sebelum waktunya. Alasan pulang cepat wajib diisi.',
                ], 400);
            }

            \App\Models\LeaveRequest::create([
                'user_id' => $user->id,
                'tanggal_mulai' => $today->toDateString(),
                'tanggal_selesai' => $today->toDateString(),
                'jenis' => 'Izin',
                'alasan' => $request->input('alasan'),
                'status_approval' => 'Pending',
            ]);
        }

        // Notify supervisor
        $pembimbing = $user->pembimbing;
        if ($pembimbing) {
            $pembimbing->notify(new \App\Notifications\AbsenNotification(
                'Kehadiran Intern Pulang',
                $user->nama_lengkap . ' telah melakukan absen pulang pada pukul ' . $now->format('H:i') . '.',
                'info'
            ));
        }

        return response()->json([
            'success' => true,
            'message' => 'Absen pulang berhasil dilakukan!',
            'data' => [
                'jam_pulang' => Carbon::parse($attendance->jam_pulang)->format('H:i'),
            ]
        ]);
    }

    /**
     * Calculate distance between two GPS coordinates in meters using Haversine formula.
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // in meters

        $latFrom = deg2rad($lat1);
        $lonFrom = deg2rad($lon1);
        $latTo = deg2rad($lat2);
        $lonTo = deg2rad($lon2);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
        
        return $angle * $earthRadius;
    }
}
