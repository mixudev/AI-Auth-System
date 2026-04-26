<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sso_clients', function (Blueprint $table) {
            $table->id();
            $table->char('oauth_client_id', 36)->nullable(); // Menyesuaikan dengan uuid passport
            $table->string('name');
            $table->string('webhook_url')->nullable();
            $table->string('webhook_secret')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Note: FK tidak langsung ditambahkan di sini karena oauth_clients di-generate oleh passport
            // secara internal, namun jika diinginkan bisa ditambahkan nanti.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sso_clients');
    }
};
