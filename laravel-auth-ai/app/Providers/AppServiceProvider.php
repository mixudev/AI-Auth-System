<?php

namespace App\Providers;

use App\Services\Auth\BlockingService;
use App\Services\Auth\LoginRiskService;
use App\Services\Auth\LoginAuditService;
use App\Services\Auth\OtpService;
use App\Services\Security\DeviceFingerprintService;
use App\Services\Security\AiRiskClientService;
use App\Services\Security\RiskFallbackService;
use App\Services\Stats\StatsService;
use App\Services\User\UserService;
use App\Repositories\TrustedDeviceRepository;
use App\Models\SecurityNotification;
use App\Models\TrustedDevice;
use App\Models\User;
use App\Policies\SecurityNotificationPolicy;
use App\Policies\TrustedDevicePolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;


class AppServiceProvider extends ServiceProvider
{
    /*
    |--------------------------------------------------------------------------
    | Registrasi binding service container.
    |
    | Semua service didaftarkan sebagai singleton untuk menghindari
    | pembuatan instance berulang dalam satu siklus request.
    |--------------------------------------------------------------------------
    */

    public function register(): void
    {
        // Singleton: satu instance per request lifecycle
        $this->app->singleton(DeviceFingerprintService::class);
        $this->app->singleton(AiRiskClientService::class);
        $this->app->singleton(RiskFallbackService::class);
        $this->app->singleton(OtpService::class);
        $this->app->singleton(BlockingService::class);

        // Service dengan dependency injection otomatis via container
        $this->app->singleton(LoginRiskService::class, function ($app) {
            return new LoginRiskService(
                $app->make(DeviceFingerprintService::class)
            );
        });

        $this->app->singleton(LoginAuditService::class, function ($app) {
            return new LoginAuditService(
                $app->make(DeviceFingerprintService::class)
            );
        });

        $this->app->singleton(TrustedDeviceRepository::class, function ($app) {
            return new TrustedDeviceRepository(
                $app->make(DeviceFingerprintService::class)
            );
        });
    }

    public function boot(): void
    {
        // Paksa koneksi HTTPS di environment production
        if ($this->app->environment('production')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        // RBAC glue:
        // - super-admin bypasses everything
        // - if an ability looks like a permission slug (e.g. "users.edit"), map it to RBAC permissions
        Gate::before(static function (User $user, string $ability) {
            if ($user->hasRole('super-admin')) {
                return true;
            }

            if (str_contains($ability, '.')) {
                return $user->hasPermission($ability) ? true : null;
            }

            return null;
        });

        // Backward-compatible gates used across requests/controllers
        Gate::define('access-admin-panel', static fn (User $user): bool => $user->can('dashboard.view'));
        Gate::define('access-admin-security', static fn (User $user): bool => $user->can('settings.security') || $user->can('errors.view'));

        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(SecurityNotification::class, SecurityNotificationPolicy::class);
        Gate::policy(TrustedDevice::class, TrustedDevicePolicy::class);

        RateLimiter::for('mfa', static function ($request) {
            // Prefer session_token-based limiting so API + web are both protected.
            // Fallback to session email (web) or IP-only (last resort).
            $sessionToken = (string) $request->input('session_token', session('mfa_session_token', ''));
            $tokenKey = $sessionToken !== '' ? hash('sha256', $sessionToken) : '';

            $email = strtolower((string) $request->input('email', session('mfa_email', '')));
            $emailKey = $email !== '' ? hash('sha256', $email) : '';

            $key = $tokenKey !== ''
                ? "mfa|token:{$tokenKey}|ip:{$request->ip()}"
                : ($emailKey !== ''
                    ? "mfa|email:{$emailKey}|ip:{$request->ip()}"
                    : "mfa|ip:{$request->ip()}");

            return Limit::perMinute(5)->by($key);
        });

        RateLimiter::for('verification-send', static function ($request) {
            $userId = (string) optional($request->user())->id;
            return Limit::perMinutes(10, 3)->by($userId !== '' ? $userId : $request->ip());
        });

        RateLimiter::for('admin-actions', static function ($request) {
            $userId = (string) optional($request->user())->id;
            return Limit::perMinute(30)->by($userId !== '' ? $userId : $request->ip());
        });

        View::composer('layouts.app', function ($view) {
            $aiOnline = Cache::remember('ai_status', 15, function () {
                try {
                    return Http::timeout(2)->get('http://fastapi-risk:8000/health')->successful();
                } catch (\Exception $e) {
                    return false;
                }
            });

            $view->with('aiOnline', $aiOnline);
        });

        
    }
}
