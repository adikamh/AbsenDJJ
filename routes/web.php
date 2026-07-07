<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

// 1. Guest Routes
Route::middleware('guest')->group(function () {
    Route::get('/', function () {
        return redirect()->route('login');
    });

    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// 2. Authenticated Routes (General)
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/logout', [AuthController::class, 'logout']); // convenient GET fallback
});

// 3. Role-based Specific Route Groups (For RBAC testing & features)
Route::middleware(['auth', 'role:super_admin'])->prefix('super-admin')->group(function () {
    Route::get('/users', function () {
        return "Welcome to Super Admin User Management panel!";
    })->name('super-admin.users');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/interns', function () {
        return "Welcome to field supervisor view of all interns!";
    })->name('admin.interns');
});

Route::middleware(['auth', 'role:peserta'])->prefix('peserta')->group(function () {
    Route::get('/my-attendance', function () {
        return "Welcome to intern attendance details!";
    })->name('peserta.attendance');
});
