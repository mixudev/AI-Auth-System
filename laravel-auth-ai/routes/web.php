<?php

use App\Http\Controllers\TimezoneController;
use App\Http\Controllers\Web\EmailVerificationController;
use App\Http\Controllers\Web\WebAuthController;
use Illuminate\Support\Facades\Route;


Route::get('/', fn() => redirect()->route('login'));

Route::middleware('guest')->group(function () {
    Route::get('/login', [WebAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [WebAuthController::class, 'login'])
        ->middleware('pre.auth.ratelimit')
        ->name('login.post');
    Route::get('/auth/mfa/verify', [WebAuthController::class, 'showMfaVerify'])->name('auth.mfa.verify');
    Route::post('/auth/mfa/verify', [WebAuthController::class, 'verifyMfa'])
        ->middleware('throttle:mfa')
        ->name('auth.mfa.verify.post');

    // Forgot Password
    Route::get('/forgot-password', [WebAuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [WebAuthController::class, 'sendResetLink'])
        ->middleware('pre.auth.ratelimit')
        ->name('password.email');
    Route::get('/reset-password/{token}', [WebAuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [WebAuthController::class, 'resetPassword'])
        ->middleware('pre.auth.ratelimit')
        ->name('password.update');
});

    Route::get('/verify-email', function () {
        return redirect()->route('dashboard');
    })->name('verification.notice');

    Route::get('/verify-email/{uuid}/{token}', [\App\Http\Controllers\Web\EmailVerificationController::class, 'verify'])
        ->middleware('throttle:6,1')
        ->name('verification.verify');

    Route::get('/email/verified', [EmailVerificationController::class, 'verified'])
    ->name('verification.verified')
    ->middleware('signed'); // hanya bisa diakses dengan URL signed

Route::middleware(['auth', 'ensure.session.version', 'verify.fingerprint'])->group(function () {
    Route::post('/logout', [WebAuthController::class, 'logout'])->name('logout');

    Route::post('/email/verification-notification', [\App\Http\Controllers\Web\EmailVerificationController::class, 'send'])
        ->middleware('throttle:verification-send')
        ->name('verification.send');

    Route::post('/email/verification-notification/{id}', [\App\Http\Controllers\Web\EmailVerificationController::class, 'sendToUser'])
        ->middleware(['permission:users.edit', 'throttle:verification-send'])
        ->name('verification.send.admin');

});

Route::post('/timezone/set', [TimezoneController::class, 'set'])
    ->middleware(['web', 'throttle:10,1'])
    ->name('timezone.set');

Route::patch('/timezone/update', [TimezoneController::class, 'update'])
    ->middleware(['web', 'auth', 'throttle:10,1'])
    ->name('timezone.update');




use App\Mail\TestMail;
use Illuminate\Support\Facades\Mail;

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


require __DIR__.'/dashboard.php';
require __DIR__.'/security.php';

// [H-03 FIX] Route development hanya dimuat di environment yang bukan production.
// Mencegah bocornya dev-tools jika APP_ENV lupa diubah.
if (app()->environment(['local', 'development', 'testing'])) {
    require __DIR__.'/dev.php';
    require __DIR__.'/email-test.php';
}

