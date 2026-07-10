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
    Route::get('/users', [\App\Http\Controllers\SuperAdmin\PembimbingController::class, 'managePembimbing'])->name('super-admin.users');
    Route::get('/pembimbing', [\App\Http\Controllers\SuperAdmin\PembimbingController::class, 'managePembimbing'])->name('super-admin.pembimbing');
    Route::post('/pembimbing', [\App\Http\Controllers\SuperAdmin\PembimbingController::class, 'storePembimbing'])->name('super-admin.pembimbing.store');
    Route::put('/pembimbing/{pembimbing}/reset-password', [\App\Http\Controllers\SuperAdmin\PembimbingController::class, 'resetPembimbingPassword'])->name('super-admin.pembimbing.reset-password');
    Route::put('/pembimbing/{pembimbing}', [\App\Http\Controllers\SuperAdmin\PembimbingController::class, 'updatePembimbing'])->name('super-admin.pembimbing.update');
    Route::delete('/pembimbing/{pembimbing}', [\App\Http\Controllers\SuperAdmin\PembimbingController::class, 'destroyPembimbing'])->name('super-admin.pembimbing.destroy');
    
    Route::get('/peserta', [\App\Http\Controllers\SuperAdmin\PesertaController::class, 'managePeserta'])->name('super-admin.peserta');
    Route::post('/peserta', [\App\Http\Controllers\SuperAdmin\PesertaController::class, 'storePeserta'])->name('super-admin.peserta.store');
    Route::put('/peserta/{peserta}/reset-password', [\App\Http\Controllers\SuperAdmin\PesertaController::class, 'resetPesertaPassword'])->name('super-admin.peserta.reset-password');
    Route::put('/peserta/{peserta}', [\App\Http\Controllers\SuperAdmin\PesertaController::class, 'updatePeserta'])->name('super-admin.peserta.update');
    Route::delete('/peserta/{peserta}', [\App\Http\Controllers\SuperAdmin\PesertaController::class, 'destroyPeserta'])->name('super-admin.peserta.destroy');

    Route::get('/instansi', [\App\Http\Controllers\SuperAdmin\InstansiController::class, 'manageInstansi'])->name('super-admin.instansi');
    Route::post('/instansi', [\App\Http\Controllers\SuperAdmin\InstansiController::class, 'storeInstansi'])->name('super-admin.instansi.store');
    Route::put('/instansi/{instansi}', [\App\Http\Controllers\SuperAdmin\InstansiController::class, 'updateInstansi'])->name('super-admin.instansi.update');
    Route::delete('/instansi/{instansi}', [\App\Http\Controllers\SuperAdmin\InstansiController::class, 'destroyInstansi'])->name('super-admin.instansi.destroy');

    Route::get('/settings', [\App\Http\Controllers\SuperAdmin\SettingsController::class, 'editSettings'])->name('super-admin.settings');
    Route::put('/settings', [\App\Http\Controllers\SuperAdmin\SettingsController::class, 'updateSettings'])->name('super-admin.settings.update');

    Route::post('/schedules', [\App\Http\Controllers\SuperAdmin\SettingsController::class, 'storeScheduleOverride'])->name('super-admin.schedules.store');
    Route::post('/schedules/sync-holidays', [\App\Http\Controllers\SuperAdmin\SettingsController::class, 'syncHolidays'])->name('super-admin.schedules.sync-holidays');
    Route::put('/schedules/{schedule}', [\App\Http\Controllers\SuperAdmin\SettingsController::class, 'updateScheduleOverride'])->name('super-admin.schedules.update');
    Route::delete('/schedules/{schedule}', [\App\Http\Controllers\SuperAdmin\SettingsController::class, 'destroyScheduleOverride'])->name('super-admin.schedules.destroy');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/interns', function () {
        return "Welcome to field supervisor view of all interns!";
    })->name('admin.interns');
});

Route::middleware(['auth', 'role:peserta'])->prefix('peserta')->group(function () {
    // Absensi & Riwayat
    Route::get('/my-attendance', [\App\Http\Controllers\Peserta\AttendanceHistoryController::class, 'index'])->name('peserta.attendance');
    Route::post('/attendance/check-in', [\App\Http\Controllers\AttendanceController::class, 'checkIn'])->name('peserta.attendance.checkin');
    Route::post('/attendance/check-out', [\App\Http\Controllers\AttendanceController::class, 'checkOut'])->name('peserta.attendance.checkout');

    // Logbook
    Route::get('/logbook', [\App\Http\Controllers\Peserta\LogbookController::class, 'index'])->name('peserta.logbook');
    Route::post('/logbook', [\App\Http\Controllers\Peserta\LogbookController::class, 'store'])->name('peserta.logbook.store');
    Route::put('/logbook/{logbook}', [\App\Http\Controllers\Peserta\LogbookController::class, 'update'])->name('peserta.logbook.update');
    Route::delete('/logbook/{logbook}', [\App\Http\Controllers\Peserta\LogbookController::class, 'destroy'])->name('peserta.logbook.destroy');
    Route::get('/logbook/export-pdf', [\App\Http\Controllers\Peserta\LogbookController::class, 'exportPdf'])->name('peserta.logbook.pdf');

    // Izin / Sakit
    Route::post('/leave-request', [\App\Http\Controllers\Peserta\LeaveRequestController::class, 'store'])->name('peserta.leave.store');
});
