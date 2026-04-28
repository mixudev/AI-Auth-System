<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wa_messages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('to_number', 30);
            $table->string('event_type', 80)->default('manual'); // manual, login_blocked, ip_blocked, risk_high, test
            $table->text('message');
            $table->enum('status', ['pending', 'sent', 'failed'])->default('pending');
            $table->string('wamid')->nullable();             // ID pesan dari Evolution API
            $table->text('error')->nullable();               // Pesan error jika gagal
            $table->tinyInteger('attempts')->default(0);     // Jumlah percobaan kirim
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index('event_type');
            $table->index('to_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wa_messages');
    }
};
