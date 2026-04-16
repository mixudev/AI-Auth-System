<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migrasi.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Menambah metadata login tambahan setelah kolom last_login_ip yang sudah ada
            $table->string('last_login_country', 3)->nullable()->after('last_login_ip');
            $table->string('last_login_ua', 512)->nullable()->after('last_login_country');
            $table->string('last_login_device', 128)->nullable()->after('last_login_ua');
        });
    }

    /**
     * Batalkan migrasi.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['last_login_country', 'last_login_ua', 'last_login_device']);
        });
    }
};
