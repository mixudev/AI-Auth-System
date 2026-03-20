<?php

use App\Http\Controllers\Admin\Dashboard\DashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Dashboard & Security Routes
| File: routes/web.php (tambahkan route ini)
|--------------------------------------------------------------------------
|
| Semua route dashboard dilindungi middleware auth.
| Jika pakai Spatie Permission, tambahkan ->middleware('role:admin').
|
*/

Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard utama
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    // Placeholder routes yang direferensikan di Blade
    // Ganti controller sesuai implementasi masing-masing
    Route::get('/security/logs', fn () => view('security.logs'))
        ->name('security.logs');

    Route::get('/security/blacklist', fn () => view('security.blacklist'))
        ->name('security.blacklist');

    Route::get('/security/notifications', fn () => view('security.notifications'))
        ->name('security.notifications');

});
