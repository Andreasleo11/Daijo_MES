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
        Schema::create('master_delivery_schedule', function (Blueprint $table) {
            $table->id();
            $table->string('customer_code');
            $table->string('item_code');
            $table->date('tanggal');
            $table->integer('quantity');
            $table->string('so_num')->nullable();
            $table->timestamps();

            // Index untuk performa
            $table->index(['customer_code', 'item_code', 'tanggal']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_delivery_schedule');
    }
};
