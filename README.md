# Secure Auth System — AI-Powered Authentication

Sistem autentikasi Laravel 11/12 yang diperkuat dengan **AI Risk Assessment** (FastAPI) untuk deteksi ancaman dini, sistem OTP, dan manajemen keamanan tingkat lanjut.

---

## 🚀 Fitur Utama

- **AI-Based Early Threat Detection**: Evaluasi setiap percobaan login menggunakan Machine Learning untuk mendeteksi perilaku mencurigakan.
- **Secure Password Reset**: Token Argon2id, deteksi link kadaluarsa, dan kemampuan admin untuk menginisiasi reset langsung dari dashboard.
- **Context-Aware Rate Limiting**: Pembatasan percobaan login/reset yang cerdas berdasarkan kombinasi konteks, email, dan IP.
- **Device Fingerprinting**: Melacak dan memverifikasi perangkat yang dipercaya.
- **Automated setup**: Script `setup.sh` yang otomatis menyiapkan seluruh lingkungan (Docker, .env, Keys, Migrations).

---

## 🛠️ Persyaratan Sistem

- **Docker Desktop** (atau Docker Engine + Compose di Linux)
- **Koneksi Internet** (untuk build image pertama kali)
- **PHP 8.4+** (didukung otomatis di dalam container)

---

## ⚙️ Cara Instalasi (Local Development)

Hanya satu langkah untuk menjalankan semuanya:

```bash
chmod +x setup.sh
./setup.sh
```

**Apa yang dilakukan script ini?**
1. Membuat file `.env` dari `.env.example` jika belum ada.
2. Membangun (build) Docker images.
3. Menjalankan database dan Redis.
4. Instalasi dependensi Composer secara aman.
5. Generate `APP_KEY` dan sinkronisasi `AI_RISK_API_KEY` untuk keamanan API.
6. Menjalankan migrasi database.

---

## 🌐 Panduan Deployment ke VPS (Production)

Ikuti langkah-langkah ini untuk deploy sistem ini di VPS (Ubuntu 22.04/24.04 disarankan):

### 1. Persiapan Server
Instal Docker dan Docker Compose lokaly di server:
```bash
# Update sistem
sudo apt update && sudo apt upgrade -y

# Instal Docker
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh
```

### 2. Clone & Konfigurasi
```bash
git clone https://github.com/your-repo/ai-auth-system.git
cd ai-auth-system
```

### 3. Konfigurasi Environment Production
Pastikan Anda mengubah nilai-nilai berikut di `laravel-auth-ai/.env` untuk keamanan:
- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL=https://nama-domain-anda.com` (Sangat Penting untuk link Reset Password)
- `DB_PASSWORD` & `DB_ROOT_PASSWORD` (Gunakan password yang kuat)
- `MAIL_HOST`, `MAIL_USERNAME`, `MAIL_PASSWORD` (Gunakan provider asli seperti Resend/Amazon SES)

### 4. Jalankan Setup Otomatis
```bash
chmod +x setup.sh
./setup.sh
```

### 5. Konfigurasi SSL (Nginx & Certbot)
Sangat disarankan menggunakan Nginx di server host sebagai Reverse Proxy ke port 8080:

```bash
# Instal Nginx & Certbot
sudo apt install nginx certbot python3-certbot-nginx -y

# Konfigurasi Virtual Host Nginx ke http://localhost:8080
# Lalu aktifkan SSL
sudo certbot --nginx -d nama-domain-anda.com
```

---

## 🛠️ Instalasi Manual (Tanpa Script Setup)

Jika Anda ingin melakukan instalasi secara manual langkah demi langkah, ikuti urutan berikut agar sistem berjalan tanpa error:

### 1. Persiapan File Environment
```bash
cp laravel-auth-ai/.env.example laravel-auth-ai/.env
cp ai-security/.env.example ai-security/.env
```

### 2. Inisialisasi Direktori Storage (Wajib)
Laravel membutuhkan struktur folder ini agar tidak muncul error `cache path`:
```bash
mkdir -p laravel-auth-ai/storage/framework/{sessions,views,cache}
mkdir -p laravel-auth-ai/storage/logs
chmod -R 777 laravel-auth-ai/storage
```

### 3. Build & Jalankan Database
```bash
docker compose build
docker compose up -d db redis
# Tunggu sekitar 15-20 detik agar database siap
```

### 4. Instal Dependensi PHP
Gunakan user `root` dan abaikan pengecekan platform untuk kompatibilitas ekstensi:
```bash
docker compose run --rm -u root app composer install --no-interaction --optimize-autoloader --ignore-platform-reqs
docker compose run --rm -u root app chown -R www-data:www-data vendor storage
```

### 5. Generate Keys
```bash
# Generate APP_KEY
docker compose run --rm app php artisan key:generate

# Generate & Sinkronisasi AI API Key
docker compose run --rm app php artisan ai:generate-key
```

### 6. Migrasi Database & Jalankan
```bash
docker compose run --rm app php artisan migrate --force
docker compose up -d
```

## 🔐 Manajemen API Key

Sistem ini menggunakan kunci rahasia untuk komunikasi antara Laravel dan AI Service. Anda bisa me-rotate kunci ini kapan saja:

```bash
docker compose run --rm app php artisan ai:generate-key
docker compose restart fastapi-risk
```
*Perintah ini akan memperbarui kedua file `.env` secara otomatis.*

---

## 📊 Monitoring & Log

- **Log Aplikasi**: `docker compose logs -f app`
- **Log Keamanan**: `docker compose exec app tail -f storage/logs/security.log`
- **Log AI Service**: `docker compose logs -f fastapi-risk`

---

## 🤝 Kontribusi

Sistem ini masih dalam pengembangan. Jika menemukan celah keamanan, silakan baca `security_audit.md` untuk perbaikan yang disarankan.

---
*Developed with Security-First Mindset*
