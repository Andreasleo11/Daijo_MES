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
        Schema::create('api_logs', function (Blueprint $table) {
            $table->id();
            $table->string('api_name'); // contoh: "spk_sync", "receipt_production"
            $table->string('method')->nullable(); // GET / POST
            $table->text('endpoint')->nullable(); // endpoint yang dipanggil
            $table->json('request_payload')->nullable(); // data dikirim
            $table->json('response_payload')->nullable(); // response dari API
            $table->integer('status_code')->nullable(); // HTTP status
            $table->string('status')->default('failed'); // success/failed
            $table->text('message')->nullable(); // pesan error / success
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_logs');
    }
};
