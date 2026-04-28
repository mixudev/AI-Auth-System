<?php

namespace App\Modules\Security;

use Illuminate\Support\ServiceProvider;

/**
 * SecurityServiceProvider
 * 
 * Modul ini fokus pada kebijakan keamanan seluruh sistem (Global Security).
 * Memuat rute dan views yang berkaitan dengan Security Policy.
 */
class SecurityServiceProvider extends ServiceProvider
{
    /**
     * Daftarkan layanan.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap layanan.
     */
    public function boot(): void
    {
        // 1. Muat Rute
        $this->loadRoutesFrom(__DIR__ . '/routes/web.php');

        // 2. Muat Views dengan Namespace 'security'
        $this->loadViewsFrom(__DIR__ . '/Views', 'security');
    }
}
