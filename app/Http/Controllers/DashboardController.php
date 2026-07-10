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
