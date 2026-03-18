<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Middleware\PreAuthRateLimitMiddleware;
use App\Http\Middleware\VerifySessionFingerprintMiddleware;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Routes Autentikasi API
|--------------------------------------------------------------------------
|
| Semua route login menggunakan PreAuthRateLimitMiddleware yang berjalan
| sebelum controller dieksekusi, memastikan rate limiting terjadi bahkan
| sebelum ada akses ke database.
|
*/

// -- Route publik: tidak memerlukan autentikasi
Route::prefix('auth')->name('auth.')->group(function () {

    // Login dengan penilaian risiko AI
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware(PreAuthRateLimitMiddleware::class)
        ->name('login');

    // Verifikasi OTP (dipanggil setelah keputusan OTP dari AI)
    Route::post('/otp/verify', [AuthController::class, 'verifyOtp'])
        ->middleware(PreAuthRateLimitMiddleware::class)
        ->name('otp.verify');
});

// -- Route yang memerlukan autentikasi
Route::prefix('auth')->name('auth.')->middleware([
    'auth:sanctum',
    VerifySessionFingerprintMiddleware::class,
])->group(function () {

    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout');
});

