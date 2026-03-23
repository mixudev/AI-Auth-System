<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class PreAuthRateLimitMiddleware
{
    /*
    |--------------------------------------------------------------------------
    | Middleware untuk membatasi frekuensi percobaan login.
    |
    | Dijalankan SEBELUM validasi password untuk mencegah brute-force.
    | Penghitung dibagi berdasarkan kombinasi email + IP sehingga:
    | - Satu IP tidak dapat menyerang banyak akun sekaligus
    | - Satu akun tidak dapat diserang dari banyak IP sekaligus
    |--------------------------------------------------------------------------
    */

    public function __construct(
        private readonly RateLimiter $limiter
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $config      = config('security.rate_limit');
        $maxAttempts = (int) ($config['max_attempts'] ?? 5);
        $decayMins   = (int) ($config['decay_minutes'] ?? 15);
        $captchaAfter = (int) ($config['captcha_after'] ?? 3);

        // Tentukan konteks berdasarkan route name (auth.login, auth.password.email, dsb)
        $context = $this->determineContext($request);

        // Kunci unik berdasarkan kombinasi email + IP + KONTEKS
        $key = $this->buildRateLimitKey($request, $context);

        // Periksa apakah batas sudah terlampaui
        if ($this->limiter->tooManyAttempts($key, $maxAttempts)) {
            $secondsUntilFree = $this->limiter->availableIn($key);

            Log::channel('security')->warning('Rate limit ' . $context . ' terlampaui', [
                'ip_address'    => $request->ip(),
                'email_attempted' => $request->input('email'),
                'context'       => $context,
                'retry_after'   => $secondsUntilFree,
            ]);

            return $this->buildThrottleResponse($secondsUntilFree, $context);
        }

        // Tambah hitungan percobaan sebelum meneruskan request
        $this->limiter->hit($key, $decayMins * 60);

        $currentAttempts = $this->limiter->attempts($key);

        $response = $next($request);

        // Jika login gagal (password salah), tambahkan header CAPTCHA jika mendekati batas
        if ($currentAttempts >= $captchaAfter) {
            $response->headers->set('X-Captcha-Required', 'true');
        }

        return $response;
    }

    /**
     * Tentukan konteks permintaan (login, forgot_password, reset_password).
     */
    private function determineContext(Request $request): string
    {
        $routeName = (string) $request->route()?->getName();

        if (str_contains($routeName, 'password.email')) {
            return 'forgot_password';
        }

        if (str_contains($routeName, 'password.update')) {
            return 'reset_password';
        }

        return 'login';
    }

    /**
     * Bangun kunci rate limit yang unik.
     * Format: {context}|{hash_email}|{hash_ip}
     */
    private function buildRateLimitKey(Request $request, string $context): string
    {
        $emailHash = sha1(strtolower((string) $request->input('email', '')));
        $ipHash    = sha1($request->ip());

        return "{$context}|{$emailHash}|{$ipHash}";
    }

    /**
     * Bangun respons JSON yang informatif saat rate limit tercapai.
     */
    private function buildThrottleResponse(int $retryAfterSeconds, string $context = 'login'): JsonResponse
    {
        $retryAfterMinutes = ceil($retryAfterSeconds / 60);

        $message = match($context) {
            'forgot_password' => "Terlalu banyak permintaan reset password. Coba lagi dalam {$retryAfterMinutes} menit.",
            'reset_password'  => "Terlalu banyak percobaan reset password. Coba lagi dalam {$retryAfterMinutes} menit.",
            default           => "Terlalu banyak percobaan login. Coba lagi dalam {$retryAfterMinutes} menit.",
        };

        return response()->json([
            'message'    => $message,
            'error_code' => 'TOO_MANY_ATTEMPTS',
            'retry_after' => $retryAfterSeconds,
        ], Response::HTTP_TOO_MANY_REQUESTS)
        ->withHeaders([
            'Retry-After' => $retryAfterSeconds,
        ]);
    }
}
