<?php

namespace App\Http\Controllers\Peserta;

use App\Http\Controllers\Controller;
use App\Models\WorkSchedule;
use App\Models\User;
use App\Models\Logbook;
use App\Models\LeaveRequest;
use App\Models\Attendance;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Intern (Peserta) Dashboard.
     */
    public function index(User $user)
    {
        $now = Carbon::now();
        
        // [TEST MODE] Khusus akun yogi.sutana@gmail.com: gunakan waktu lokal HP dari cookie jika tersedia
        if ($user->email === 'yogi.sutana@gmail.com' && request()->hasCookie('client_time')) {
            try {
                $now = Carbon::parse(request()->cookie('client_time'))->timezone('Asia/Jakarta');
            } catch (\Exception $e) {
                $now = Carbon::now();
            }
        }
        
        $today = $now->copy()->startOfDay();

        $todayAttendance = Attendance::where('user_id', $user->id)
            ->whereDate('tanggal', $today->toDateString())
            ->first();

        $recentLogbooks = Logbook::where('user_id', $user->id)
            ->orderBy('tanggal', 'desc')
            ->limit(5)
            ->get();

        $recentLeaves = LeaveRequest::where('user_id', $user->id)
            ->orderBy('tanggal_mulai', 'desc')
            ->limit(5)
            ->get();

        $todayLeave = LeaveRequest::where('user_id', $user->id)
            ->where('status_approval', 'Approved')
            ->whereDate('tanggal_mulai', '<=', $today->toDateString())
            ->whereDate('tanggal_selesai', '>=', $today->toDateString())
            ->first();

        // Calculate target schedule hours for today
        $schedule = WorkSchedule::getScheduleForDate($now);
        $settings = app(\App\Settings\GeneralSettings::class);

        $isHoliday = false;
        $holidayName = null;
        if ($schedule) {
            $isHoliday = $schedule->is_holiday;
            $holidayName = $schedule->keterangan ?? 'Hari Libur';
        }

        if ($isHoliday) {
            $targetJamMasuk = null;
            $targetJamPulang = null;
            $isPastLateLimit = false;
            $targetBatasTerlambat = null;
        } else {
            $jamMasukRaw = ($schedule && $schedule->jam_masuk) ? $schedule->jam_masuk : $settings->jam_masuk;
            $jamPulangRaw = ($schedule && $schedule->jam_pulang) ? $schedule->jam_pulang : $settings->jam_pulang;
            
            $targetJamMasuk = $jamMasukRaw ? Carbon::parse($jamMasukRaw)->format('H:i') : null;
            $targetJamPulang = $jamPulangRaw ? Carbon::parse($jamPulangRaw)->format('H:i') : null;

            $isPastLateLimit = false;
            $batasTerlambatRaw = ($schedule && $schedule->batas_keterlambatan) ? $schedule->batas_keterlambatan : $settings->batas_keterlambatan;
            $targetBatasTerlambat = $batasTerlambatRaw ? Carbon::parse($batasTerlambatRaw)->format('H:i') : null;

            if ($batasTerlambatRaw) {
                $limitParts = explode(':', $batasTerlambatRaw);
                $limitHour = isset($limitParts[0]) ? (int) $limitParts[0] : 8;
                $limitMinute = isset($limitParts[1]) ? (int) $limitParts[1] : 15;
                $limitSecond = isset($limitParts[2]) ? (int) $limitParts[2] : 0;
                $limitTime = $today->copy()->setTime($limitHour, $limitMinute, $limitSecond);

                if ($now->greaterThan($limitTime)) {
                    $isPastLateLimit = true;
                }
            }
        }

        $officeLat = $settings->latitude_kantor;
        $officeLng = $settings->longitude_kantor;
        $officeRadius = $settings->radius_meter;

        $officeLocations = \App\Models\OfficeLocation::all();

        $todayLogbooksCount = Logbook::where('user_id', $user->id)
            ->whereDate('tanggal', $today->toDateString())
            ->count();

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

        return view('dashboard.peserta.dashboard', compact(
            'todayAttendance',
            'recentLogbooks',
            'recentLeaves',
            'todayLeave',
            'targetJamMasuk',
            'targetJamPulang',
            'officeLat',
            'officeLng',
            'officeRadius',
            'officeLocations',
            'todayLogbooksCount',
            'isPastLateLimit',
            'targetBatasTerlambat',
            'requirePhoto',
            'isHoliday',
            'holidayName'
        ));
    }

    public function getNotifications()
    {
        $user = auth()->user();
        $user->unsetRelation('unreadNotifications');
        $notifications = $user->unreadNotifications->map(function($notification) {
            return [
                'id' => $notification->id,
                'title' => $notification->data['title'] ?? '',
                'message' => $notification->data['message'] ?? '',
                'type' => $notification->data['type'] ?? '',
                'created_at' => $notification->created_at->diffForHumans(),
            ];
        });

        return response()->json($notifications);
    }

    public function markNotificationsRead()
    {
        $user = auth()->user();
        $user->unreadNotifications->markAsRead();

        return response()->json(['success' => true]);
    }

    public function checkHoliday()
    {
        $now = Carbon::now();
        $user = auth()->user();
        
        // [TEST MODE] Khusus akun yogi.sutana@gmail.com: gunakan waktu lokal HP dari cookie jika tersedia
        if ($user && $user->email === 'yogi.sutana@gmail.com' && request()->hasCookie('client_time')) {
            try {
                $now = Carbon::parse(request()->cookie('client_time'))->timezone('Asia/Jakarta');
            } catch (\Exception $e) {
                $now = Carbon::now();
            }
        }

        $schedule = WorkSchedule::getScheduleForDate($now);
        
        $isHoliday = false;
        $holidayName = null;
        if ($schedule) {
            $isHoliday = $schedule->is_holiday;
            $holidayName = $schedule->keterangan ?? 'Hari Libur';
        }
        
        // Update cache file so it stays fresh (hanya untuk user biasa, agar tidak merusak cache tanggal server)
        if (!$user || $user->email !== 'yogi.sutana@gmail.com') {
            WorkSchedule::updateTodayHolidayCacheFile();
        }
        
        return response()->json([
            'date' => $now->toDateString(),
            'is_holiday' => $isHoliday,
            'holiday_name' => $holidayName
        ]);
    }
}
