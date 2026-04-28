# Upgrade Plan: Laravel 11 ke Laravel 13 — MixuAuth Identity Server

## Kondisi Saat Ini

| Komponen | Versi Sekarang | Target |
|---|---|---|
| Laravel Framework | ^11.0 | ^13.0 |
| PHP (composer.json) | ^8.2 | ^8.3 |
| PHP (Docker image) | 8.4-fpm-alpine | 8.4-fpm-alpine (tidak perlu ubah) |
| Laravel Passport | ^13.7 | ^13.x (sudah kompatibel) |
| Laravel Sanctum | ^4.0 | ^4.x atau ^5.x (perlu verifikasi) |
| Laravel Socialite | ^5.26 | ^5.x (sudah kompatibel) |
| pragmarx/google2fa-laravel | ^3.0 | ^3.x (sudah kompatibel) |
| phpunit/phpunit | ^11.0 | ^11.x atau ^12.x |
| nunomaduro/collision | ^8.0 | ^9.0 (perlu update) |

## Analisis Breaking Changes Laravel 13

### Tidak Ada Breaking Changes Arsitektur
Laravel 13 dirancang sebagai upgrade yang mulus. File `bootstrap/app.php` dengan gaya `Application::configure()` (Laravel 11+) **langsung kompatibel** — tidak perlu refactor.

### Perubahan Utama yang Relevan
- **PHP 8.3 minimum** — Docker sudah menggunakan PHP 8.4, aman.
- **Native PHP Attributes** — Opsional, tidak mewajibkan perubahan kode lama.
- **Cache::touch()** — Fitur baru, tidak memengaruhi kode yang ada.
- **Laravel AI SDK stable** — Fitur baru, opsional.

### Risiko Spesifik Proyek Ini

| Risiko | Tingkat | Keterangan |
|---|---|---|
| `jenssegers/agent: *` | **SEDANG** | Package tidak diakui kompatibel resmi. Versi `*` bisa menarik versi tidak kompatibel. |
| `laravel/sanctum: ^4.0` | **RENDAH** | Perlu verifikasi apakah ^4 mendukung Laravel 13 atau perlu naik ke ^5. |
| `phpunit/phpunit: ^11.0` | **RENDAH** | Laravel 13 umumnya menggunakan PHPUnit 12, perlu update. |
| `nunomaduro/collision: ^8.0` | **RENDAH** | Perlu update ke ^9.0 untuk Laravel 13. |
| `session.cookie_samesite = Strict` di php.ini | **SEDANG** | `Strict` dapat mengganggu alur OAuth/SSO redirect dari domain lain. |

## User Review Required

> [!WARNING]
> **`jenssegers/agent`**: Package ini diketahui tidak aktif dikembangkan. Proyek ini menggunakannya untuk User-Agent parsing di `DeviceFingerprintService`. Sebaiknya migrasi ke `hisorange/browser-detect` atau menggunakan `WhichBrowser\Parser` yang lebih aktif sebelum upgrade.

> [!IMPORTANT]
> **`session.cookie_samesite = Strict` di `docker/php/php.ini`**: Pengaturan ini dapat memblokir cookie sesi saat callback OAuth dari client SSO. Untuk sistem SSO, nilai yang lebih aman adalah `Lax`. Ini perlu didiskusikan sebelum upgrade.

> [!NOTE]
> **Laravel AI SDK**: Laravel 13 memiliki AI SDK stabil. Jika ingin mengganti implementasi FastAPI risk assessment dengan AI SDK bawaan Laravel di masa depan, ini adalah momen yang tepat untuk mempersiapkan arsitekturnya.

---

## Rencana Perubahan

### Phase 0: Persiapan & Backup (Sebelum Upgrade)

**Checklist Pra-Upgrade:**
- [ ] Pastikan semua perubahan sudah di-commit ke Git
- [ ] Buat Git tag: `git tag v1.0-pre-laravel13`
- [ ] Buat backup database production: `mysqldump`
- [ ] Dokumentasikan versi semua container yang sedang berjalan
- [ ] Pastikan test suite berjalan tanpa error di versi saat ini
- [ ] Verifikasi semua environment variable di `.env` sudah lengkap

---

### Phase 1: Update Dependencies

#### [MODIFY] [composer.json](file:///d:/WEBSITE/DOCKER/AI-AUTH-02/identity-server/composer.json)

Perubahan versi berikut perlu dilakukan:

```json
{
  "require": {
    "php": "^8.3",
    "laravel/framework": "^13.0",
    "laravel/passport": "^13.7",
    "laravel/sanctum": "^4.0",
    "laravel/socialite": "^5.26",
    "laravel/tinker": "^2.9",
    "pragmarx/google2fa-laravel": "^3.0",
    "bacon/bacon-qr-code": "^3.1",
    "guzzlehttp/guzzle": "^7.9",
    "jenssegers/agent": "*"
  },
  "require-dev": {
    "nunomaduro/collision": "^9.0",
    "phpunit/phpunit": "^12.0",
    "laravel/pail": "^1.2",
    "laravel/pint": "^1.13",
    "laravel/sail": "^1.26",
    "mockery/mockery": "^1.6",
    "fakerphp/faker": "*"
  }
}
```

---

### Phase 2: Perbaikan Docker & Infrastruktur

#### [MODIFY] [Dockerfile](file:///d:/WEBSITE/DOCKER/AI-AUTH-02/docker/laravel/Dockerfile)
- Update komentar `Laravel 11` menjadi `Laravel 13`.
- Tidak perlu ganti versi PHP (8.4 sudah memenuhi syarat PHP 8.3+).

#### [MODIFY] [php.ini](file:///d:/WEBSITE/DOCKER/AI-AUTH-02/docker/php/php.ini)
- Ubah `session.cookie_samesite = Strict` menjadi `Lax` untuk kompatibilitas SSO redirect cross-domain.

---

### Phase 3: Update setup.sh

#### [MODIFY] [setup.sh](file:///d:/WEBSITE/DOCKER/AI-AUTH-02/setup.sh)
- Tambahkan perintah `php artisan config:clear` dan `php artisan view:clear` setelah `composer install` agar cache tidak bercampur antara versi lama dan baru.
- Update banner versi dari `Laravel 11/12` ke `Laravel 13`.

---

### Phase 4: Verifikasi Kode Aplikasi

#### Tidak Perlu Diubah (Langsung Kompatibel)
- `bootstrap/app.php` — Gaya `Application::configure()` sudah standar Laravel 13.
- Semua middleware, service provider, model Eloquent.
- Semua route file (`web.php`, `api.php`).
- `AuthFlowService`, `SSOServiceProvider`, semua konfigurasi.

---

## Panduan Eksekusi Step-by-Step

### Langkah 1: Buat Backup
```bash
# Di dalam container atau server
git tag v1.0-pre-laravel13
docker compose exec app php artisan config:cache
docker compose exec db mysqldump -u root -p${MYSQL_ROOT_PASSWORD} Mixu_Auth > backup_pre_upgrade.sql
```

### Langkah 2: Update composer.json
Perbarui versi di `identity-server/composer.json` sesuai Phase 1.

### Langkah 3: Jalankan Composer Update di Container
```bash
docker compose exec app composer update --no-scripts
```

### Langkah 4: Jalankan Script Post-Update
```bash
docker compose exec app php artisan package:discover --ansi
docker compose exec app php artisan config:clear
docker compose exec app php artisan route:clear
docker compose exec app php artisan view:clear
docker compose exec app php artisan cache:clear
```

### Langkah 5: Publish Asset yang Diperbarui
```bash
docker compose exec app php artisan vendor:publish --tag=laravel-assets --ansi --force
docker compose exec app php artisan vendor:publish --tag=passport-config --ansi --force
```

### Langkah 6: Jalankan Migrasi (jika ada migrasi baru dari package)
```bash
docker compose exec app php artisan migrate --force
```

### Langkah 7: Rebuild Docker Image
```bash
docker compose build --no-cache
docker compose up -d
```

### Langkah 8: Optimasi Production
```bash
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
docker compose exec app php artisan view:cache
docker compose exec app php artisan event:cache
```

---

## Checklist Pasca-Upgrade

### Fungsional
- [ ] Halaman login dapat diakses
- [ ] Login berhasil tanpa pesan "Sesi tidak valid"
- [ ] Alur OTP berjalan normal
- [ ] SSO OAuth redirect berfungsi dari client
- [ ] Dashboard terbuka setelah login
- [ ] Logout berfungsi dan redirect ke halaman login
- [ ] AI Risk Assessment (FastAPI) merespons dengan benar

### Infrastruktur
- [ ] Semua container berjalan: `docker compose ps`
- [ ] Tidak ada error di log Laravel: `docker compose logs app`
- [ ] Tidak ada error di log Redis: `docker compose logs redis`
- [ ] Database dapat diakses: `docker compose exec app php artisan db:show`
- [ ] phpMyAdmin dapat diakses di port 8081

### Keamanan
- [ ] `APP_DEBUG=false` di production
- [ ] Semua cookie SSO masih berfungsi
- [ ] Rate limiting masih aktif
- [ ] Token Passport masih valid

---

## Strategi Rollback

### Rollback Cepat (< 5 Menit)
Jika upgrade gagal dan sistem tidak dapat diakses:
```bash
# 1. Kembalikan ke Git tag sebelum upgrade
git checkout v1.0-pre-laravel13 -- identity-server/

# 2. Rebuild image lama
docker compose build --no-cache

# 3. Restart semua service
docker compose down
docker compose up -d

# 4. Restore database jika ada migrasi yang gagal
docker compose exec -T db mysql -u root -p${MYSQL_ROOT_PASSWORD} Mixu_Auth < backup_pre_upgrade.sql
```

### Rollback Terencana (Jika Masalah Ditemukan Setelah Deploy)
```bash
# Kembalikan composer.json ke versi lama
git checkout v1.0-pre-laravel13 -- identity-server/composer.json identity-server/composer.lock

# Update dependensi ke versi lama
docker compose exec app composer install --no-interaction

# Clear cache
docker compose exec app php artisan config:clear
docker compose exec app php artisan cache:clear
```

---

## Rekomendasi Urutan Pelaksanaan

```
Phase 0 (Backup)          → 15 menit
Phase 1 (composer.json)   → 5 menit
Phase 2 (Docker & php.ini)→ 10 menit
Phase 3 (setup.sh)        → 5 menit
Eksekusi Langkah 1-4      → 10-20 menit (tergantung kecepatan internet)
Eksekusi Langkah 5-8      → 10 menit
Verifikasi Checklist      → 15 menit

Total Estimasi: 70-80 menit
```

---

## Open Questions

> [!IMPORTANT]
> 1. Apakah `jenssegers/agent` boleh diganti dengan library yang lebih aktif, atau harus dipertahankan untuk saat ini?
> 2. Apakah perubahan `session.cookie_samesite` dari `Strict` ke `Lax` dapat dilakukan? Ini memengaruhi postur keamanan session.
> 3. Apakah upgrade ingin dilakukan pada environment **development** dulu, atau langsung ke production?
