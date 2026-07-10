<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Instansi;
use App\Models\Attendance;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Super Admin Dashboard.
     */
    public function index()
    {
        $totalUsers = User::count();
        $totalInstansi = Instansi::count();
        $totalHadirHariIni = Attendance::where('tanggal', Carbon::today()->toDateString())
            ->whereIn('status', ['Hadir', 'Terlambat'])
            ->count();

        $recentUsers = User::with('role', 'instansi')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Fetch last 7 working days attendance stats
        $attendanceChartData = [];
        $date = Carbon::today();
        $daysCounted = 0;

        while ($daysCounted < 7) {
            if (!$date->isWeekend()) {
                $dateString = $date->toDateString();

                $hadir = Attendance::where('tanggal', $dateString)
                    ->where('status', 'Hadir')
                    ->count();

                $terlambat = Attendance::where('tanggal', $dateString)
                    ->where('status', 'Terlambat')
                    ->count();

                $izin = Attendance::where('tanggal', $dateString)
                    ->whereIn('status', ['Izin', 'Sakit'])
                    ->count();

                $absen = Attendance::where('tanggal', $dateString)
                    ->where('status', 'Tanpa Keterangan')
                    ->count();

                $attendanceChartData[] = [
                    'label' => $date->translatedFormat('d M'),
                    'hadir' => $hadir,
                    'terlambat' => $terlambat,
                    'izin' => $izin,
                    'absen' => $absen,
                ];
                $daysCounted++;
            }
            $date = $date->subDay();
        }

        $attendanceChartData = array_reverse($attendanceChartData);

        return view('dashboard.super_admin.dashboard', compact(
            'totalUsers',
            'totalInstansi',
            'totalHadirHariIni',
            'recentUsers',
            'attendanceChartData'
        ));
    }
}
