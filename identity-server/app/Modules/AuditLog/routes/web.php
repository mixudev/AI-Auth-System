<?php

use App\Modules\AuditLog\Controllers\Admin\AuditLogController;
use App\Modules\AuditLog\Controllers\Admin\LogCenterController;
use Illuminate\Support\Facades\Route;

/**
 * AuditLog Module Web Routes
 * 
 * Mengatur rute untuk melihat riwayat aktivitas sistem.
 */
Route::middleware(['web', 'auth'])->prefix('admin/logs')->name('audit-logs.')->group(function () {
    
    // Pusat Monitoring Terpadu (Auth + Audit)
    Route::get('/', [LogCenterController::class, 'index'])->name('center');

    // Detail Monitoring Aktivitas
    Route::get('/audit-list', [AuditLogController::class, 'index'])->name('index');
    Route::get('/audit-list/{auditLog}', [AuditLogController::class, 'show'])->name('show');
});
