<?php

namespace App\Modules\AuditLog;

use Illuminate\Support\ServiceProvider;
use App\Modules\AuditLog\Middleware\AuditMiddleware;

/**
 * AuditLogServiceProvider
 * 
 * Modul ini menyediakan layanan pencatatan riwayat aktivitas (Audit Logging).
 * Menyediakan middleware global atau rute-spesifik untuk memantau aksi pengguna.
 */
class AuditLogServiceProvider extends ServiceProvider
{
    /**
     * Daftarkan layanan.
     */
    public function register(): void
    {
        // Daftarkan middleware ke alias agar mudah digunakan di routes
        $this->app['router']->aliasMiddleware('audit', AuditMiddleware::class);
    }

    /**
     * Bootstrap layanan.
     */
    public function boot(): void
    {
        // 1. Muat Rute
        $this->loadRoutesFrom(__DIR__ . '/routes/web.php');

        // 2. Muat Views dengan Namespace 'AuditLog'
        $this->loadViewsFrom(__DIR__ . '/Views', 'AuditLog');
    }
}
