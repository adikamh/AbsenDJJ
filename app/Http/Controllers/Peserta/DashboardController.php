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
        $todayAttendance = Attendance::where('user_id', $user->id)
            ->where('tanggal', Carbon::today()->toDateString())
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
            ->whereDate('tanggal_mulai', '<=', Carbon::today())
            ->whereDate('tanggal_selesai', '>=', Carbon::today())
            ->first();

        // Calculate target schedule hours for today
        $now = Carbon::now();
        $schedule = WorkSchedule::getScheduleForDate($now);
        $settings = app(\App\Settings\GeneralSettings::class);

        $isHoliday = false;
        if ($schedule) {
            $isHoliday = $schedule->is_holiday;
        } else {
            $isHoliday = $now->isWeekend();
        }

        if ($isHoliday) {
            $targetJamMasuk = null;
            $targetJamPulang = null;
        } else {
            $jamMasukRaw = ($schedule && $schedule->jam_masuk) ? $schedule->jam_masuk : $settings->jam_masuk;
            $jamPulangRaw = ($schedule && $schedule->jam_pulang) ? $schedule->jam_pulang : $settings->jam_pulang;
            
            $targetJamMasuk = $jamMasukRaw ? Carbon::parse($jamMasukRaw)->format('H:i') : null;
            $targetJamPulang = $jamPulangRaw ? Carbon::parse($jamPulangRaw)->format('H:i') : null;
        }

        $officeLat = $settings->latitude_kantor;
        $officeLng = $settings->longitude_kantor;
        $officeRadius = $settings->radius_meter;

        return view('dashboard.peserta.dashboard', compact(
            'todayAttendance',
            'recentLogbooks',
            'recentLeaves',
            'todayLeave',
            'targetJamMasuk',
            'targetJamPulang',
            'officeLat',
            'officeLng',
            'officeRadius'
        ));
    }
}
