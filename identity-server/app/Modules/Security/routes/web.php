<?php

use App\Modules\Security\Controllers\SecurityController;
use App\Modules\Security\Controllers\SystemHealthController;
use App\Modules\Security\Controllers\Admin\SecuritySettingController;
use Illuminate\Support\Facades\Route;

/**
 * Security Module Web Routes
 * 
 * Mengelola rute monitoring keamanan (Logs, Devices, IP Control)
 * dan Kebijakan Keamanan Sistem (Security Policy).
 */
Route::middleware(['web', 'auth'])->prefix('admin/security')->name('admin.security.')->group(function () {
    
    // ── Monitoring & Logs ────────────────────────────────────────────────
    Route::get('/logs', [SecurityController::class, 'logs'])->name('logs.index');
    Route::get('/logs/{log}/details', [SecurityController::class, 'logDetails'])->name('logs.show');
    Route::post('/logs/bulk-delete', [SecurityController::class, 'bulkDeleteLogs'])->name('logs.bulk-delete');

    // ── Device Management ────────────────────────────────────────────────
    Route::get('/devices', [SecurityController::class, 'devices'])->name('devices.index');
    Route::get('/devices/{device}/details', [SecurityController::class, 'deviceDetails'])->name('devices.show');
    Route::post('/devices/{device}/revoke', [SecurityController::class, 'revokeDevice'])->name('devices.revoke');

    // ── OTP & Access Control ─────────────────────────────────────────────
    Route::get('/otps', [SecurityController::class, 'otps'])->name('otps.index');

    Route::get('/blacklist', [SecurityController::class, 'blacklist'])->name('blacklist.index');
    Route::post('/blacklist', [SecurityController::class, 'storeBlacklist'])->name('blacklist.store');
    Route::delete('/blacklist/{blacklist}', [SecurityController::class, 'destroyBlacklist'])->name('blacklist.destroy');

    Route::get('/whitelist', [SecurityController::class, 'whitelist'])->name('whitelist.index');
    Route::post('/whitelist', [SecurityController::class, 'storeWhitelist'])->name('whitelist.store');
    Route::delete('/whitelist/{whitelist}', [SecurityController::class, 'destroyWhitelist'])->name('whitelist.destroy');

    // ── System Health ────────────────────────────────────────────────────
    Route::get('/health', [SystemHealthController::class, 'index'])->name('health');

});
