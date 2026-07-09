<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CookieConsentController;
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

Route::post('/cookie-consent', [CookieConsentController::class, 'store'])->name('cookie-consent.store');

// 3. Role-based Specific Route Groups (For RBAC testing & features)
Route::middleware(['auth', 'role:super_admin'])->prefix('super-admin')->group(function () {
    Route::get('/users', [DashboardController::class, 'managePembimbing'])->name('super-admin.users');
    Route::get('/pembimbing', [DashboardController::class, 'managePembimbing'])->name('super-admin.pembimbing');
    Route::post('/pembimbing', [DashboardController::class, 'storePembimbing'])->name('super-admin.pembimbing.store');
    Route::put('/pembimbing/{pembimbing}/reset-password', [DashboardController::class, 'resetPembimbingPassword'])->name('super-admin.pembimbing.reset-password');
    Route::put('/pembimbing/{pembimbing}', [DashboardController::class, 'updatePembimbing'])->name('super-admin.pembimbing.update');
    Route::delete('/pembimbing/{pembimbing}', [DashboardController::class, 'destroyPembimbing'])->name('super-admin.pembimbing.destroy');
    Route::get('/peserta', [DashboardController::class, 'managePeserta'])->name('super-admin.peserta');
    Route::post('/peserta', [DashboardController::class, 'storePeserta'])->name('super-admin.peserta.store');
    Route::put('/peserta/{peserta}/reset-password', [DashboardController::class, 'resetPesertaPassword'])->name('super-admin.peserta.reset-password');
    Route::put('/peserta/{peserta}', [DashboardController::class, 'updatePeserta'])->name('super-admin.peserta.update');
    Route::delete('/peserta/{peserta}', [DashboardController::class, 'destroyPeserta'])->name('super-admin.peserta.destroy');

    Route::get('/instansi', [DashboardController::class, 'manageInstansi'])->name('super-admin.instansi');
    Route::post('/instansi', [DashboardController::class, 'storeInstansi'])->name('super-admin.instansi.store');
    Route::put('/instansi/{instansi}', [DashboardController::class, 'updateInstansi'])->name('super-admin.instansi.update');
    Route::delete('/instansi/{instansi}', [DashboardController::class, 'destroyInstansi'])->name('super-admin.instansi.destroy');

    Route::get('/settings', [DashboardController::class, 'editSettings'])->name('super-admin.settings');
    Route::put('/settings', [DashboardController::class, 'updateSettings'])->name('super-admin.settings.update');

    Route::post('/schedules', [DashboardController::class, 'storeScheduleOverride'])->name('super-admin.schedules.store');
    Route::put('/schedules/{schedule}', [DashboardController::class, 'updateScheduleOverride'])->name('super-admin.schedules.update');
    Route::delete('/schedules/{schedule}', [DashboardController::class, 'destroyScheduleOverride'])->name('super-admin.schedules.destroy');
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

    Route::post('/attendance/check-in', [App\Http\Controllers\AttendanceController::class, 'checkIn'])->name('peserta.attendance.checkin');
    Route::post('/attendance/check-out', [App\Http\Controllers\AttendanceController::class, 'checkOut'])->name('peserta.attendance.checkout');
});
