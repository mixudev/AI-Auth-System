<?php

use App\Http\Middleware\VerifySessionFingerprintMiddleware;
use App\Http\Middleware\EnsureSessionVersionMiddleware;
use App\Http\Middleware\CheckRole;
use App\Http\Middleware\CheckPermission;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

/*
|--------------------------------------------------------------------------
| Bootstrap Aplikasi Laravel 11
|--------------------------------------------------------------------------
| Laravel 11 menggunakan file ini sebagai pengganti Kernel.php.
| Middleware global, route, dan exception handler dikonfigurasi di sini.
|--------------------------------------------------------------------------
*/

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        apiPrefix: 'api',
    )
    ->withMiddleware(function (Middleware $middleware) {

        // Middleware global untuk semua request API
        // Catatan security: API idealnya stateless. Hindari set cookie identitas perangkat di group API
        // kecuali Anda memang sengaja membuat API stateful (SPA + cookie auth).
        $middleware->api(append: [
            // Header keamanan dasar ditangani oleh reverse proxy (Nginx/Traefik)
            // Middleware tambahan dapat ditambahkan di sini sesuai kebutuhan
        ]);

        // Daftarkan alias middleware untuk kemudahan penggunaan di routes
        $middleware->alias([
            'pre.auth.ratelimit' => \App\Http\Middleware\PreAuthRateLimitMiddleware::class,
            'verify.fingerprint' => VerifySessionFingerprintMiddleware::class,
            'ensure.session.version' => EnsureSessionVersionMiddleware::class,
            'role' => CheckRole::class,
            'permission' => CheckPermission::class,
        ]);

        // Kecualikan route auth dari CSRF (untuk API stateless)
        // Deteksi timezone untuk semua request web
        $middleware->web(append: [
            \App\Http\Middleware\DeviceIdentifierMiddleware::class,
            \App\Http\Middleware\TimezoneMiddleware::class,
            \App\Http\Middleware\SecurityHeadersMiddleware::class, // [M-05 FIX]
        ]);

        $trustedProxies = array_values(array_filter(array_map(
            static fn (string $proxy) => trim($proxy),
            explode(',', (string) env('TRUSTED_PROXIES', '127.0.0.1,::1'))
        )));

        $middleware->trustProxies(at: $trustedProxies);
    })
    ->withExceptions(function (Exceptions $exceptions) {

        // Kembalikan semua exception dalam format JSON untuk API
        $exceptions->render(function (\Throwable $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return \App\Exceptions\ApiExceptionHandler::render($e);
            }
        });

    })
    
    ->withProviders([
        App\Providers\DashboardServiceProvider::class,
        App\Providers\TimezoneServiceProvider::class,

    ])
    ->create();
