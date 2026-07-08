<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /**
     * Handle intern check-in (absen masuk).
     */
    public function checkIn(Request $request)
    {
        $request->validate([
            'koordinat' => 'required|string',
            'foto' => 'required|string', // Base64 data url
        ]);

        $user = Auth::user();

        // Double check if already checked in today
        $todayAttendance = Attendance::where('user_id', $user->id)
            ->where('tanggal', Carbon::today()->toDateString())
            ->first();

        if ($todayAttendance) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah melakukan absen masuk hari ini.',
            ], 400);
        }

        // Process Base64 photo
        $fotoData = $request->input('foto');
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

        // Save photo file in public folder
        $fileName = 'selfie_masuk_' . $user->id . '_' . time() . '.' . $type;
        $dirPath = public_path('uploads/attendance');
        
        if (!File::isDirectory($dirPath)) {
            File::makeDirectory($dirPath, 0755, true, true);
        }

        File::put($dirPath . '/' . $fileName, $fotoData);
        $fotoPath = 'uploads/attendance/' . $fileName;

        // Determine status (Terlambat if after 08:00 AM, otherwise Hadir)
        $now = Carbon::now();
        $limitTime = Carbon::today()->setTime(8, 0, 0); // 08:00 AM limit
        $status = $now->greaterThan($limitTime) ? 'Terlambat' : 'Hadir';

        // Create attendance record
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'tanggal' => Carbon::today()->toDateString(),
            'jam_masuk' => $now->toTimeString(),
            'koordinat_masuk' => $request->input('koordinat'),
            'foto_masuk' => $fotoPath,
            'status' => $status,
        ]);

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
        $request->validate([
            'koordinat' => 'required|string',
            'foto'      => 'required|string', // Base64 data url
        ]);

        $user = Auth::user();

        // Get today's attendance record
        $attendance = Attendance::where('user_id', $user->id)
            ->where('tanggal', Carbon::today()->toDateString())
            ->first();

        if (!$attendance) {
            return response()->json([
                'success' => false,
                'message' => 'Anda harus melakukan absen masuk terlebih dahulu.',
            ], 400);
        }

        if ($attendance->jam_pulang) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah melakukan absen pulang hari ini.',
            ], 400);
        }

        // Process Base64 photo
        $fotoData = $request->input('foto');
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
        $fileName = 'selfie_pulang_' . $user->id . '_' . time() . '.' . $type;
        $dirPath  = public_path('uploads/attendance');

        if (!File::isDirectory($dirPath)) {
            File::makeDirectory($dirPath, 0755, true, true);
        }

        File::put($dirPath . '/' . $fileName, $fotoData);
        $fotoPath = 'uploads/attendance/' . $fileName;

        $now = Carbon::now();

        // Update today's attendance record
        $attendance->update([
            'jam_pulang'       => $now->toTimeString(),
            'koordinat_pulang' => $request->input('koordinat'),
            'foto_pulang'      => $fotoPath,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Absen pulang berhasil dilakukan!',
            'data' => [
                'jam_pulang' => Carbon::parse($attendance->jam_pulang)->format('H:i'),
            ]
        ]);
    }
}
