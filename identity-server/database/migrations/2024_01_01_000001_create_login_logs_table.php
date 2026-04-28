<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /*
    |--------------------------------------------------------------------------
    | Tabel audit log untuk seluruh aktivitas percobaan login.
    | Setiap baris mewakili satu percobaan masuk ke sistem.
    |--------------------------------------------------------------------------
    */

    public function up(): void
    {
        Schema::create('login_logs', function (Blueprint $table) {
            $table->id();

            // Referensi ke pengguna (nullable: email tidak dikenal tetap dicatat)
            $table->foreignId('user_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            // Email yang digunakan saat percobaan login
            $table->string('email_attempted', 255)->index();

            // Data jaringan & perangkat
            $table->ipAddress('ip_address')->index();
            $table->string('device_fingerprint', 64)->nullable()->index();
            $table->string('user_agent', 512)->nullable();
            $table->string('country_code', 3)->nullable();

            // Hasil penilaian risiko
            $table->unsignedSmallInteger('risk_score')->nullable();
            $table->enum('decision', ['ALLOW', 'OTP', 'BLOCK', 'PENDING', 'FALLBACK'])
                  ->nullable()
                  ->index();
            $table->json('reason_flags')->nullable();  // Array alasan keputusan
            $table->json('ai_response_raw')->nullable(); // Respons mentah dari AI (tanpa PII)

            // Status akhir percobaan login
            $table->enum('status', ['success', 'otp_required', 'blocked', 'failed', 'fallback'])
                  ->index();

            // Waktu kejadian (tidak menggunakan created_at/updated_at standar)
            $table->timestamp('occurred_at')->index();

            // Indeks komposit untuk query audit yang umum
            $table->index(['user_id', 'occurred_at']);
            $table->index(['ip_address', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('login_logs');
    }
};
