<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Display the corresponding role-based dashboard.
     */
    public function index()
    {
        // Auto-update past attendances where user checked in but forgot to check out (past 24:00 of that day)
        \App\Models\Attendance::where('tanggal', '<', \Carbon\Carbon::today()->toDateString())
            ->whereNotNull('jam_masuk')
            ->whereNull('jam_pulang')
            ->whereNotIn('status', ['Lupa Absen Pulang', 'Tanpa Keterangan', 'Izin', 'Sakit'])
            ->update(['status' => 'Lupa Absen Pulang']);

        $user = Auth::user();

        if ($user->isSuperAdmin()) {
            return app(\App\Http\Controllers\SuperAdmin\DashboardController::class)->index();
        } elseif ($user->isAdmin()) {
            return app(\App\Http\Controllers\Admin\DashboardController::class)->index($user);
        } else {
            return app(\App\Http\Controllers\Peserta\DashboardController::class)->index($user);
        }
    }
}
