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
        Schema::create("wa_gateway_configs", function (Blueprint $table) {
            $table->id();
            $table->foreignId("user_id")->constrained("users")->onDelete("cascade");
            $table->string("name");
            $table->text("token")->encrypted();
            $table->string("alert_phone_number");
            $table->boolean("send_on_critical_alert")->default(false);
            $table->boolean("is_active")->default(true);
            $table->string("webhook_url")->nullable();
            $table->json("meta")->nullable();
            $table->timestamps();

            $table->index(["user_id", "is_active"]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("wa_gateway_configs");
    }
};
