<?php

namespace App\Modules\WaGateway;

use App\Modules\WaGateway\Services\WaAlertService;
use App\Modules\WaGateway\Observers\SecurityNotificationObserver;
use App\Modules\Security\Models\SecurityNotification;
use Illuminate\Support\ServiceProvider;

class WaGatewayServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/Config/wa_gateway.php', 'wa_gateway');
        $this->app->singleton(WaAlertService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Load routes
        $this->loadRoutesFrom(__DIR__ . '/routes/web.php');

        // Load views (module-local)
        $this->loadViewsFrom(__DIR__ . '/resources/views', 'wa-gateway');

        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/migrations');

        // Publish assets
        $this->publishes([
            __DIR__ . '/resources' => resource_path('admin/wa-gateway'),
        ], 'wa-gateway-assets');

        // Publish config
        $this->publishes([
            __DIR__ . '/Config/wa_gateway.php' => config_path('wa_gateway.php'),
        ], 'wa-gateway-config');

        // Register observer untuk SecurityNotification
        // Ini akan otomatis mengirim alert critical ke WA Gateway
        SecurityNotification::observe(SecurityNotificationObserver::class);

        // View composer untuk semua view
        \Illuminate\Support\Facades\View::composer(
            'wa-gateway::*',
            function ($view) {
                $view->with('module', 'wa-gateway');
            }
        );
    }
}
