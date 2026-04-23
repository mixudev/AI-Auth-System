# Konfigurasi Environment

Dokumen ini menjelaskan lokasi file env, variabel penting, dan prosedur aman setelah perubahan.

## Lokasi File Env

| File | Dipakai Oleh |
|---|---|
| `.env` (root) | Docker Compose level |
| `laravel-auth-ai/.env` | Laravel app/worker/scheduler |
| `ai-security/.env` | FastAPI risk service |

## Variabel Penting Laravel

| Variabel | Fungsi |
|---|---|
| `APP_ENV`, `APP_DEBUG`, `APP_URL` | Runtime aplikasi |
| `DB_*` | Koneksi database |
| `REDIS_*` | Cache/session/queue |
| `MAIL_*` | Pengiriman email OTP/reset |
| `AI_RISK_SERVICE_URL`, `AI_RISK_API_KEY` | Integrasi AI risk |
| `RATE_LIMIT_CHALLENGE` | Mode challenge: `captcha` / `throttle` |
| `CAPTCHA_SITE_KEY`, `CAPTCHA_SECRET` | Konfigurasi captcha |
| `CAPTCHA_DEBUG_LOG` | Log status runtime captcha |

## Variabel Penting FastAPI

| Variabel | Fungsi |
|---|---|
| `AI_API_KEY` | Validasi request dari Laravel |
| `RISK_THRESHOLD_*` | Batas keputusan skor |
| `HOST`, `PORT`, `WORKERS` | Runtime Uvicorn |

## Prosedur Setelah Ubah Env

### Jika ubah `laravel-auth-ai/.env`

```powershell
docker compose up -d --force-recreate app worker scheduler
docker compose exec -T app php artisan config:clear
docker compose exec -T app php artisan cache:clear
```

### Jika ubah `ai-security/.env`

```powershell
docker compose up -d --force-recreate fastapi-risk
```

### Jika ubah root `.env`

```powershell
docker compose up -d --force-recreate db redis app worker scheduler fastapi-risk
```

## Validasi Nilai Runtime

### Cek env di container

```powershell
docker compose exec -T app sh -lc "printenv | grep -E '^(RATE_LIMIT_CHALLENGE|CAPTCHA_SITE_KEY|CAPTCHA_SECRET|CAPTCHA_DEBUG_LOG)=' || true"
```

### Cek config Laravel yang dipakai

```powershell
docker compose exec -T app php artisan tinker --execute="dump(config('security.rate_limit.challenge')); dump((string) config('services.captcha.site_key')); dump((string) config('services.captcha.secret'));"
```

Jika hasil runtime tidak sama dengan `.env`, service belum sinkron dan perlu recreate.
