<?php

namespace App\Http\Controllers\Peserta;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\WorkSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AttendanceHistoryController extends Controller
{
    /**
     * Show attendance history calendar for the logged-in intern.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Parse selected month & year, default to current
        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);
        
        $selectedDate = Carbon::create($year, $month, 1);
        $startOfMonth = $selectedDate->copy()->startOfMonth();
        $endOfMonth = $selectedDate->copy()->endOfMonth();
        
        // Fetch all attendance records for the selected month
        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('tanggal', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
            ->get()
            ->keyBy(function($item) {
                return Carbon::parse($item->tanggal)->toDateString();
            });
            
        // Fetch work schedule overrides for the selected month (to display holidays/custom workdays)
        $schedules = WorkSchedule::where('type', 'date')
            ->whereBetween('specific_date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
            ->get()
            ->keyBy(function($item) {
                return Carbon::parse($item->specific_date)->toDateString();
            });

        // Calculate statistics
        $stats = [
            'hadir' => 0,
            'terlambat' => 0,
            'izin' => 0,
            'absen' => 0,
        ];

        foreach ($attendances as $att) {
            if ($att->status === 'Hadir') {
                $stats['hadir']++;
            } elseif ($att->status === 'Terlambat') {
                $stats['terlambat']++;
            } elseif (in_array($att->status, ['Izin', 'Sakit'])) {
                $stats['izin']++;
            } elseif ($att->status === 'Tanpa Keterangan') {
                $stats['absen']++;
            }
        }

        // Calculate overall attendance rate
        $totalHadir = Attendance::where('user_id', $user->id)
            ->where('status', 'Hadir')
            ->count();
            
        $totalTerlambat = Attendance::where('user_id', $user->id)
            ->where('status', 'Terlambat')
            ->count();
            
        $totalAttendanceCount = Attendance::where('user_id', $user->id)->count();
        $attendanceRate = $totalAttendanceCount > 0 
            ? round((($totalHadir + $totalTerlambat) / $totalAttendanceCount) * 100) 
            : 0;

        return view('dashboard.peserta.attendance_history', compact(
            'attendances',
            'schedules',
            'stats',
            'selectedDate',
            'month',
            'year',
            'attendanceRate'
        ));
    }
}
