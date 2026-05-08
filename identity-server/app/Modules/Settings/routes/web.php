<?php

use App\Modules\Settings\Controllers\Admin\ConfigurationController;
use Illuminate\Support\Facades\Route;

/**
 * Settings Module Web Routes
 *
 * Semua route memerlukan:
 * - Sudah login (auth) + session/fingerprint valid
 * - Role: super-admin atau admin (bukan security-officer — konfigurasi sistem
 *   hanya boleh diubah oleh administrator, bukan security monitor)
 * - Permission: settings.manage
 *
 * Halaman ini berisi konfigurasi sensitif (SMTP, token policy, security policy).
 */
Route::middleware([
    'web',
    'auth',
    'ensure.session.version',
    'verify.fingerprint',
    'role:super-admin,admin',
    'permission:settings.manage',
])->prefix('admin/settings')->name('settings.')->group(function () {

    // Consolidated System Configurations
    Route::get('/configurations', [ConfigurationController::class, 'index'])
        ->name('configurations.index');

    Route::post('/configurations', [ConfigurationController::class, 'update'])
        ->name('configurations.update');

    Route::post('/configurations/test-mail', [ConfigurationController::class, 'testMail'])
        ->name('configurations.test-mail');
});
