<?php

return [

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
        'channel'         => 'email',     // Channel pengiriman: email | sms
    ],

    /*
    |--------------------------------------------------------------------------
    | Konfigurasi Rate Limiting Login
    |--------------------------------------------------------------------------
    */
    'rate_limit' => [
        'max_attempts'     => 5,          // Maksimum percobaan login
        'decay_minutes'    => 15,         // Durasi lockout dalam menit
        'captcha_after'    => 3,          // Tampilkan CAPTCHA setelah N kali gagal
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
