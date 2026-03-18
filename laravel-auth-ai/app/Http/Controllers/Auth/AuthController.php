<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\VerifyOtpRequest;
use App\Models\User;
use App\Repositories\TrustedDeviceRepository;
use App\Services\AiRiskClientService;
use App\Services\DeviceFingerprintService;
use App\Services\LoginAuditService;
use App\Services\LoginRiskService;
use App\Services\OtpService;
use App\Services\RiskFallbackService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Controller autentikasi utama dengan penilaian risiko berbasis AI.
    |
    | Alur login:
    | 1. Rate limiting (middleware)
    | 2. Validasi kredensial (email + password)
    | 3. Kumpulkan data risiko pre-login
    | 4. Kirim ke FastAPI untuk penilaian AI
    | 5. Eksekusi keputusan: ALLOW | OTP | BLOCK
    |--------------------------------------------------------------------------
    */

    public function __construct(
        private readonly LoginRiskService        $riskService,
        private readonly AiRiskClientService     $aiClient,
        private readonly RiskFallbackService     $fallbackService,
        private readonly OtpService              $otpService,
        private readonly LoginAuditService       $auditService,
        private readonly TrustedDeviceRepository $trustedDeviceRepo,
        private readonly DeviceFingerprintService $fingerprintService,
    ) {}

    /**
     * Proses percobaan login dari pengguna.
     *
     * POST /auth/login
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->only('email', 'password');

        // -- Langkah 1: Temukan pengguna berdasarkan email
        $user = User::where('email', $credentials['email'])->first();

        // -- Langkah 2: Verifikasi password (Argon2id via config hashing)
        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            $this->handleFailedCredentials($request, $credentials['email']);

            return response()->json([
                'message'    => 'Email atau password yang Anda masukkan salah.',
                'error_code' => 'INVALID_CREDENTIALS',
            ], Response::HTTP_UNAUTHORIZED);
        }

        // -- Langkah 3: Periksa status akun
        if (! $user->isActive()) {
            Log::channel('security')->info('Login ditolak: akun tidak aktif', [
                'user_id' => $user->id,
            ]);

            return response()->json([
                'message'    => 'Akun Anda telah dinonaktifkan. Hubungi administrator.',
                'error_code' => 'ACCOUNT_INACTIVE',
            ], Response::HTTP_FORBIDDEN);
        }

        // -- Langkah 4: Kumpulkan sinyal risiko pre-login
        $riskPayload = $this->riskService->prepareRiskPayload($request, $user);

        // -- Langkah 5: Kirim ke AI FastAPI, gunakan fallback jika gagal
        try {
            $assessment = $this->aiClient->sendToFastApi($riskPayload);
        } catch (\RuntimeException $e) {
            Log::channel('security')->error('AI tidak tersedia, fallback aktif', [
                'error'   => $e->getMessage(),
                'user_id' => $user->id,
            ]);
            $assessment = $this->fallbackService->assess($riskPayload);
        }

        // -- Langkah 6: Eksekusi keputusan berdasarkan hasil penilaian
        // -- Jika OTP tidak diaktifkan, ubah keputusan 'OTP' menjadi 'ALLOW'
        $decision = $assessment->decision;
        if ($decision === 'OTP' && !config('security.otp.enabled')) {
            $decision = 'ALLOW';
            Log::channel('security')->info('OTP dilewati: OTP_ENABLED=false', [
                'user_id' => $user->id,
            ]);
        }

        return match ($decision) {
            'ALLOW' => $this->handleAllowDecision($request, $user, $assessment),
            'OTP'   => $this->handleOtpDecision($request, $user, $assessment),
            'BLOCK' => $this->handleBlockDecision($request, $user, $assessment),
            default => $this->handleBlockDecision($request, $user, $assessment), // Fail-safe
        };
    }

    /**
     * Verifikasi kode OTP yang dikirimkan pengguna.
     *
     * POST /auth/otp/verify
     */
    public function verifyOtp(VerifyOtpRequest $request): JsonResponse
    {
        $result = $this->otpService->verifyOtp(
            $request->input('session_token'),
            $request->input('otp_code')
        );

        if (! $result['success']) {
            $message = match ($result['reason']) {
                'expired'              => 'Kode OTP sudah kedaluwarsa. Silakan login ulang untuk mendapatkan kode baru.',
                'max_attempts_exceeded' => 'Batas percobaan OTP terlampaui. Silakan login ulang.',
                'invalid_session'      => 'Sesi OTP tidak valid atau sudah digunakan.',
                default                => 'Kode OTP yang Anda masukkan salah.',
            };

            $statusCode = in_array($result['reason'], ['expired', 'max_attempts_exceeded', 'invalid_session'])
                ? Response::HTTP_GONE
                : Response::HTTP_UNPROCESSABLE_ENTITY;

            return response()->json([
                'message'    => $message,
                'error_code' => strtoupper($result['reason']),
            ], $statusCode);
        }

        // -- OTP valid: selesaikan proses login
        $user = User::findOrFail($result['user_id']);

        return $this->finalizeLogin($request, $user);
    }

    /**
     * Logout pengguna dan batalkan sesi aktif.
     *
     * POST /auth/logout
     */
    public function logout(Request $request): JsonResponse
    {
        $userId = Auth::id();

        Auth::logout();
        
        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        Log::channel('security')->info('Pengguna logout', [
            'user_id'    => $userId,
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'message' => 'Anda berhasil keluar dari sistem.',
        ]);
    }

    // -----------------------------------------------------------------------
    // Private: Penanganan Keputusan AI
    // -----------------------------------------------------------------------

    /**
     * Keputusan ALLOW: selesaikan login langsung.
     */
    private function handleAllowDecision(Request $request, User $user, $assessment): JsonResponse
    {
        $this->auditService->recordSuccess($request, $user, $assessment);
        $this->clearFailedAttempts($request, $user->email);

        return $this->finalizeLogin($request, $user);
    }

    /**
     * Keputusan OTP: simpan konteks dan kirim kode OTP.
     */
    private function handleOtpDecision(Request $request, User $user, $assessment): JsonResponse
    {
        $this->auditService->recordOtpRequired($request, $user, $assessment);

        $otpData = $this->otpService->generateOtp(
            $user,
            $request->ip(),
            $this->fingerprintService->generate($request)
        );

        // -- Kirim kode OTP ke pengguna via email atau SMS
        // Pastikan channel pengiriman dikonfigurasi di config/security.php
        $this->dispatchOtpNotification($user, $otpData['otp_code']);

        return response()->json([
            'message'        => 'Kode verifikasi telah dikirimkan. Silakan periksa email atau SMS Anda.',
            'requires_otp'   => true,
            'session_token'  => $otpData['session_token'], // Digunakan untuk endpoint verifyOtp
            'expires_in'     => config('security.otp.expires_minutes') . ' menit',
        ], Response::HTTP_ACCEPTED);
    }

    /**
     * Keputusan BLOCK: tolak login dan catat insiden.
     */
    private function handleBlockDecision(Request $request, User $user, $assessment): JsonResponse
    {
        $this->auditService->recordBlocked($request, $user, $assessment);

        return response()->json([
            'message'    => 'Login tidak dapat dilanjutkan karena aktivitas mencurigakan terdeteksi. Hubungi administrator jika ini adalah kesalahan.',
            'error_code' => 'LOGIN_BLOCKED',
        ], Response::HTTP_FORBIDDEN);
    }

    // -----------------------------------------------------------------------
    // Private: Helper Methods
    // -----------------------------------------------------------------------

    /**
     * Selesaikan proses login: buat sesi, daftarkan perangkat, perbarui record.
     */
    // private function finalizeLogin(Request $request, User $user): JsonResponse
    // {
    //     // Buat sesi Laravel
    //     Auth::login($user);
    //     $request->session()->regenerate();

    //     // Ikat sesi ke fingerprint perangkat
    //     session(['auth_device_fingerprint' => $this->fingerprintService->generate($request)]);

    //     // Daftarkan perangkat sebagai perangkat terpercaya
    //     $this->trustedDeviceRepo->trustDevice($user->id, $request);

    //     // Perbarui timestamp login terakhir
    //     $user->recordLogin($request->ip());

    //     return response()->json([
    //         'message' => 'Login berhasil. Selamat datang kembali!',
    //         'user'    => [
    //             'id'    => $user->id,
    //             'name'  => $user->name,
    //             'email' => $user->email,
    //         ],
    //     ]);
    // }
    private function finalizeLogin(Request $request, User $user): JsonResponse
    {

        // Buat sesi Laravel jika tersedia (Web context)
        Auth::login($user);
        
        if ($request->hasSession()) {
            $request->session()->regenerate();
            // Ikat sesi ke fingerprint perangkat (jika session ada)
            $request->session()->put('auth_device_fingerprint', $this->fingerprintService->generate($request));
        }
        
        // Catat login & daftarkan trusted device (selalu dijalankan)
        $user->recordLogin($request->ip());
        $this->trustedDeviceRepo->trustDevice($user->id, $request);

        return response()->json([
            'message' => 'Login berhasil. Selamat datang kembali!',
            'user'    => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
            ],
        ]);
    }

    /**
     * Tangani percobaan login dengan kredensial yang salah.
     * Tambah counter gagal di cache untuk sinyal risiko.
     */
    private function handleFailedCredentials(Request $request, string $email): void
    {
        // Catat ke log audit
        $this->auditService->recordFailedPassword($request, $email);

        // Tambah counter di cache (digunakan sebagai sinyal oleh LoginRiskService)
        $user = User::where('email', $email)->first();
        if ($user) {
            $cacheKey = "failed_attempts:{$user->id}:{$request->ip()}";
            Cache::increment($cacheKey);
            Cache::put($cacheKey, Cache::get($cacheKey, 1), now()->addMinutes(30));
        }
    }

    /**
     * Bersihkan counter percobaan gagal setelah login berhasil.
     */
    private function clearFailedAttempts(Request $request, string $email): void
    {
        $user = User::where('email', $email)->first();
        if ($user) {
            Cache::forget("failed_attempts:{$user->id}:{$request->ip()}");
        }
    }

    /**
     * Kirim notifikasi OTP ke pengguna.
     * Ganti implementasi sesuai channel yang dikonfigurasi.
     */
    private function dispatchOtpNotification(User $user, string $otpCode): void
    {
        $channel = config('security.otp.channel', 'email');

        if ($channel === 'email') {
            // Kirim via email — gunakan queued notification untuk performa
            $user->notify(new \App\Notifications\OtpCodeNotification($otpCode));
        }

        // Channel SMS dapat ditambahkan di sini
    }
}
