<?php

namespace App\Modules\Settings;

use Illuminate\Support\ServiceProvider;

/**
 * SettingsServiceProvider
 * 
 * Modul ini menangani konfigurasi sistem global. 
 * Memuat rute, views, dan menyediakan akses ke data pengaturan di seluruh aplikasi.
 */
class SettingsServiceProvider extends ServiceProvider
{
    /**
     * Daftarkan layanan ke container.
     */
    public function register(): void
    {
        //
    }

    /**
     * Jalankan proses bootstrap layanan.
     */
    public function boot(): void
    {
        // 0. Cek apakah fitur Konfigurasi diaktifkan via .env
        if (!env('APP_CONFIG_UI', true)) {
            return;
        }

        // 1. Muat Rute untuk modul Settings
        $this->loadRoutesFrom(__DIR__ . '/routes/web.php');

        // 2. Muat Views dengan Namespace 'settings'
        $this->loadViewsFrom(__DIR__ . '/Views', 'settings');

        // 3. Override Config dari Database
        $this->syncDatabaseConfig();
    }

    /**
     * Sinkronisasi nilai konfigurasi Laravel dengan data dari database.
     */
    protected function syncDatabaseConfig(): void
    {
        try {
            // Site Identity
            $siteName = Models\Setting::get('site_name');
            if ($siteName) {
                config(['app.name' => $siteName]);
            }

            // Mail Configuration (Full SMTP Stack)
            $mailSettings = [
                'mail_host'         => 'mail.mailers.smtp.host',
                'mail_port'         => 'mail.mailers.smtp.port',
                'mail_username'     => 'mail.mailers.smtp.username',
                'mail_password'     => 'mail.mailers.smtp.password',
                'mail_encryption'   => 'mail.mailers.smtp.encryption',
                'mail_from_address' => 'mail.from.address',
                'mail_from_name'    => 'mail.from.name',
            ];

            foreach ($mailSettings as $settingKey => $configKey) {
                $val = Models\Setting::get($settingKey);
                if ($val !== null && $val !== '') {
                    config([$configKey => ($settingKey === 'mail_port' ? (int)$val : $val)]);
                }
            }

        } catch (\Throwable $e) {
            // Silently fail if DB not ready
        }
    }
}
