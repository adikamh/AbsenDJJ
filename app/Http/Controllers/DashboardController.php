<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Instansi;
use App\Models\Attendance;
use App\Models\Logbook;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display the corresponding role-based dashboard.
     */
    public function index()
    {
        $user = Auth::user();

        if ($user->isSuperAdmin()) {
            return $this->superAdminDashboard();
        } elseif ($user->isAdmin()) {
            return $this->adminDashboard($user);
        } else {
            return $this->pesertaDashboard($user);
        }
    }

    /**
     * Super Admin Dashboard.
     */
    private function superAdminDashboard()
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

        return view('dashboard.super_admin', compact(
            'totalUsers',
            'totalInstansi',
            'totalHadirHariIni',
            'recentUsers'
        ));
    }

    /**
     * Field Supervisor (Admin) Dashboard.
     */
    private function adminDashboard(User $user)
    {
        // Get list of guided interns
        $interns = $user->anakBimbingan()->with('instansi')->get();
        $internIds = $interns->pluck('id');

        // Get logbooks pending approval for these interns
        $pendingLogbooks = Logbook::with('user')
            ->whereIn('user_id', $internIds)
            ->where('status_approval', 'Pending')
            ->orderBy('tanggal', 'desc')
            ->get();

        // Get leave requests pending approval for these interns
        $pendingLeaves = LeaveRequest::with('user')
            ->whereIn('user_id', $internIds)
            ->where('status_approval', 'Pending')
            ->orderBy('tanggal_mulai', 'desc')
            ->get();

        // Attendance stats for today
        $hadirTodayCount = Attendance::whereIn('user_id', $internIds)
            ->where('tanggal', Carbon::today()->toDateString())
            ->whereIn('status', ['Hadir', 'Terlambat'])
            ->count();

        return view('dashboard.admin', compact(
            'interns',
            'pendingLogbooks',
            'pendingLeaves',
            'hadirTodayCount'
        ));
    }

    /**
     * Intern (Peserta) Dashboard.
     */
    private function pesertaDashboard(User $user)
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

        return view('dashboard.peserta', compact(
            'todayAttendance',
            'recentLogbooks',
            'recentLeaves'
        ));
    }
}
