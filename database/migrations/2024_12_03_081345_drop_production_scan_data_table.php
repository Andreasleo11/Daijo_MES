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
        Schema::dropIfExists('production_scan_data');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('production_scan_data', function (Blueprint $table) {
            $table->id();
            $table->integer('daily_item_code_id');
            $table->string('item_code');
            $table->string('label');
            $table->timestamps();
        });
    }
};
