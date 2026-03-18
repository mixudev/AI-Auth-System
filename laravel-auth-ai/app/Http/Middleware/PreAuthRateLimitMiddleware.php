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

        // Kunci unik berdasarkan kombinasi email yang dicoba dan IP
        $key = $this->buildRateLimitKey($request);

        // Periksa apakah batas sudah terlampaui
        if ($this->limiter->tooManyAttempts($key, $maxAttempts)) {
            $secondsUntilFree = $this->limiter->availableIn($key);

            Log::channel('security')->warning('Rate limit login terlampaui', [
                'ip_address'    => $request->ip(),
                'email_attempted' => $request->input('email'),
                'retry_after'   => $secondsUntilFree,
            ]);

            return $this->buildThrottleResponse($secondsUntilFree);
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
     * Bangun kunci rate limit yang unik.
     * Format: login|{hash_email}|{hash_ip}
     */
    private function buildRateLimitKey(Request $request): string
    {
        $emailHash = sha1(strtolower((string) $request->input('email', '')));
        $ipHash    = sha1($request->ip());

        return "login|{$emailHash}|{$ipHash}";
    }

    /**
     * Bangun respons JSON yang informatif saat rate limit tercapai.
     */
    private function buildThrottleResponse(int $retryAfterSeconds): JsonResponse
    {
        $retryAfterMinutes = ceil($retryAfterSeconds / 60);

        return response()->json([
            'message'    => "Terlalu banyak percobaan login. Coba lagi dalam {$retryAfterMinutes} menit.",
            'error_code' => 'TOO_MANY_ATTEMPTS',
            'retry_after' => $retryAfterSeconds,
        ], Response::HTTP_TOO_MANY_REQUESTS)
        ->withHeaders([
            'Retry-After' => $retryAfterSeconds,
        ]);
    }
}
