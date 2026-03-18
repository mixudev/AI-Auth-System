<?php

namespace App\Providers;

use App\Repositories\TrustedDeviceRepository;
use App\Services\AiRiskClientService;
use App\Services\DeviceFingerprintService;
use App\Services\LoginAuditService;
use App\Services\LoginRiskService;
use App\Services\OtpService;
use App\Services\RiskFallbackService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cache;

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
