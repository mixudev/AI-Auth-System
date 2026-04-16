<?php

namespace App\Services\Security;

use Illuminate\Http\Request;
use Jenssegers\Agent\Agent;

class DeviceFingerprintService
{
    public function __construct(
        private readonly GeoIpService $geoIpService
    ) {}

    /**
     * Dapatkan kode negara berdasarkan IP (ISO 3166-1 alpha-2).
     */
    public function getCountry(string $ip): string
    {
        return $this->geoIpService->getCountryCode($ip);
    }

    /**
     * Dapatkan IP asli pengunjung dengan memeriksa header proxy yang umum.
     */
    public function getRealIp(Request $request): string
    {
        $headers = [
            'CF-Connecting-IP',
            'X-Forwarded-For',
            'X-Real-IP',
            'Client-IP',
            'HTTP_X_FORWARDED_FOR',
        ];

        foreach ($headers as $header) {
            $value = $request->header($header) ?? $request->server($header);
            
            if (!empty($value)) {
                $ips = explode(',', (string) $value);
                foreach ($ips as $ip) {
                    $ip = trim($ip);
                    // Validasi IP dan pastikan bukan IP internal Docker jika ada pilihan lain
                    if (filter_var($ip, FILTER_VALIDATE_IP)) {
                        return $ip;
                    }
                }
            }
        }

        return (string) $request->ip();
    }

    /**
     * Dapatkan rincian spesifik perangkat untuk diteruskan ke AI.
     */
    public function getDetailedDevice(Request $request): array
    {
        $agent = new Agent();
        $agent->setUserAgent($request->userAgent() ?? '');

        $deviceType = 'Desktop';
        if ($agent->isTablet()) {
            $deviceType = 'Tablet';
        } elseif ($agent->isMobile()) {
            $deviceType = 'Mobile';
        } elseif ($agent->isRobot()) {
            $deviceType = 'Robot';
        }

        return [
            'browser'         => $agent->browser() ?: 'Unknown Browser',
            'browser_version' => $agent->version($agent->browser() ?: '') ?: 'Unknown',
            'os'              => $agent->platform() ?: 'Unknown OS',
            'os_version'      => $agent->version($agent->platform() ?: '') ?: 'Unknown',
            'device_type'     => $deviceType,
            'is_bot'          => $agent->isRobot() ? 1 : 0,
            'bot_name'        => $agent->isRobot() ? $agent->robot() : null,
        ];
    }
    /*
    |--------------------------------------------------------------------------
    | Layanan untuk menghasilkan dan memvalidasi fingerprint perangkat.
    |
    | Fingerprint dibuat dari kombinasi atribut HTTP yang tersedia tanpa
    | menyimpan data identifikasi pribadi secara langsung.
    | Nilai akhir di-hash sehingga tidak dapat dibalikkan.
    |--------------------------------------------------------------------------
    */

    /**
     * Hasilkan hash fingerprint unik untuk request saat ini.
     * Menggunakan ID unik dari cookie sebagai komponen identitas utama yang stabil.
     */
    public function generate(Request $request): string
    {
        // Ambil ID dari cookie (Sudah dijamin ada oleh DeviceIdentifierMiddleware)
        $deviceId = $request->cookie('device_trust_id');
        
        // Debug: Pantau kehadiran dan nilai cookie
        if ($deviceId) {
            \Illuminate\Support\Facades\Log::channel('security')->debug('Fingerprint Source: COOKIE', [
                'device_id_tail' => '...' . substr($deviceId, -8),
                'full_hash'      => hash('md5', $deviceId), // Hash untuk tracking aman di log
            ]);
        } else {
            \Illuminate\Support\Facades\Log::channel('security')->warning('Fingerprint Source: MISSING COOKIE (FALLBACK ACTIVE)', [
                'has_any_cookies' => count($request->cookies->all()) > 0,
                'ua'              => $request->userAgent(),
            ]);
        }

        // Jika karena alasan ekstrem cookie benar-benar kosong, fallback ke User-Agent
        if (!$deviceId) {
            $userAgent = strtolower(trim($request->userAgent() ?? 'none'));
            return hash('sha256', "legacy|{$userAgent}");
        }

        return $deviceId;
    }

    /**
     * Dapatkan tanda tangan perangkat (Device Signature) untuk verifikasi integritas.
     * Digunakan untuk memastikan cookie tidak dipindahkan ke perangkat lain yang berbeda jauh.
     */
    public function getDeviceSignature(Request $request): string
    {
        $details = $this->getDetailedDevice($request);
        
        // Kita gunakan Browser Utama dan OS Utama sebagai signature.
        // Versi browser sengaja tidak dimasukkan agar update browser otomatis tidak memicu OTP.
        $browser = $details['browser'];
        $os      = $details['os'];
        $type    = $details['device_type'];

        return hash('sha256', "sig|{$browser}|{$os}|{$type}");
    }

    /**
     * Buat UUID baru untuk identitas perangkat.
     */
    public function generateNewDeviceId(): string
    {
        return (string) \Illuminate\Support\Str::uuid();
    }

    /**
     * Hasilkan label perangkat yang ramah pengguna dari User-Agent string.
     * Digunakan untuk ditampilkan pada halaman manajemen perangkat.
     */
    public function buildDeviceLabel(Request $request): string
    {
        $details = $this->getDetailedDevice($request);
        $browser = trim("{$details['browser']} {$details['browser_version']}");
        $os = trim("{$details['os']} {$details['os_version']}");
        
        $browser = $browser === '' || $browser === 'Unknown Browser Unknown' ? 'Browser Tidak Dikenal' : $browser;
        $os = $os === '' || $os === 'Unknown OS Unknown' ? 'OS Tidak Dikenal' : $os;

        return "{$browser} di {$os}";
    }

    /**
     * Periksa apakah fingerprint saat ini cocok dengan hash yang tersimpan.
     */
    public function matches(Request $request, string $storedHash): bool
    {
        return hash_equals($storedHash, $this->generate($request));
    }
}
