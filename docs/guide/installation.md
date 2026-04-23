# Panduan Instalasi

Panduan ini untuk menyiapkan sistem dari nol sampai aplikasi bisa diakses.

## Prasyarat

| Komponen | Versi Minimum |
|---|---|
| Docker Engine | 20.10+ |
| Docker Compose Plugin | 2.0+ |
| Git | 2.x |

Cek cepat:

```bash
docker --version
docker compose version
git --version
```

## Langkah Instalasi

### 1) Clone repository

```bash
git clone <repo-url>
cd AI-AUTH-02
```

### 2) Siapkan file environment

```bash
cp .env.example .env
cp laravel-auth-ai/.env.example laravel-auth-ai/.env
cp ai-security/.env.example ai-security/.env
```

### 3) Isi konfigurasi wajib

Minimal isi:

- `laravel-auth-ai/.env`: SMTP, `AI_RISK_API_KEY`, captcha (jika mode captcha dipakai)
- `ai-security/.env`: API key yang sama dengan Laravel

### 4) Jalankan stack

```bash
docker compose up -d --build
```

### 5) Verifikasi service

```bash
docker compose ps
```

Semua service utama harus status `Up` / `healthy` (jika healthcheck aktif).

## Endpoint Lokal Default

| Service | URL |
|---|---|
| Web Laravel (via Nginx) | http://localhost:8080 |
| API Laravel | http://localhost:8080/api |
| FastAPI Health | http://localhost:8000/health |
| phpMyAdmin | http://localhost:8081 |
| Docs VitePress | http://localhost:8090 |

## Verifikasi Pasca Instalasi

1. Buka halaman login web.
2. Uji endpoint health FastAPI.
3. Jalankan login test dengan kredensial valid.
4. Pastikan log app tidak menampilkan error fatal.

## Jika Setup Gagal

Gunakan halaman [Troubleshooting](/guide/troubleshooting) untuk langkah recovery cepat.
