<?php

use App\Http\Controllers\Admin\Security\DevMonitoringController;
use App\Http\Controllers\Admin\Security\DevStatsController;
use App\Http\Controllers\Admin\Security\DevOtpController;
use App\Http\Controllers\Admin\Security\DevLoginLogController;
use App\Http\Controllers\Admin\Security\DevTrustedDeviceController;
use App\Http\Controllers\Admin\Security\DevUserController;
use App\Http\Controllers\Admin\Security\DevIpBlacklistController;
use App\Http\Controllers\Admin\Security\DevIpWhitelistController;
use App\Http\Controllers\Admin\Security\DevSystemErrorController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| DEV ONLY — Auth Monitoring Routes
|--------------------------------------------------------------------------
|
| REMOVE or gate behind middleware (e.g. `dev.only`, `ip.whitelist`)
| before deploying to any shared / production environment.
|
| Protected dengan middleware role:super-admin sebagai lapisan keamanan dasar.
| Pertimbangkan untuk tambahan IP whitelist di Nginx/Apache untuk production.
|
*/

// NOTE:
// Dev monitoring routes telah dipindahkan ke `routes/dev.php` dan di-gate dengan environment.
// Jangan definisikan `/dev/monitoring` di sini agar tidak ikut ter-load pada non-dev env.

/*
|--------------------------------------------------------------------------
| Security Pages (View Only / Placeholder)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:security-officer,admin,super-admin'])->prefix('security')->name('security.')->group(function () {

    Route::get('/logs', fn () => view('security.logs'))
        ->middleware('permission:login-logs.view')
        ->name('logs');

    Route::get('/blacklist', fn () => view('security.blacklist'))
        ->middleware('permission:ip-list.view')
        ->name('blacklist');

    Route::get('/notifications', fn () => view('security.notifications'))
        ->middleware('permission:dashboard.view')
        ->name('notifications');

});
