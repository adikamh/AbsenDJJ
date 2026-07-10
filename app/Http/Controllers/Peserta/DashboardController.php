<?php

namespace App\Http\Controllers\Peserta;

use App\Http\Controllers\Controller;
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

        return view('dashboard.peserta.dashboard', compact(
            'todayAttendance',
            'recentLogbooks',
            'recentLeaves'
        ));
    }
}
