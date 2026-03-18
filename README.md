# Secure Auth System — Panduan Lengkap

Panduan ini menjelaskan cara menjalankan sistem dari nol sampai siap digunakan,
termasuk cara mengatur SMTP untuk pengiriman OTP.

---

## Struktur Direktori

```
secure-system/
├── laravel-auth-ai/        ← Project Laravel (dari zip sebelumnya)
├── ai-security/            ← Project FastAPI (dari zip sebelumnya)
├── docker/
│   └── nginx/
│       └── default.conf    ← Konfigurasi Nginx
├── docker-compose.yml      ← Orchestrasi semua service
├── setup.sh                ← Script setup otomatis
└── README.md               ← File ini
```

---

## Langkah 1 — Siapkan File Konfigurasi

### Laravel `.env`

```bash
cd laravel-auth-ai
cp .env.example .env
```

Buka `laravel-auth-ai/.env` dan isi bagian berikut:

```env
# Wajib diisi — nilai DB harus cocok dengan docker-compose.yml
DB_PASSWORD=secret123
DB_ROOT_PASSWORD=rootsecret123

# API Key FastAPI — harus sama persis dengan ai-security/.env
AI_RISK_API_KEY=ganti-dengan-string-acak-minimal-32-karakter

# SMTP untuk OTP (lihat panduan di bawah)
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=isi_dari_mailtrap
MAIL_PASSWORD=isi_dari_mailtrap
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@yourcompany.com"
MAIL_FROM_NAME="Secure Auth"
```

### FastAPI `.env`

```bash
cd ../ai-security
cp .env.example .env
```

Buka `ai-security/.env` dan isi:

```env
# Harus sama persis dengan AI_RISK_API_KEY di Laravel
API_KEY=ganti-dengan-string-acak-minimal-32-karakter

APP_ENV=development
```

---

## Langkah 2 — Cara Mendapatkan Kredensial SMTP

### Opsi A: Mailtrap (Gratis, Untuk Development)

Email tidak benar-benar terkirim — semua tertangkap di dashboard.
Cocok untuk testing dan development.

1. Buka https://mailtrap.io dan daftar akun gratis
2. Masuk ke: Email Testing → Inboxes → My Inbox
3. Klik ikon settings (gear) di sebelah kanan inbox
4. Pilih tab **SMTP Settings**
5. Pilih **Laravel** di dropdown Integration
6. Salin nilai Host, Port, Username, Password ke `.env` Laravel

### Opsi B: Resend (Gratis 3.000 email/bulan, Untuk Production)

1. Daftar di https://resend.com
2. Verifikasi domain perusahaan Anda (tambahkan DNS TXT record)
3. Buat API Key di menu API Keys
4. Install package:

   ```bash
   cd laravel-auth-ai
   composer require resend/resend-laravel
   ```

5. Isi `.env`:

   ```env
   MAIL_MAILER=resend
   RESEND_API_KEY=re_xxxxxxxxxxxxxxxxxxxx
   MAIL_FROM_ADDRESS="noreply@domain-anda.com"
   MAIL_FROM_NAME="Secure Auth"
   ```

### Opsi C: Gmail SMTP (Hanya Development, Tidak Disarankan Production)

1. Aktifkan 2FA di akun Google Anda
2. Buka: https://myaccount.google.com/apppasswords
3. Buat App Password baru (nama: "Laravel")
4. Salin password 16 karakter yang dihasilkan
5. Isi `.env`:

   ```env
   MAIL_MAILER=smtp
   MAIL_HOST=smtp.gmail.com
   MAIL_PORT=587
   MAIL_USERNAME=emailanda@gmail.com
   MAIL_PASSWORD=abcd efgh ijkl mnop   # App Password dari langkah 4
   MAIL_ENCRYPTION=tls
   MAIL_FROM_ADDRESS="emailanda@gmail.com"
   MAIL_FROM_NAME="Secure Auth"
   ```

---

## Langkah 3 — Jalankan Sistem

### Cara Otomatis (Direkomendasikan)

```bash
cd secure-system
chmod +x setup.sh
./setup.sh
```

Script ini akan:
- Memeriksa Docker tersedia
- Menyalin .env.example jika belum ada
- Build semua Docker image
- Menjalankan migration database
- Menjalankan semua service
- Memverifikasi semua berjalan

### Cara Manual

```bash
# 1. Build images
docker compose build

# 2. Jalankan database dan redis dulu
docker compose up -d db redis

# 3. Tunggu database siap (sekitar 15 detik)
sleep 15

# 4. Generate APP_KEY Laravel
docker compose run --rm app php artisan key:generate

# 5. Jalankan migration
docker compose run --rm app php artisan migrate --force

# 6. Jalankan semua service
docker compose up -d

# 7. Lihat status
docker compose ps
```

---

## Langkah 4 — Verifikasi Sistem Berjalan

### Cek semua service

```bash
docker compose ps
```

Output yang diharapkan:
```
NAME                    STATUS          PORTS
secure-system-app-1     Up              9000/tcp
secure-system-nginx-1   Up              0.0.0.0:8080->80/tcp
secure-system-worker-1  Up
secure-system-fastapi-1 Up (healthy)
secure-system-db-1      Up (healthy)    0.0.0.0:3306->3306/tcp
secure-system-redis-1   Up (healthy)    0.0.0.0:6379->6379/tcp
```

### Cek FastAPI

```bash
curl http://localhost:8000/health
```

Respons yang diharapkan:
```json
{
  "status": "ok",
  "model_loaded": false,
  "version": "1.0.0"
}
```

`model_loaded: false` adalah normal — sistem berjalan dengan rule-based fallback.

### Test Login API

```bash
# Test dengan kredensial salah — harus dapat 401
curl -X POST http://localhost:8080/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"wrongpassword"}'
```

---

## Langkah 5 — Buat User Pertama

```bash
# Masuk ke tinker Laravel
docker compose exec app php artisan tinker

# Di dalam tinker, buat user pertama:
App\Models\User::create([
    'name'     => 'Admin',
    'email'    => 'admin@example.com',
    'password' => 'P@ssw0rd1234',  // Akan otomatis di-hash Argon2id
    'is_active' => true,
]);
```

### Test Login Lengkap

```bash
# Login — harusnya dapat ALLOW atau OTP tergantung sinyal risiko
curl -X POST http://localhost:8080/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@example.com",
    "password": "P@ssw0rd1234"
  }'
```

Jika respons adalah `requires_otp: true`, cek email di Mailtrap dashboard.
Lalu verifikasi OTP:

```bash
curl -X POST http://localhost:8080/api/auth/otp/verify \
  -H "Content-Type: application/json" \
  -d '{
    "session_token": "token_dari_respons_login",
    "otp_code": "123456"
  }'
```

---

## Memantau Log

```bash
# Log Laravel (aplikasi)
docker compose logs -f app

# Log queue worker (pengiriman OTP)
docker compose logs -f worker

# Log FastAPI (penilaian risiko)
docker compose logs -f fastapi-risk

# Log keamanan khusus (di dalam container)
docker compose exec app tail -f storage/logs/security.log
```

---

## Menghentikan & Membersihkan

```bash
# Hentikan semua service (data tersimpan)
docker compose down

# Hentikan dan hapus semua data (reset total)
docker compose down -v
```

---

## Troubleshooting Umum

### Email OTP tidak terkirim

```bash
# Cek log worker
docker compose logs worker

# Cek apakah queue berjalan
docker compose exec app php artisan queue:monitor
```

Kemungkinan penyebab:
- Kredensial SMTP salah di `.env`
- Worker tidak berjalan (`docker compose ps` untuk cek)
- Queue driver bukan redis (pastikan `QUEUE_CONNECTION=redis`)

### FastAPI tidak merespons

```bash
docker compose logs fastapi-risk
```

Kemungkinan penyebab:
- File `.env` FastAPI belum diisi
- `API_KEY` kosong

### Database connection error

```bash
docker compose logs db
```

Kemungkinan penyebab:
- Database belum selesai startup saat migration dijalankan
- Solusi: `docker compose restart app` setelah db healthy

### OTP selalu mengatakan "sesi tidak valid"

Pastikan `session_token` yang dikirim ke `/otp/verify` persis sama
dengan yang diterima dari respons `/login`. Token ini case-sensitive
dan harus dikirim dalam 5 menit.
