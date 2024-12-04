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
        Schema::create('production_scanned_data', function (Blueprint $table) {
            $table->id();
            $table->string('spk_code');
            $table->string('item_code');
            $table->string('warehouse');
            $table->integer('quantity');
            $table->string('label');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_scanned_data');
    }
};
