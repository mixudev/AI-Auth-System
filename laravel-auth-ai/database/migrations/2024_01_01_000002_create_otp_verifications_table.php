<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /*
    |--------------------------------------------------------------------------
    | Tabel untuk menyimpan sesi OTP yang menunggu verifikasi.
    | Rekaman lama dibersihkan secara berkala via scheduled command.
    |--------------------------------------------------------------------------
    */

    public function up(): void
    {
        Schema::create('otp_verifications', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            // Hash Bcrypt dari kode OTP — kode asli tidak disimpan
            $table->string('token', 255);

            // Token sesi sementara untuk menghubungkan proses verifikasi OTP
            $table->string('session_token', 64)->unique()->index();

            // Data konteks percobaan OTP
            $table->ipAddress('ip_address');
            $table->string('device_fingerprint', 64)->nullable();

            // Kontrol kedaluwarsa dan percobaan
            $table->timestamp('expires_at')->index();
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamp('verified_at')->nullable();

            $table->timestamps();

            // Indeks untuk pencarian OTP aktif per pengguna
            $table->index(['user_id', 'verified_at', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('otp_verifications');
    }
};
