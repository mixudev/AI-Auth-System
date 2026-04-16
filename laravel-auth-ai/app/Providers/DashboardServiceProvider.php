<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Services\Stats\StatsService;
use App\Services\Dashboard\DashboardStatsService;
use App\Services\Dashboard\DashboardChartService;

class DashboardServiceProvider extends ServiceProvider
{
        /**
     * Daftarkan ke app/Providers/AppServiceProvider.php:
     *
     *   use App\Providers\DashboardServiceProvider;
     *
     *   // di dalam register():
     *   $this->app->register(DashboardServiceProvider::class);
     *
     * Atau tambahkan ke config/app.php di array 'providers'.
     */
    public function register(): void
    {
        // Singleton — satu instance per request (efisien, tidak instantiate ulang)
        $this->app->singleton(DashboardStatsService::class);
        $this->app->singleton(DashboardChartService::class);
    }

    public function boot(StatsService $statscount): void
    {
        View::composer('admin.*', function ($view) use ($statscount) {
            $view->with('statscount', $statscount->get());
        });
    }
}