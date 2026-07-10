<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Logbook;
use App\Models\LeaveRequest;
use App\Models\Attendance;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Field Supervisor (Admin) Dashboard.
     */
    public function index(User $user)
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

        return view('dashboard.admin.dashboard', compact(
            'interns',
            'pendingLogbooks',
            'pendingLeaves',
            'hadirTodayCount'
        ));
    }
}
