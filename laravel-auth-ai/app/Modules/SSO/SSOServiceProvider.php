<?php

namespace App\Modules\SSO;

use App\Modules\SSO\Models\PassportClient;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

class SSOServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // ── Passport Configuration ─────────────────────────────────────────
        // Abaikan pengecekan permission key (penting untuk Docker/Windows)
        Passport::$validateKeyPermissions = false;

        // Ambil expiry dari database (Gunakan helper Setting)
        $accessExpiry  = (int) \App\Modules\Settings\Models\Setting::get('token_expiry_access', 120);
        $refreshExpiry = (int) \App\Modules\Settings\Models\Setting::get('token_expiry_refresh', 43200); // 30 hari dalam menit

        Passport::tokensExpireIn(now()->addMinutes($accessExpiry));
        Passport::refreshTokensExpireIn(now()->addMinutes($refreshExpiry));
        Passport::personalAccessTokensExpireIn(now()->addMonths(6));

        // Use custom client model to skip authorization prompts for seamless SSO
        Passport::useClientModel(PassportClient::class);

        // Use custom consent view (as a fallback)
        Passport::authorizationView('sso.authorize');

        // ── Routes ─────────────────────────────────────────────────────────
        $this->loadRoutesFrom(__DIR__ . '/routes/web.php');

        // CATATAN: enablePasswordGrant() sengaja TIDAK dipanggil.
        // Sistem SSO ini menggunakan Authorization Code Grant — bukan Password Grant.
        // Password Grant sudah deprecated di Passport v12+ dan tidak diperlukan.
    }
}
