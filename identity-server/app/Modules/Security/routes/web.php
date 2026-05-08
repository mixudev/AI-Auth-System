<?php

use App\Modules\Security\Controllers\SecurityController;
use App\Modules\Security\Controllers\SystemHealthController;
use App\Modules\Security\Controllers\Admin\SecuritySettingController;
use Illuminate\Support\Facades\Route;

/**
 * Security Module Web Routes
 *
 * Semua route di sini memerlukan:
 * - Sudah login (auth)
 * - Session valid (ensure.session.version)
 * - Fingerprint device cocok (verify.fingerprint)
 * - Memiliki role: super-admin, admin, atau security-officer
 *
 * Setiap endpoint juga dilindungi oleh permission granular
 * sehingga role saja tidak cukup — permission di role harus sesuai.
 */
Route::middleware([
    'web',
    'auth',
    'ensure.session.version',
    'verify.fingerprint',
    'role:super-admin,admin,security-officer',
])->prefix('admin/security')->name('admin.security.')->group(function () {

    // ── Monitoring & Logs ────────────────────────────────────────────────
    Route::get('/logs', [SecurityController::class, 'logs'])
        ->middleware('permission:login-logs.view')
        ->name('logs.index');

    Route::get('/logs/{log}/details', [SecurityController::class, 'logDetails'])
        ->middleware('permission:login-logs.view')
        ->name('logs.show');

    Route::post('/logs/bulk-delete', [SecurityController::class, 'bulkDeleteLogs'])
        ->middleware('permission:login-logs.delete')
        ->name('logs.bulk-delete');

    // ── Device Management ────────────────────────────────────────────────
    Route::get('/devices', [SecurityController::class, 'devices'])
        ->middleware('permission:devices.view')
        ->name('devices.index');

    Route::get('/devices/{device}/details', [SecurityController::class, 'deviceDetails'])
        ->middleware('permission:devices.view')
        ->name('devices.show');

    Route::post('/devices/{device}/revoke', [SecurityController::class, 'revokeDevice'])
        ->middleware('permission:devices.revoke')
        ->name('devices.revoke');

    // ── OTP Logs ─────────────────────────────────────────────────────────
    Route::get('/otps', [SecurityController::class, 'otps'])
        ->middleware('permission:login-logs.view')
        ->name('otps.index');

    // ── IP Blacklist ──────────────────────────────────────────────────────
    Route::get('/blacklist', [SecurityController::class, 'blacklist'])
        ->middleware('permission:ip-list.view')
        ->name('blacklist.index');

    Route::post('/blacklist', [SecurityController::class, 'storeBlacklist'])
        ->middleware('permission:ip-list.blacklist')
        ->name('blacklist.store');

    Route::delete('/blacklist/{blacklist}', [SecurityController::class, 'destroyBlacklist'])
        ->middleware('permission:ip-list.blacklist')
        ->name('blacklist.destroy');

    // ── IP Whitelist ──────────────────────────────────────────────────────
    Route::get('/whitelist', [SecurityController::class, 'whitelist'])
        ->middleware('permission:ip-list.view')
        ->name('whitelist.index');

    Route::post('/whitelist', [SecurityController::class, 'storeWhitelist'])
        ->middleware('permission:ip-list.whitelist')
        ->name('whitelist.store');

    Route::delete('/whitelist/{whitelist}', [SecurityController::class, 'destroyWhitelist'])
        ->middleware('permission:ip-list.whitelist')
        ->name('whitelist.destroy');

    // ── System Health ─────────────────────────────────────────────────────
    Route::get('/health', [SystemHealthController::class, 'index'])
        ->middleware('permission:system.health')
        ->name('health');
});
