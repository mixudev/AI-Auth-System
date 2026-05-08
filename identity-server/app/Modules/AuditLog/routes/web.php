<?php

use App\Modules\AuditLog\Controllers\Admin\AuditLogController;
use App\Modules\AuditLog\Controllers\Admin\LogCenterController;
use Illuminate\Support\Facades\Route;

/**
 * AuditLog Module Web Routes
 *
 * Semua route memerlukan:
 * - Sudah login (auth) + session/fingerprint valid
 * - Role: super-admin, admin, atau security-officer
 * - Permission: audit-logs.view
 *
 * Audit log berisi aktivitas seluruh user dan merupakan data sensitif
 * yang hanya boleh diakses oleh tim keamanan/administrator.
 */
Route::middleware([
    'web',
    'auth',
    'ensure.session.version',
    'verify.fingerprint',
    'role:super-admin,admin,security-officer',
    'permission:audit-logs.view',
])->prefix('admin/logs')->name('audit-logs.')->group(function () {

    // Pusat Monitoring Terpadu (Auth + Audit)
    Route::get('/', [LogCenterController::class, 'index'])->name('center');

    // Detail Monitoring Aktivitas
    Route::get('/audit-list', [AuditLogController::class, 'index'])->name('index');
    Route::get('/audit-list/{auditLog}', [AuditLogController::class, 'show'])->name('show');
});
