<?php
use App\Http\Controllers\Web\WebAuthController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Dev\DevMonitoringController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect()->route('login'));

Route::middleware('guest')->group(function () {
    Route::get('/login', [WebAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [WebAuthController::class, 'login'])->name('login.post');
    Route::get('/otp', [WebAuthController::class, 'showOtp'])->name('otp.verify');
    Route::post('/otp', [WebAuthController::class, 'verifyOtp'])->name('otp.verify.post');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/audit-log', [DashboardController::class, 'auditLog'])->name('audit.log');
    Route::post('/logout', [WebAuthController::class, 'logout'])->name('logout');
});

Route::prefix('/dev/monitoring')->group(function () {
    Route::get('/', [DevMonitoringController::class, 'dashboard']);
    Route::get('/api/stats', [DevMonitoringController::class, 'stats']);
    Route::get('/api/otps', [DevMonitoringController::class, 'otps']);
    Route::get('/api/logs', [DevMonitoringController::class, 'loginLogs']);
    Route::get('/api/devices', [DevMonitoringController::class, 'trustedDevices']);
    Route::get('/api/users', [DevMonitoringController::class, 'users']);
    Route::post('/api/unblock/{userId}', [DevMonitoringController::class, 'unblockUser']);
    Route::post('/api/devices/{deviceId}/revoke', [DevMonitoringController::class, 'revokeDevice']);
});