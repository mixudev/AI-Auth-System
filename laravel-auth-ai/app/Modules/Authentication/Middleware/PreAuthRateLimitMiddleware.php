<?php

namespace App\Modules\Authentication\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use App\Modules\Security\Services\GeoIpService;
use App\Modules\Security\Services\DeviceFingerprintService;
use App\Modules\WhatsAppGateway\Jobs\SendWhatsAppNotification;

class PreAuthRateLimitMiddleware
{
    /*
    |--------------------------------------------------------------------------
    | Middleware untuk membatasi frekuensi percobaan login.
    |
    | Dua lapis perlindungan:
    |   Layer 1 — Per-IP global  : ip_max_attempts/decay_minutes dari satu IP
    |             [H-01 FIX] Mencegah bypass rate-limit dengan rotasi email.
    |   Layer 2 — Per-email + IP : max_attempts/decay_minutes per kombinasi
    |
    | [H-07 FIX] Backend CAPTCHA Enforcement:
    |   Setelah captcha_after gagal, token CAPTCHA wajib dikirim dan diverifikasi
    |   di backend sebelum request diteruskan ke controller.
    |--------------------------------------------------------------------------
    */

    public function __construct(
        private readonly RateLimiter $limiter,
        private readonly DeviceFingerprintService $fingerprintService
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $config        = config('security.rate_limit');
        $maxAttempts   = (int) ($config['max_attempts'] ?? 5);
        $decayMins     = (int) ($config['decay_minutes'] ?? 15);
        $captchaAfter  = (int) ($config['captcha_after'] ?? 3);
        $challengeType = $config['challenge'] ?? 'captcha';
        $ipMaxAttempts = (int) ($config['ip_max_attempts'] ?? 20);
        $hardLimit     = (int) ($config['hard_limit'] ?? 10);
        $maxCaptchaErr = (int) ($config['max_captcha_errors'] ?? 5);
        $isCaptchaConfigured = $this->isCaptchaConfigured();
        $captchaDebugLog = (bool) ($config['captcha_debug_log'] ?? false);

        $ip      = $this->fingerprintService->getRealIp($request);
        $context = $this->determineContext($request);

        Log::channel('security')->debug('[RateLimit] Handled request', [
            'ip'            => $ip,
            'challengeType' => $challengeType,
            'context'       => $context,
        ]);

        if ($captchaDebugLog) {
            Log::channel('security')->info('[Captcha] Runtime mode status', [
                'mode'             => $challengeType,
                'is_configured'    => $isCaptchaConfigured,
                'site_key_present' => (string) config('services.captcha.site_key', '') !== '',
                'secret_present'   => (string) config('services.captcha.secret', '') !== '',
                'verify_url'       => (string) config('services.captcha.verify_url', ''),
            ]);
        }

        // ── Layer 1: Rate limit per-IP (global, semua email) ─────────────────
        $ipOnlyKey = "ratelimit:ip:{$context}:" . sha1($ip);
        if ($this->limiter->tooManyAttempts($ipOnlyKey, $ipMaxAttempts)) {
            $waitSeconds = $this->limiter->availableIn($ipOnlyKey);
            Log::channel('security')->info('[RateLimit] Block Layer 1 (IP)', ['ip' => $ip]);
            
            // Kirim Alert WA (Layer 1)
            $this->notifySecurityAlert($request, 'Global IP Block (Layer 1)', $context);
            
            return $this->buildThrottleResponse($request, $waitSeconds, 'global_ip');
        }

        // ── Layer 2: Rate limit per-email + IP (spesifik) ────────────────────
        $key = $this->buildRateLimitKey($request, $context);
        $isTooMany = $this->limiter->tooManyAttempts($key, $maxAttempts);

        Log::channel('security')->debug('[RateLimit] Check Layer 2', [
            'isTooMany' => $isTooMany,
            'attempts'  => $this->limiter->attempts($key),
        ]);

        // Jika mode throttle, langsung blokir keras jika melebihi maxAttempts
        if ($isTooMany && $challengeType === 'throttle') {
            $waitSeconds = $this->limiter->availableIn($key);
            Log::channel('security')->info('[RateLimit] Block Layer 2 (Throttle Mode)', ['key' => $key]);
            return $this->buildThrottleResponse($request, $waitSeconds, $context);
        }

        // Lapis 3: Hard Limit (Absolute Block) - Mencegah brute-force persisten meski pakai captcha
        if ($this->limiter->tooManyAttempts($key, $hardLimit)) {
            $waitSeconds = $this->limiter->availableIn($key);
            Log::channel('security')->info('[RateLimit] Block Layer 3 (Hard Limit)', ['key' => $key]);
            
            // Kirim Alert WA (Layer 3)
            $this->notifySecurityAlert($request, 'Hard Limit Reached (Layer 3)', $context);

            return $this->buildThrottleResponse($request, $waitSeconds, 'hard_limit');
        }

        // ── [H-07] Backend CAPTCHA Enforcement ───────────────────────────────
        $captchaRequiredKey = "captcha_required:{$key}";
        $captchaFailKey     = "captcha_failures:{$key}";

        if ($challengeType === 'captcha' && (Cache::has($captchaRequiredKey) || $isTooMany)) {
            if (! $isCaptchaConfigured) {
                Log::channel('security')->error('[RateLimit] CAPTCHA challenge aktif tetapi konfigurasi belum lengkap.', [
                    'site_key_present' => (string) config('services.captcha.site_key', '') !== '',
                    'secret_present'   => (string) config('services.captcha.secret', '') !== '',
                ]);

                return $this->buildCaptchaConfigurationResponse($request);
            }
            
            // Cek apakah sudah terlalu banyak gagal CAPTCHA
            if ((int) Cache::get($captchaFailKey, 0) >= $maxCaptchaErr) {
                Log::channel('security')->info('[RateLimit] Block (Captcha Fail Limit)', ['key' => $key]);
                return $this->buildThrottleResponse($request, $decayMins * 60, 'captcha_fail');
            }

            $captchaToken = $request->input('captcha_token');
            Log::channel('security')->debug('[RateLimit] Verifying Captcha', ['has_token' => !empty($captchaToken)]);

            if (! $this->verifyCaptchaToken($captchaToken)) {
                
                // Catat kegagalan CAPTCHA
                $fails = (int) Cache::get($captchaFailKey, 0) + 1;
                Cache::put($captchaFailKey, $fails, now()->addMinutes($decayMins));

                Log::channel('security')->info('[RateLimit] Captcha verification failed', ['fails' => $fails]);

                if ($request->expectsJson() || $request->is('api/*')) {
                    return response()->json([
                        'message'          => 'Verifikasi Keamanan diperlukan.',
                        'error_code'       => 'CAPTCHA_REQUIRED',
                        'requires_captcha' => true,
                    ], Response::HTTP_TOO_MANY_REQUESTS);
                }

                return back()
                    ->withInput($request->except('password', 'captcha_token'))
                    ->withErrors(['captcha_token' => 'Harap lengkapi verifikasi keamanan untuk membuktikan Anda bukan robot.'])
                    ->with('requires_captcha', true);
            }

            // Token valid → bersihkan SEMUA limiter untuk key ini agar user bisa mencoba lagi dengan "bersih"
            Log::channel('security')->info('[RateLimit] Captcha success, clearing limiters', ['key' => $key]);
            Cache::forget($captchaRequiredKey);
            Cache::forget($captchaFailKey);
            $this->limiter->clear($key);
            if ($request->hasSession()) {
                $request->session()->forget('requires_captcha');
            }
        }

        // ── Catat percobaan pada kedua layer ──────────────────────────────────
        $this->limiter->hit($key, $decayMins * 60);
        $this->limiter->hit($ipOnlyKey, $decayMins * 60);

        $currentAttempts = $this->limiter->attempts($key);

        $response = $next($request);

        // Setelah N gagal → set flag CAPTCHA required di cache
        if ($currentAttempts >= $captchaAfter && $challengeType === 'captcha') {
            Cache::put($captchaRequiredKey, true, now()->addMinutes($decayMins));
            $response->headers->set('X-Captcha-Required', 'true');
            
            // Simpan persisten agar UI tetap menampilkan CAPTCHA sampai lolos verifikasi.
            if ($request->hasSession()) {
                $request->session()->put('requires_captcha', true);
            }
        }

        return $response;
    }

    /**
     * Verifikasi token CAPTCHA ke penyedia eksternal (hCaptcha / Turnstile).
     */
    private function verifyCaptchaToken(?string $token): bool
    {
        if (empty($token)) {
            return false;
        }

        $secret = config('services.captcha.secret');

        if (empty($secret)) {
            Log::channel('security')->error('CAPTCHA secret belum dikonfigurasi');
            return false;
        }

        try {
            $verifyUrl = config('services.captcha.verify_url', 'https://challenges.cloudflare.com/turnstile/v0/siteverify');
            $result    = Http::asForm()
                ->timeout(5)
                ->post($verifyUrl, [
                    'secret'   => $secret,
                    'response' => $token,
                ]);

            return (bool) $result->json('success', false);
        } catch (\Throwable $e) {
            Log::channel('security')->warning('Verifikasi CAPTCHA gagal (exception)', [
                'error' => $e->getMessage(),
            ]);

            // Fail-closed untuk mencegah bypass saat provider error
            return false;
        }
    }

    private function isCaptchaConfigured(): bool
    {
        $siteKey = (string) config('services.captcha.site_key', '');
        $secret  = (string) config('services.captcha.secret', '');

        return $siteKey !== '' && $secret !== '';
    }

    /**
     * Tentukan konteks permintaan berdasarkan nama route.
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
     * Bangun kunci rate limit per-email + IP + konteks.
     */
    private function buildRateLimitKey(Request $request, string $context): string
    {
        $emailHash = sha1(strtolower((string) $request->input('email', '')));
        $ipHash    = sha1($this->fingerprintService->getRealIp($request));

        return "{$context}|{$emailHash}|{$ipHash}";
    }

    /**
     * Bangun response 429 yang kontekstual (mendukung Web maupun API).
     */
    private function buildThrottleResponse(Request $request, int $retryAfterSeconds, string $context = 'login'): Response
    {
        $retryAfterMinutes = (int) ceil($retryAfterSeconds / 60);

        $message = match ($context) {
            'forgot_password' => "Terlalu banyak permintaan reset password. Coba lagi dalam {$retryAfterMinutes} menit.",
            'reset_password'  => "Terlalu banyak percobaan reset password. Coba lagi dalam {$retryAfterMinutes} menit.",
            'global_ip'       => "Terlalu banyak aktivitas dari IP Anda. Akses dibatasi sementara ({$retryAfterMinutes} menit).",
            'captcha_fail'    => "Terlalu banyak kegagalan verifikasi keamanan. Silakan coba lagi dalam {$retryAfterMinutes} menit.",
            'hard_limit'      => "Keamanan sistem: Terlalu banyak percobaan login pada akun ini. Silakan coba lagi nanti.",
            default           => "Terlalu banyak percobaan login gagal. Coba lagi dalam {$retryAfterMinutes} menit.",
        };

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'message'     => $message,
                'error_code'  => 'TOO_MANY_ATTEMPTS',
                'retry_after' => $retryAfterSeconds,
            ], Response::HTTP_TOO_MANY_REQUESTS)
                ->withHeaders([
                    'Retry-After' => $retryAfterSeconds,
                ]);
        }

        return back()
            ->withInput($request->except('password', 'captcha_token'))
            ->withErrors(['email' => $message])
            ->withHeaders([
                'Retry-After' => $retryAfterSeconds,
            ]);
    }

    private function buildCaptchaConfigurationResponse(Request $request): Response
    {
        $message = 'Mode CAPTCHA aktif, tetapi konfigurasi belum lengkap. Periksa CAPTCHA_SITE_KEY dan CAPTCHA_SECRET.';

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'message'          => $message,
                'error_code'       => 'CAPTCHA_CONFIG_ERROR',
                'requires_captcha' => true,
                'captcha_status'   => [
                    'mode'             => config('security.rate_limit.challenge', 'captcha'),
                    'site_key_present' => (string) config('services.captcha.site_key', '') !== '',
                    'secret_present'   => (string) config('services.captcha.secret', '') !== '',
                ],
            ], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        return back()
            ->withInput($request->except('password', 'captcha_token'))
            ->withErrors(['captcha_token' => $message])
            ->with('requires_captcha', true)
            ->with('captcha_config_error', $message);
    }

    /**
     * Kirim notifikasi WhatsApp saat terdeteksi pelanggaran keamanan serius.
     */
    private function notifySecurityAlert(Request $request, string $reason, string $context): void
    {
        try {
            // Mencegah spam notifikasi WA untuk IP/Key yang sama dalam jangka waktu singkat
            $ip = $this->fingerprintService->getRealIp($request);
            $email = $request->input('email', 'N/A');
            $lockKey = "wa_alert_sent:" . sha1($ip . $reason);

            if (Cache::has($lockKey)) {
                return;
            }

            $time = now()->format('Y-m-d H:i:s');
            
            $message = "🚨 *SECURITY ALERT - MIXUAUTH* 🚨\n\n"
                     . "Terdeteksi aktivitas mencurigakan:\n"
                     . "--------------------------------\n"
                     . "📍 *Reason:* {$reason}\n"
                     . "📧 *Email:* {$email}\n"
                     . "🌐 *IP:* {$ip}\n"
                     . "🕒 *Waktu:* {$time}\n"
                     . "🔄 *Context:* {$context}\n\n"
                     . "⚠️ _Sistem telah melakukan pemblokiran sementara._";

            // Dispatch job ke queue notifications-high agar terkirim cepat
            SendWhatsAppNotification::dispatch($message)->onQueue('notifications-high');

            // Kunci agar tidak kirim lagi untuk alasan yang sama dari IP yang sama selama 30 menit
            Cache::put($lockKey, true, now()->addMinutes(30));
            
        } catch (\Throwable $e) {
            Log::channel('security')->error('[WhatsApp] Failed to dispatch alert', [
                'error' => $e->getMessage()
            ]);
        }
    }
}
