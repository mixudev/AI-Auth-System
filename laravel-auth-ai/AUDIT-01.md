**Audit keamanan & arsitektur (Laravel 11) – fokus AuthN/AuthZ**

Cakupan yang saya review: alur login web/API, MFA/OTP, session & cookie binding, rate limiting, trusted device, RBAC, route middleware, logging/audit, reset password, email verification, konfigurasi environment & Docker, plus struktur modul.

## Ringkasan Eksekutif
Ada beberapa temuan **kritikal/high** yang perlu diprioritaskan:
1. Secret hardcoded di codebase.
2. CAPTCHA/risk control punya mode **fail-open**.
3. Rute security admin belum dilindungi middleware session-hardening (`ensure.session.version`, `verify.fingerprint`).
4. `isAdmin()` masih punya bypass berbasis email config.
5. Implementasi TOTP saat setup berpotensi salah simpan secret.
6. Fallback risk scoring punya bug normalisasi skor IP.
7. Exposure infrastruktur (DB/Redis publish ke host).

---

## Temuan Detail (urut severity)

### 1) **Critical** – Hardcoded credential di command route
Deskripsi: ada API key Mailtrap hardcoded dan command debug email aktif.
File terdampak: [routes/console.php:28](/D:/WEBSITE/DOCKER/AI-AUTH-02/laravel-auth-ai/routes/console.php:28), [routes/console.php:38](/D:/WEBSITE/DOCKER/AI-AUTH-02/laravel-auth-ai/routes/console.php:38)  
Skenario serangan: key bocor lewat repo/log/screenshot, attacker kirim email abuse/phishing via akun Anda.  
Fix (Laravel 11):
```php
// routes/console.php
if (app()->environment(['local', 'testing'])) {
    Artisan::command('send-mail', function () {
        $apiKey = config('services.mailtrap.api_key');
        // ...
    });
}
```
Dan pindahkan key ke env, rotate key sekarang.  
Refactor: pindahkan command debug ke `app/Console/Commands/Dev/` dan register hanya untuk local/test.

---

### 2) **High** – CAPTCHA verification fail-open
Deskripsi: jika secret CAPTCHA kosong atau provider error jaringan, validasi dianggap lolos (`return true`).  
File: [PreAuthRateLimitMiddleware.php:129](/D:/WEBSITE/DOCKER/AI-AUTH-02/laravel-auth-ai/app/Modules/Authentication/Middleware/PreAuthRateLimitMiddleware.php:129), [PreAuthRateLimitMiddleware.php:149](/D:/WEBSITE/DOCKER/AI-AUTH-02/laravel-auth-ai/app/Modules/Authentication/Middleware/PreAuthRateLimitMiddleware.php:149)  
Skenario: botnet sengaja memicu error provider, challenge ter-bypass, brute-force jadi jauh lebih mudah.  
Fix:
```php
if (empty($secret)) {
    Log::channel('security')->error('CAPTCHA secret missing');
    return false; // fail-closed
}

try {
   // verify...
} catch (\Throwable $e) {
   return false; // fail-closed in production
}
```
Refactor: buat `CaptchaVerificationService` terpisah + circuit breaker + health metric.

---

### 3) **High** – Security admin routes tidak memakai session hardening middleware
Deskripsi: route security hanya pakai `web`,`auth`,`role`, tanpa `ensure.session.version` dan `verify.fingerprint`.  
File: [app/Modules/Security/routes/web.php:16](/D:/WEBSITE/DOCKER/AI-AUTH-02/laravel-auth-ai/app/Modules/Security/routes/web.php:16)  
Skenario: jika session cookie dicuri/terpasang, akses halaman security bisa lolos tanpa verifikasi fingerprint/version revoke.  
Fix:
```php
Route::middleware([
  'web','auth','ensure.session.version','verify.fingerprint',
  'role:super-admin,admin,security-officer'
])->group(...);
```
Refactor: buat middleware group standar `auth.hardened` reusable untuk semua route sensitif.

---

### 4) **High** – Privilege fallback via `ADMIN_EMAILS`
Deskripsi: `isAdmin()` mengizinkan admin berdasarkan email list walau tanpa role RBAC.  
File: [app/Models/User.php:263](/D:/WEBSITE/DOCKER/AI-AUTH-02/laravel-auth-ai/app/Models/User.php:263), [app/Models/User.php:271](/D:/WEBSITE/DOCKER/AI-AUTH-02/laravel-auth-ai/app/Models/User.php:271)  
Skenario: salah konfigurasi/seed email dapat mengangkat privilege tanpa alur RBAC formal.  
Fix:
```php
public function isAdmin(): bool
{
    return $this->hasRole(['super-admin','admin']);
}
```
Jika butuh emergency access, gate-kan dengan feature flag + audit ketat.  
Refactor: hilangkan fallback email, jadikan role table satu-satunya source of truth.

---

### 5) **High** – Potensi salah penyimpanan secret TOTP
Deskripsi: saat setup MFA, `totp_secret` di-encrypt manual, padahal model sudah cast `encrypted`; ini berisiko double-encryption dan verifikasi TOTP gagal.  
File: [ProfileController.php:226](/D:/WEBSITE/DOCKER/AI-AUTH-02/laravel-auth-ai/app/Modules/Identity/Controllers/ProfileController.php:226), [User.php:108](/D:/WEBSITE/DOCKER/AI-AUTH-02/laravel-auth-ai/app/Models/User.php:108)  
Skenario: user terkunci di flow MFA atau fallback tidak konsisten.  
Fix:
```php
$user->update([
  'mfa_enabled' => true,
  'mfa_type' => 'totp',
  'totp_secret' => $secret, // biarkan cast encrypted yang kerja
]);
```
Refactor: ekstrak seluruh setup/confirm MFA ke `MfaEnrollmentService` + integration tests.

---

### 6) **Medium** – Bug logika fallback risk scoring (IP risk jadi nol)
Deskripsi: payload `ip_risk_score` dinormalisasi 0..1, tapi di fallback di-cast ke int => hampir selalu 0.  
File: [LoginRiskService.php:48](/D:/WEBSITE/DOCKER/AI-AUTH-02/laravel-auth-ai/app/Modules/Authentication/Services/LoginRiskService.php:48), [RiskFallbackService.php:71](/D:/WEBSITE/DOCKER/AI-AUTH-02/laravel-auth-ai/app/Modules/Security/Services/RiskFallbackService.php:71)  
Skenario: saat AI down, decision jadi terlalu permisif.  
Fix:
```php
$ipRisk = (float) ($riskPayload['ip_risk_score'] ?? 0.0); // 0..1
$score += (int) round(($ipRisk * 100) * $weights['ip_risk_multiplier']);
```
Refactor: samakan kontrak DTO risk payload (typed object), jangan array bebas.

---

### 7) **Medium** – GeoIP pakai HTTP plaintext + fallback negara statis
Deskripsi: lookup ke `http://ip-api.com/...` tanpa TLS, fallback `ID` statis.  
File: [GeoIpService.php:27](/D:/WEBSITE/DOCKER/AI-AUTH-02/laravel-auth-ai/app/Modules/Security/Services/GeoIpService.php:27), [GeoIpService.php:46](/D:/WEBSITE/DOCKER/AI-AUTH-02/laravel-auth-ai/app/Modules/Security/Services/GeoIpService.php:46)  
Skenario: MITM memanipulasi country sehingga risk decision bias.  
Fix: pakai provider HTTPS + timeout + signed response bila ada.
Refactor: buat `GeoIpProviderInterface` dengan fallback chain terukur.

---

### 8) **Medium** – API auth pakai session guard pada group API
Deskripsi: `/api/auth/logout` pakai `auth` session middleware pada group API.  
File: [app/Modules/Authentication/routes/api.php:45](/D:/WEBSITE/DOCKER/AI-AUTH-02/laravel-auth-ai/app/Modules/Authentication/routes/api.php:45)  
Skenario: boundary web/api jadi kabur; rawan salah konfigurasi CSRF/cookie policy lintas client.  
Fix: pakai `auth:sanctum` (token-based) untuk API, atau pindahkan endpoint session-auth ke web route + CSRF.  
Refactor: pisah tegas `WebSessionAuth` vs `ApiTokenAuth`.

---

### 9) **Medium** – Security control overbroad (role-only, tanpa permission granular)
Deskripsi: security routes mengandalkan role global; action sensitif seperti whitelist IP tidak diproteksi permission spesifik.  
File: [SecurityController.php:89](/D:/WEBSITE/DOCKER/AI-AUTH-02/laravel-auth-ai/app/Modules/Security/Controllers/SecurityController.php:89), [Security/routes/web.php:16](/D:/WEBSITE/DOCKER/AI-AUTH-02/laravel-auth-ai/app/Modules/Security/routes/web.php:16)  
Skenario: role yang seharusnya observability-only bisa mengubah whitelist/blacklist.  
Fix: tambahkan middleware permission per endpoint (`ip-list.whitelist`, `ip-list.blacklist`, dll).  
Refactor: gunakan policy + command handlers per action sensitif.

---

### 10) **Medium** – Exposure service infrastruktur ke host
Deskripsi: MySQL dan Redis di-publish ke host (`3307`, `6379`).  
File: [docker-compose.yml:170](/D:/WEBSITE/DOCKER/AI-AUTH-02/docker-compose.yml:170), [docker-compose.yml:190](/D:/WEBSITE/DOCKER/AI-AUTH-02/docker-compose.yml:190)  
Skenario: port scanning lokal/VPN/host compromise -> brute-force service langsung.  
Fix: jangan expose port internal di production; atau bind `127.0.0.1:3307:3306`.  
Refactor: profile compose `dev` vs `prod` terpisah.

---

### 11) **Low** – Ruang OTP mengecil (tidak mengizinkan leading zero)
Deskripsi: OTP generator mulai dari `100000`, bukan `000000`.  
File: [OtpService.php:160](/D:/WEBSITE/DOCKER/AI-AUTH-02/laravel-auth-ai/app/Modules/Authentication/Services/OtpService.php:160)  
Skenario: entropy sedikit turun (1,000,000 -> 900,000).  
Fix:
```php
$max = (10 ** $length) - 1;
$code = random_int(0, $max);
return str_pad((string) $code, $length, '0', STR_PAD_LEFT);
```
Refactor: buat util generator terpusat + test statistik sederhana.

---

### 12) **Low** – Drift konfigurasi/testing menurunkan keandalan audit keamanan
Deskripsi:
- Blade directive timezone memanggil endpoint yang tidak ada (`/api/v1/timezone/set`).
- Sebagian test auth masih pakai namespace lama (`App\DTOs`, `App\Services`), berpotensi tidak jalan sesuai kode aktual.
File: [TimezoneServiceProvider.php:85](/D:/WEBSITE/DOCKER/AI-AUTH-02/laravel-auth-ai/app/Modules/Timezone/TimezoneServiceProvider.php:85), [LoginRiskAssessmentTest.php:5](/D:/WEBSITE/DOCKER/AI-AUTH-02/laravel-auth-ai/tests/Feature/Auth/LoginRiskAssessmentTest.php:5)  
Skenario: false sense of security karena test suite tidak merepresentasikan runtime aktual.  
Fix: sinkronkan endpoint & update namespace test ke modul sekarang.  
Refactor: buat test matrix `AuthFlow`, `RBAC`, `SessionBinding`, `MFA`, `RateLimit`.

---

## Rekomendasi Struktur Refactor (Clean Architecture)

Struktur saat ini sudah modular, tapi masih ada coupling tinggi di controller/service/model event. Saran:

```text
app/
  Domain/
    Auth/
      Entities/
      ValueObjects/
      Policies/
      Events/
    Security/
    Identity/
    Authorization/
  Application/
    Auth/
      Commands/
      Handlers/
      DTO/
      UseCases/
    Security/
  Infrastructure/
    Persistence/Eloquent/
    Http/Controllers/Web/
    Http/Controllers/Api/
    Services/External/
      AiRisk/
      GeoIp/
      Captcha/
  Interfaces/
    Http/Middleware/
    Console/Commands/
```

Prinsip yang disarankan:
1. Controller tipis, semua orkestrasi ke UseCase/Handler.
2. Hindari side-effect berat di Eloquent `booted()`; pindahkan ke domain event listener/queue.
3. Satu kontrak `RiskContext DTO` lintas AI dan fallback.
4. Middleware chain standar untuk route sensitif (`auth.hardened`).
5. Environment hardening: secret manager + rotasi key + larangan hardcoded secret via CI scan (`gitleaks`, `trufflehog`).

---

## Prioritas eksekusi (disarankan)
1. Cabut/rotate semua secret yang terekspos, hapus hardcoded key (Critical).
2. Ubah CAPTCHA ke fail-closed dan perketat security route middleware (High).
3. Perbaiki TOTP secret storage + fallback risk bug (High/Medium).
4. Rapikan boundary web/api auth dan permission granularity (Medium).
5. Refactor bertahap ke use-case layer + test alignment (Medium/Low).

Kalau kamu mau, saya bisa lanjutkan dengan **patch konkret per file** (langsung implementasi) mulai dari 3 prioritas teratas dulu.