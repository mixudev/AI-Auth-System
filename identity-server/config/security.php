<?php

return [

    'admin_emails' => array_values(array_filter(array_map(
        static fn (string $email) => strtolower(trim($email)),
        explode(',', (string) env('ADMIN_EMAILS', ''))
    ))),

    /*
    |--------------------------------------------------------------------------
    | Konfigurasi Threshold Risiko AI
    |--------------------------------------------------------------------------
    | Nilai batas yang menentukan aksi yang diambil berdasarkan risk_score.
    | Semua nilai dapat disesuaikan tanpa mengubah logika aplikasi.
    */
    'risk_thresholds' => [
        'allow'  => 30,   // risk_score < 30 → login langsung diterima
        'otp'    => 60,   // risk_score 30–59 → wajib verifikasi OTP
        // risk_score >= 60 → login diblokir otomatis
    ],

    /*
    |--------------------------------------------------------------------------
    | Konfigurasi OTP
    |--------------------------------------------------------------------------
    */
    'otp' => [
        'enabled'         => env('OTP_ENABLED', true),  // Aktifkan/nonaktifkan OTP
        'length'          => 6,           // Panjang kode OTP
        'expires_minutes' => 5,           // Masa berlaku OTP dalam menit
        'max_attempts'    => 3,           // Maksimum percobaan verifikasi OTP
        'cooldown_seconds' => 60,         // Jeda minimum antar OTP baru
        'channel'         => 'email',     // Channel pengiriman: email | sms
    ],

    /*
    |--------------------------------------------------------------------------
    | Konfigurasi Rate Limiting Login
    |--------------------------------------------------------------------------
    */
    'rate_limit' => [
        'max_attempts'       => (int) env('RATE_LIMIT_MAX_ATTEMPTS', 5), // Percobaan login bebas sebelum muncul captcha (atau sebelum blokir jika challenge=throttle)
        'decay_minutes'      => 15,         // Durasi lockout dalam menit
        'challenge'          => env('RATE_LIMIT_CHALLENGE', 'captcha'), // Opsi challenge: 'captcha' | 'throttle'
        'captcha_after'      => 3,          // Tampilkan CAPTCHA setelah N kali gagal (jika challenge=captcha)
        'max_captcha_errors' => (int) env('RATE_LIMIT_MAX_CAPTCHA_ERRORS', 5), // Batas kesalahan pengisian captcha
        'hard_limit'         => (int) env('RATE_LIMIT_HARD_LIMIT', 10),       // Batas total percobaan login (meskipun captcha benar) sebelum blokir total
        'captcha_debug_log'  => filter_var(env('CAPTCHA_DEBUG_LOG', false), FILTER_VALIDATE_BOOL), // Log status mode captcha true/false
    ],

    /*
    |--------------------------------------------------------------------------
    | Konfigurasi Koneksi AI FastAPI
    |--------------------------------------------------------------------------
    */
    'ai_service' => [
        'base_url'        => env('AI_RISK_SERVICE_URL', 'http://fastapi-risk:8000'),
        'endpoint'        => '/api/v1/risk-score',
        'timeout_seconds' => (int) env('AI_RISK_TIMEOUT', 5),
        'connect_timeout' => 3,
        'api_key'         => env('AI_RISK_API_KEY'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Konfigurasi Sesi & Perangkat
    |--------------------------------------------------------------------------
    */
    'session' => [
        'bind_to_fingerprint' => true,    // Ikat sesi ke fingerprint perangkat
        'trusted_device_days' => 30,      // Durasi kepercayaan perangkat dalam hari
        'trusted_device_cookie_minutes' => 60 * 24 * 30,
    ],

    /*
    |--------------------------------------------------------------------------
    | Fallback Rule-Based Scoring (jika AI tidak tersedia)
    |--------------------------------------------------------------------------
    | Bobot digunakan untuk menghitung skor risiko secara manual.
    */
    'fallback_scoring' => [
        'enabled'              => true,
        'new_device_weight'    => 25,
        'new_country_weight'   => 20,
        'vpn_weight'           => 15,
        'failed_attempts_multiplier' => 5,  // per percobaan gagal
        'odd_hour_weight'      => 10,       // login di luar jam kerja
        'ip_risk_multiplier'   => 0.4,      // bobot ip_risk_score
    ],

];
