<?php

use App\Http\Controllers\Admin\Security\DevIpBlacklistController;
use App\Http\Controllers\Admin\Security\DevIpWhitelistController;
use App\Http\Controllers\Admin\Security\DevLoginLogController;
use App\Http\Controllers\Admin\Security\DevMonitoringController;
use App\Http\Controllers\Admin\Security\DevOtpController;
use App\Http\Controllers\Admin\Security\DevStatsController;
use App\Http\Controllers\Admin\Security\DevSystemErrorController;
use App\Http\Controllers\Admin\Security\DevTrustedDeviceController;
use App\Http\Controllers\Admin\Security\DevUserController;
use Illuminate\Support\Facades\Route;

if (! app()->environment(['local', 'development', 'testing'])) {
    return;
}

Route::middleware(['auth', 'ensure.session.version', 'role:super-admin', 'throttle:admin-actions'])
    ->prefix('/dev/monitoring')
    ->name('dev.monitoring.')
    ->group(function () {
        Route::get('/', [DevMonitoringController::class, 'dashboard'])->middleware('permission:dashboard.view')->name('dashboard');
        Route::get('/api/stats', DevStatsController::class)->middleware('permission:analytics.view')->name('api.stats');
        Route::get('/api/otps', [DevOtpController::class, 'index'])->middleware('permission:otp.view')->name('api.otps.index');
        Route::get('/api/logs', [DevLoginLogController::class, 'index'])->middleware('permission:login-logs.view')->name('api.logs.index');
        Route::get('/api/export/logs', [DevLoginLogController::class, 'export'])->middleware('permission:login-logs.export')->name('api.logs.export');
        Route::get('/api/devices', [DevTrustedDeviceController::class, 'index'])->middleware('permission:devices.view')->name('api.devices.index');
        Route::post('/api/devices/{deviceId}/revoke', [DevTrustedDeviceController::class, 'revoke'])->middleware('permission:devices.revoke')->name('api.devices.revoke');
        Route::get('/api/users', [DevUserController::class, 'index'])->middleware('permission:users.view')->name('api.users.index');
        Route::post('/api/users/{userId}/unblock', [DevUserController::class, 'unblock'])->middleware('permission:users.edit')->name('api.users.unblock');
        Route::post('/api/users/{userId}/block', [DevUserController::class, 'block'])->middleware('permission:users.edit')->name('api.users.block');
        Route::get('/api/ip-blacklist', [DevIpBlacklistController::class, 'index'])->middleware('permission:ip-list.view')->name('api.blacklist.index');
        Route::post('/api/ip-blacklist', [DevIpBlacklistController::class, 'store'])->middleware('permission:ip-list.blacklist')->name('api.blacklist.store');
        Route::delete('/api/ip-blacklist/{ip}', [DevIpBlacklistController::class, 'destroy'])->middleware('permission:ip-list.blacklist')->name('api.blacklist.destroy');
        Route::get('/api/ip-whitelist', [DevIpWhitelistController::class, 'index'])->middleware('permission:ip-list.view')->name('api.whitelist.index');
        Route::post('/api/ip-whitelist', [DevIpWhitelistController::class, 'store'])->middleware('permission:ip-list.whitelist')->name('api.whitelist.store');
        Route::delete('/api/ip-whitelist/{ip}', [DevIpWhitelistController::class, 'destroy'])->middleware('permission:ip-list.whitelist')->name('api.whitelist.destroy');
        Route::get('/api/system-errors', [DevSystemErrorController::class, 'index'])->middleware('permission:errors.view')->name('api.errors.index');
    });
