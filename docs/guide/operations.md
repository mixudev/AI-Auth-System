# Operasional Harian

Halaman ini adalah runbook untuk aktivitas rutin tim.

## Daily Health Check

```bash
docker compose ps
docker compose logs --tail=100 app
docker compose logs --tail=100 fastapi-risk
```

Checklist:

1. Service inti `Up`.
2. Tidak ada error fatal berulang di log.
3. Endpoint `http://localhost:8000/health` merespons.

## Saat Deploy Perubahan Aplikasi

1. Pull kode terbaru.
2. Rebuild/restart service yang berubah.
3. Jalankan migration jika ada.
4. Verifikasi login flow.

Contoh:

```bash
docker compose up -d --build app worker scheduler
docker compose exec -T app php artisan migrate --force
```

## Saat Deploy Perubahan Env

Ikuti prosedur di [Konfigurasi Environment](/guide/environment) sesuai file env yang diubah.

## Backup dan Recovery Dasar

### Backup database (contoh sederhana)

```bash
docker compose exec -T db sh -lc 'mysqldump -uroot -p"$MYSQL_ROOT_PASSWORD" "$MYSQL_DATABASE"' > backup.sql
```

### Restore database

```bash
cat backup.sql | docker compose exec -T db sh -lc 'mysql -uroot -p"$MYSQL_ROOT_PASSWORD" "$MYSQL_DATABASE"'
```

## Perintah Operasional Cepat

```bash
# Masuk shell app
docker compose exec app sh

# Jalankan artisan
docker compose exec -T app php artisan about

# Cek queue worker
docker compose logs -f worker

# Cek scheduler
docker compose logs -f scheduler
```
