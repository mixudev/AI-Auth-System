<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /*
    |--------------------------------------------------------------------------
    | Tabel untuk menyimpan daftar perangkat terpercaya milik setiap pengguna.
    | Perangkat baru yang belum terdaftar akan meningkatkan skor risiko.
    |--------------------------------------------------------------------------
    */

    public function up(): void
    {
        Schema::create('trusted_devices', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            // Hash SHA-256 dari fingerprint perangkat
            $table->string('fingerprint_hash', 64)->index();

            // Label perangkat yang mudah dibaca manusia
            $table->string('device_label', 255)->nullable();

            // Lokasi dan jaringan saat perangkat pertama kali dipercaya
            $table->ipAddress('ip_address')->nullable();
            $table->string('country_code', 3)->nullable();

            // Kontrol kepercayaan
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamp('trusted_until')->nullable()->index();
            $table->boolean('is_revoked')->default(false)->index();

            $table->timestamps();

            // Satu pengguna tidak boleh mendaftarkan fingerprint yang sama dua kali
            $table->unique(['user_id', 'fingerprint_hash']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trusted_devices');
    }
};
