<?php

use App\Http\Controllers\Web\WebAuthController;
use App\Http\Controllers\Dev\DevMonitoringController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TimezoneController;


Route::get('/', fn() => redirect()->route('login'));

Route::middleware('guest')->group(function () {
    Route::get('/login', [WebAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [WebAuthController::class, 'login'])->name('login.post');
    Route::get('/otp', [WebAuthController::class, 'showOtp'])->name('otp.verify');
    Route::post('/otp', [WebAuthController::class, 'verifyOtp'])->name('otp.verify.post');

    // Forgot Password
    Route::get('/forgot-password', [WebAuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [WebAuthController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [WebAuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [WebAuthController::class, 'resetPassword'])->name('password.update');
});

Route::middleware('auth')->group(function () {

    Route::resource('users', \App\Http\Controllers\Web\UserController::class);
    
    Route::post('/logout', [WebAuthController::class, 'logout'])->name('logout');


});


Route::post('/timezone/set', [TimezoneController::class, 'set'])
    ->middleware(['web', 'throttle:10,1'])
    ->name('timezone.set');

Route::patch('/timezone/update', [TimezoneController::class, 'update'])
    ->middleware(['web', 'auth', 'throttle:10,1'])
    ->name('timezone.update');


// cek data session lengkap dalam bentuk JSON (untuk dev monitoring) tanpa controller
Route::get('/session', function () {
    return response()->json([
        'session' => session()->all(),
        'user'    => auth()->user(),
    ]);
})->middleware(['web', 'auth', 'throttle:5,1'])->name('dev.session');

use App\Mail\TestMail;

Route::get('/test-email', function () {

    abort_if(app()->environment('production'), 404);

Mail::to('Hello@gmail.com')->send(new TestMail(
    userName: 'John Doe',
    userEmail: 'Hello@gmail.com',
    actionUrl: url('/verify?token='),
    plan: 'Pro',
    createdAt: now()->format('d M Y'),
    mailSubject: 'Verifikasi Email Anda — YourApp',
    unsubscribeUrl: config('app.url') . '/unsubscribe',
));

    return response()->json([
        'status' => 'ok',
        'content' => 'Email test berhasil dikirim',
    ]);
});


// panggil route scurity (dev monitoring)
require __DIR__.'/scurity.php';
require __DIR__.'/dashboard.php';
require __DIR__.'/email-test.php';