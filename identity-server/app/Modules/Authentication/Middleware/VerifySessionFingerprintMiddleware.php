<?php

namespace App\Modules\Authentication\Middleware;

use App\Modules\Security\Services\DeviceFingerprintService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class VerifySessionFingerprintMiddleware
{
    /*
    |--------------------------------------------------------------------------
    | Middleware untuk memverifikasi bahwa sesi aktif berasal dari
    | perangkat yang sama dengan saat login.
    |
    | Mencegah session hijacking: jika fingerprint berubah,
    | sesi dianggap tidak valid dan pengguna diarahkan login ulang.
    |--------------------------------------------------------------------------
    */

    public function __construct(
        private readonly DeviceFingerprintService $fingerprintService
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        // Lewati jika tidak ada sesi yang aktif
        if (! Auth::check()) {
            return $next($request);
        }

        // Lewati jika fitur ini dinonaktifkan di konfigurasi
        if (! config('security.session.bind_to_fingerprint', true)) {
            return $next($request);
        }

        $currentFingerprint = $this->fingerprintService->generate($request);
        $storedFingerprint  = session('auth_device_fingerprint');

        // Jika sesi belum memiliki fingerprint (sesi lama), simpan sekarang
        if (empty($storedFingerprint)) {
            session(['auth_device_fingerprint' => $currentFingerprint]);
            return $next($request);
        }

        // Bandingkan fingerprint
        if (! hash_equals($storedFingerprint, $currentFingerprint)) {
            // [TRANSISI FIX] Periksa apakah ini adalah transisi dari legacy ke token-based
            // Jika disimpan sebagai legacy dan sekarang ada token, kita izinkan update satu kali
            $userAgent = strtolower(trim($request->userAgent() ?? 'none'));
            $legacyFingerprint = hash('sha256', "legacy|{$userAgent}");

            if (hash_equals($storedFingerprint, $legacyFingerprint)) {
                // Sesi ini sebelumnya legacy, sekarang perangkat memberikan ID yang valid.
                // Kita update sesi ke fingerprint baru dan izinkan lewat.
                session(['auth_device_fingerprint' => $currentFingerprint]);
                return $next($request);
            }

            $userId = Auth::id();

            Log::channel('security')->warning('Sesi tidak valid: fingerprint berubah secara ilegal', [
                'user_id'    => $userId,
                'ip_address' => $this->fingerprintService->getRealIp($request),
                'stored'     => substr($storedFingerprint, 0, 8) . '...',
                'current'    => substr($currentFingerprint, 0, 8) . '...',
            ]);

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if (! ($request->expectsJson() || $request->is('api/*'))) {
                return redirect()->route('login')->withErrors([
                    'email' => 'Sesi Anda tidak valid. Silakan login kembali.',
                ]);
            }

            return response()->json([
                'message'    => 'Sesi Anda tidak valid. Silakan login kembali.',
                'error_code' => 'SESSION_INVALID',
            ], Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}
