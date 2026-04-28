<div align="center">

<p align="center">
  <img src="https://img.shields.io/badge/MixuAuth-v2.0.0-00a396?style=for-the-badge&labelColor=0d1117" alt="Version">
  <img src="https://img.shields.io/badge/Identity_Server-LTS-00a396?style=for-the-badge&labelColor=0d1117" alt="Status">
</p>

<h1 align="center">◈ MIXUAUTH IDENTITY SERVER ◈</h1>

**Sistem Manajemen Identitas Terpusat Berbasis Kecerdasan Buatan untuk Ekosistem Aplikasi Modern**

[![Laravel](https://img.shields.io/badge/Laravel-13.x-FF2D20?style=flat-square&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.4-777BB4?style=flat-square&logo=php&logoColor=white)](https://php.net)
[![License](https://img.shields.io/badge/License-MIT-22C55E?style=flat-square)](LICENSE)

</div>

## 1. Pendahuluan

MixuAuth adalah platform Identity Provider (IdP) yang dibangun di atas kerangka kerja Laravel 13. Sistem ini dirancang untuk menangani seluruh siklus hidup pengguna, mulai dari pendaftaran, autentikasi multifaktor, hingga otorisasi lintas aplikasi (Single Sign-On). Perbedaan utama MixuAuth terletak pada integrasi mesin AI yang mengevaluasi anomali login secara dinamis untuk mencegah akses ilegal sebelum terjadi.



## 2. Arsitektur Sistem dan Komponen Teknis

Sistem ini beroperasi dengan membagi beban kerja ke dalam beberapa lapisan layanan khusus:

### 2.1 Core Identity Engine (Laravel 13)
*   **Framework**: Laravel 13.6.0 (Latest Release).
*   **Engine Autentikasi**: Laravel Passport (OAuth2/OIDC) & Sanctum (Stateful API).
*   **Hashing Kredensial**: Argon2id (Standard RFC 9106).
*   **Runtime**: PHP 8.4-fpm-alpine.

### 2.2 Security Intelligence (FastAPI)
*   **Framework**: FastAPI (High-performance Python framework).
*   **Model Machine Learning**: Isolation Forest (Anomaly Detection).
*   **Komunikasi**: RESTful API dengan pengamanan internal API Key.
*   **Runtime**: Python 3.11-slim.

### 2.3 Infrastruktur Pendukung
*   **Relational Database**: MySQL 8.0 dengan pengoptimalan indexing pada tabel audit.
*   **Speed Layer & Broker**: Redis 7.2 untuk session management, rate limiting, dan background jobs.
*   **Reverse Proxy**: Nginx untuk SSL termination dan load balancing.



## 3. Fitur Keamanan Tingkat Lanjut

Sistem mengimplementasikan protokol keamanan berlapis:

*   **Adaptive MFA**: Sistem secara otomatis mewajibkan One-Time Password (OTP) melalui WhatsApp atau Email hanya jika skor risiko AI melampaui ambang batas tertentu.
*   **Device Binding**: Sesi login diikat secara unik ke kombinasi User-Agent dan ID Perangkat yang terenkripsi.
*   **Protection against Brute Force**: Implementasi rate limiting bertingkat berdasarkan IP address dan user identifier.
*   **CSRF & XSS Protection**: Konfigurasi Content Security Policy (CSP) yang ketat dan proteksi token anti-forgery pada setiap endpoint stateful.



## 4. Dokumentasi Endpoint Utama

Akses API diatur melalui prefix `/api/v2/`. Berikut adalah beberapa endpoint inti:

| Method | Endpoint | Fungsi | Proteksi |
| : | : | : | : |
| POST | `/api/auth/login` | Autentikasi utama & pengecekan risiko | Public / Rate-limited |
| POST | `/api/auth/verify-otp` | Verifikasi tantangan MFA | Session-bound |
| GET | `/api/user/profile` | Mengambil profil pengguna aktif | Bearer Token / Session |
| POST | `/api/oauth/token` | Penerbitan token akses OAuth2 | Client Secret |
| POST | `/api/auth/logout` | Terminasi sesi global & webhook | Auth Required |



## 5. Parameter Konfigurasi Lingkungan (.env)

Konfigurasi krusial untuk operasional sistem:

### Konfigurasi Dasar
*   `APP_ENV`: (production/local) Menentukan mode operasional.
*   `APP_KEY`: Kunci enkripsi utama (32 karakter).
*   `APP_URL`: Alamat dasar server untuk pembentukan link reset password.

### Konfigurasi Keamanan & AI
*   `SECURITY_AI_KEY`: Token internal untuk akses ke Security Service.
*   `SECURITY_RISK_THRESHOLD`: Ambang batas skor risiko (0.0 - 1.0) untuk memicu MFA.
*   `SESSION_BIND_DEVICE`: (true/false) Mengaktifkan penguncian sesi ke perangkat.

### Infrastruktur
*   `DB_CONNECTION`: Disarankan `mysql`.
*   `CACHE_STORE`: Disarankan `redis` untuk performa optimal.
*   `QUEUE_CONNECTION`: Disarankan `redis` untuk memproses pengiriman OTP di latar belakang.



## 6. Prosedur Instalasi dan Deployment

### Langkah 1: Persiapan Lingkungan
Pastikan Docker dan Docker Compose telah terinstal pada host system.

### Langkah 2: Inisialisasi Otomatis
```bash
# Menjalankan skrip setup menyeluruh
./setup.sh
```

### Langkah 3: Verifikasi Layanan
Pastikan seluruh kontainer dalam status 'Healthy':
```bash
docker compose ps
```

### Langkah 4: Pemeliharaan Rutin
Perintah berikut harus dijalankan secara berkala untuk menjaga performa:
```bash
# Optimalisasi cache konfigurasi
docker compose exec app php artisan config:cache

# Pembersihan log lama (disarankan via Cron)
docker compose exec app php artisan logs:clear
```



## 7. Troubleshooting

*   **Masalah Koneksi AI**: Pastikan `SECURITY_AI_URL` di `.env` mengarah ke nama container `fastapi-risk` di dalam jaringan Docker.
*   **Sesi Invalid**: Biasanya disebabkan oleh ketidaksesuaian `APP_KEY` setelah rotasi. Jalankan `cache:clear` setelah perubahan kunci.
*   **Gagal Kirim OTP**: Periksa status antrean pada Redis menggunakan `php artisan queue:monitor`.



## 8. Lisensi dan Hak Cipta

Dokumentasi pengembangan dapat ditemukan di [CONTRIBUTING.md](CONTRIBUTING.md). Kebijakan privasi dan pelaporan bug keamanan ada di [SECURITY.md](SECURITY.md).

Proyek ini dilisensikan di bawah **Lisensi MIT**.


<div align="center">
Copyright (c) 2026 MixuDev Security Architecture Team.
Pusat Inovasi Keamanan dan Identitas Digital.
</div>
