# MixuAuth: Sistem Autentikasi Berbasis AI

MixuAuth adalah sistem manajemen identitas dan autentikasi yang tangguh berbasis Laravel 11/12, terintegrasi dengan microservice Penilaian Risiko berbasis AI yang ditenagai oleh FastAPI. Sistem ini menyediakan deteksi ancaman proaktif, alur autentikasi adaptif, dan arsitektur yang mengutamakan keamanan untuk aplikasi web modern.

## Ikhtisar Teknis

Sistem ini memanfaatkan Machine Learning untuk mengevaluasi upaya login secara real-time, memberikan skor risiko berdasarkan pola perilaku. Bergantung pada risiko yang dihitung, sistem secara dinamis menyesuaikan tantangan autentikasi—mengizinkan akses langsung, memerlukan autentikasi multifaktor (OTP), atau memblokir aktivitas mencurigakan sepenuhnya.

### Fitur Utama

- Penilaian Risiko AI: Analisis perilaku real-time menggunakan algoritma Isolation Forest untuk mendeteksi anomali.
- Autentikasi Adaptif: Kontrol alur dinamis (Izinkan, OTP, Blokir) berdasarkan penilaian risiko yang granular.
- Keamanan Identitas Lanjutan: Implementasi hashing Argon2id, protokol reset kata sandi yang aman, dan fingerprinting perangkat.
- Pembatasan Laju Berbasis Konteks: Kontrol lalu lintas cerdas berdasarkan konteks pengguna, reputasi IP, dan upaya login.
- Infrastruktur Terotomatisasi: Deployment berbasis kontainer menggunakan Docker dengan skrip inisialisasi otomatis.

## Arsitektur Sistem

Proyek ini dibagi menjadi layanan-layanan khusus:

- Identity Server (Laravel): Menangani logika autentikasi inti, manajemen pengguna, dan kontrol sesi.
- Security Service (FastAPI): Microservice berperforma tinggi yang didedikasikan untuk inferensi AI dan pemodelan risiko.
- Infrastruktur: Diorkestrasi melalui Docker Compose, menggunakan MySQL 8.0 untuk persistensi dan Redis 7 untuk caching berkecepatan tinggi serta manajemen antrean.

## Memulai

### Prasyarat

- Docker Desktop atau Docker Engine 24.0+
- Docker Compose V2
- Git

### Instalasi Terotomatisasi

Proyek ini menyertakan skrip penyiapan komprehensif yang mengotomatiskan konfigurasi lingkungan, instalasi dependensi, dan pembuatan kunci keamanan.

1. Clone repositori:
   ```bash
   git clone https://github.com/mixudev/mixuauth.git
   cd mixuauth
   ```

2. Jalankan skrip setup:
   ```bash
   chmod +x setup.sh
   ./setup.sh
   ```

### Instalasi Manual

Untuk lingkungan yang memerlukan kontrol manual:

1. Siapkan file environment:
   ```bash
   cp identity-server/.env.example identity-server/.env
   cp security-service/.env.example security-service/.env
   ```

2. Bangun dan inisialisasi kontainer:
   ```bash
   docker compose build
   docker compose up -d db redis
   ```

3. Instal dependensi dan buat kunci keamanan:
   ```bash
   docker compose run --rm -u root app composer install --no-interaction --optimize-autoloader --ignore-platform-reqs
   docker compose run --rm app php artisan key:generate
   docker compose run --rm app php artisan ai:generate-key
   ```

4. Jalankan migrasi database:
   ```bash
   docker compose run --rm app php artisan migrate --force
   ```

## Konfigurasi

### Deployment Produksi

Sebelum melakukan deployment ke lingkungan produksi, pastikan konfigurasi berikut diterapkan di `identity-server/.env`:

- APP_ENV: Atur ke `production`
- APP_DEBUG: Atur ke `false`
- APP_URL: Atur ke domain yang telah diverifikasi (diperlukan untuk reset kata sandi yang aman)
- MAIL_DRIVER: Konfigurasikan penyedia SMTP yang andal untuk pengiriman OTP

### Keamanan API Internal

Komunikasi antara Identity Server dan Security Service dilindungi melalui kunci API internal. Kunci ini dapat dirotasi menggunakan:

```bash
docker compose run --rm app php artisan ai:generate-key
docker compose restart fastapi-risk
```

## Pemantauan dan Pemeliharaan

### Log Layanan

Pantau kesehatan aplikasi dan peristiwa keamanan melalui output standar:

- Logika Aplikasi: `docker compose logs -f app`
- Audit Keamanan: `docker compose exec app tail -f storage/logs/security.log`
- Inferensi AI: `docker compose logs -f fastapi-risk`

### Pemeliharaan Model AI

Model Security Service dapat dilatih ulang menggunakan data login lokal untuk meningkatkan akurasi deteksi. Instruksi mendetail tersedia di direktori `security-service/training/`.

## Lisensi

Proyek ini dilisensikan di bawah Lisensi MIT - lihat file LICENSE untuk detailnya.

---

Dikembangkan oleh Tim Arsitektur Keamanan MixuDev.
