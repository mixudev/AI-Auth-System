# Final Implementation Plan — SSO Server (`laravel-auth-ai` sebagai Identity Provider)

## Keputusan yang Sudah Dikonfirmasi

| Topik | Keputusan |
|---|---|
| OAuth2 Engine | ✅ Install `laravel/passport` |
| `access_areas` | ✅ Tabel terpisah, khusus SSO (bukan permissions) |
| Admin UI | ✅ Terintegrasi di dashboard admin existing (tambah section "SSO Server" di sidebar) |
| Halaman Login/Consent | ✅ Reuse sistem login existing, tambah consent page setelah login |
| Standarisasi UI | ✅ Guest Portal (`guest/portal.blade.php`) menggunakan `layouts.app-dashboard` |
| Pengayaan Data | ✅ Menambahkan enterprise data di `AccessAreaSeeder` dan `SsoClientSeeder` |

---

## Endpoint yang WAJIB Disediakan Server

| Endpoint | Method | Handler | Keterangan |
|---|---|---|---|
| `/oauth/authorize` | GET | `OAuthController@show` | Tampilkan consent page |
| `/oauth/authorize` | POST | `OAuthController@approve` | User setujui → Passport generate code |
| `/oauth/token` | POST | *(Passport native)* | Tukar code → access_token |
| `/oauth/token/refresh` | POST | *(Passport native)* | Refresh token |
| `/oauth/revoke` | POST | *(Passport native)* | Revoke token |
| `/api/user` | GET | `UserInfoController@show` | Profil user + roles + access_areas |
| `/api/logout` | POST | `SsoLogoutController@handle` | Logout + trigger global webhook |

---

## Struktur Modul Baru

```
app/Modules/SSO/
├── SSOServiceProvider.php
├── Controllers/
│   ├── OAuthController.php              ← Consent page handler
│   ├── UserInfoController.php           ← GET /api/user
│   ├── SsoLogoutController.php          ← POST /api/logout
│   └── Admin/
│       ├── SsoClientController.php      ← CRUD client apps (admin UI)
│       └── AccessAreaController.php     ← CRUD access areas (admin UI)
├── Models/
│   ├── AccessArea.php
│   └── SsoClient.php
├── Services/
│   └── GlobalLogoutService.php          ← Dispatcher webhook ke semua client
├── Jobs/
│   └── SendGlobalLogoutWebhookJob.php   ← Async queue job
└── routes/
    ├── web.php                          ← /oauth/authorize + admin UI routes
    └── api.php                          ← /api/user, /api/logout
```

---

## Proposed Changes — Detail Per File

### Phase 1: Dependencies & Database

---

#### [MODIFY] `composer.json`
Tambahkan `laravel/passport` di `require`.

**Commands:**
```bash
composer require laravel/passport --ignore-platform-reqs
php artisan passport:install --uuids
php artisan passport:keys
```

---

#### [NEW] Migration: `access_areas` table
```
database/migrations/xxxx_create_access_areas_table.php
database/migrations/xxxx_create_user_access_area_table.php
```

Kolom `access_areas`: `id`, `name`, `slug`, `description`, `is_active`, `timestamps`
Kolom `user_access_area`: `user_id`, `access_area_id` (pivot)

#### [NEW] Migration: `sso_clients` table
```
database/migrations/xxxx_create_sso_clients_table.php
```

Kolom: `id`, `oauth_client_id` (FK ke `oauth_clients.id`), `name`, `webhook_url`, `webhook_secret`, `is_active`, `timestamps`

#### [NEW] Seeders (Pengayaan Data Enterprise)
1. `database/seeders/AccessAreaSeeder.php`
2. `database/seeders/SsoClientSeeder.php`
3. Memodifikasi `database/seeders/DatabaseSeeder.php` agar memanggil kedua seeder baru tersebut dan menggunakan mock data user kelas enterprise (Dosen, HR, Mahasiswa, Admin).

---

### Phase 2: Models

---

#### [MODIFY] `User.php`
- Tambah `use Laravel\Passport\HasApiTokens;`
- Tambah trait `HasApiTokens` di class
- Tambah relasi `accessAreas()` BelongsToMany

#### [NEW] `app/Modules/SSO/Models/AccessArea.php`
Model dengan relasi `users()` BelongsToMany.

#### [NEW] `app/Modules/SSO/Models/SsoClient.php`
Model dengan relasi ke `oauth_clients` via `oauth_client_id`.

---

### Phase 3: Service Provider & Config

---

#### [NEW] `app/Modules/SSO/SSOServiceProvider.php`
```php
// Register Passport routes (custom, bukan default Passport::routes())
// Load routes dari module
// Register views dari module
```

#### [MODIFY] `app/Providers/AppServiceProvider.php`
Register `SSOServiceProvider`. Konfigurasi durasi kedaluwarsa Passport `Passport::tokensExpireIn(now()->addDays(15));`.

#### [MODIFY] `config/auth.php`
```php
'guards' => [
    'api' => [
        'driver'   => 'passport',
        'provider' => 'users',
    ],
],
```

---

### Phase 4: Core SSO Controllers

*(Sama dengan rencana awal)*

---

### Phase 5: Global Logout Webhook

*(Sama dengan rencana awal)*

---

### Phase 6: Admin UI Controllers

*(Sama dengan rencana awal)*

---

### Phase 7: Routes

*(Sama dengan rencana awal)*

---

### Phase 8: Views Admin UI & Portal Guest

---

#### [MODIFY] `resources/views/guest/portal.blade.php`
Refactor tampilan Guest Portal menggunakan standar UI dashboard:
```blade
@extends('layouts.app-dashboard')
@section('title', 'Guest Portal')
@section('page-title', 'Guest Portal')
@section('content')
  <!-- Gunakan Tailwind CSS dan komponen bawaan -->
@endsection
```

#### [NEW] `resources/views/sso/authorize.blade.php`
Halaman consent SSO (reuse layout `app-dashboard.blade.php`).

*(Views lainnya sama dengan rencana awal)*

---

### Phase 9: Sidebar Update

*(Sama dengan rencana awal)*

---

## Urutan Eksekusi

- [x] **Step 1** — Install `laravel/passport`
- [x] **Step 2** — Buat Seeder: `AccessAreaSeeder`, `SsoClientSeeder`, update `DatabaseSeeder`
- [x] **Step 3** — Refactor tampilan Guest Portal (`portal.blade.php`) ke Tailwind
- [ ] **Step 4** — Buat migration baru: `access_areas`, `user_access_area`, `sso_clients`
- [ ] **Step 5** — Modifikasi `User` model: `HasApiTokens` + relasi `accessAreas()`
- [ ] **Step 6** — Buat models: `AccessArea`, `SsoClient`
- [ ] **Step 7** — Konfigurasi `config/auth.php` guard `api` → Passport
- [ ] **Step 8** — Buat `SSOServiceProvider` + config `AppServiceProvider`
- [ ] **Step 9** — Buat Controller & Routes
- [ ] **Step 10** — Testing end-to-end
