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
        Schema::create("wa_gateway_logs", function (Blueprint $table) {
            $table->id();
            $table->foreignId("wa_gateway_config_id")
                ->constrained("wa_gateway_configs")
                ->onDelete("cascade");
            $table->string("target_number");
            $table->longText("message");
            $table->enum("status", ["pending", "success", "failed"])->default("pending");
            $table->string("response_id")->nullable();
            $table->json("response_data")->nullable();
            $table->text("error_message")->nullable();
            $table->timestamp("sent_at")->nullable();
            $table->timestamps();

            $table->index(["wa_gateway_config_id", "status"]);
            $table->index(["sent_at"]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("wa_gateway_logs");
    }
};
