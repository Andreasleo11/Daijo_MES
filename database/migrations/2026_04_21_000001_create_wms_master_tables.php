<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wms_warehouses', function (Blueprint $table) {
            $table->id();
            $table->string('whse_code')->unique();
            $table->string('whse_name');
            $table->timestamps();
        });

        Schema::create('wms_racks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('whse_id')->constrained('wms_warehouses');
            $table->string('rack_code');
            $table->timestamps();
        });

        Schema::create('wms_positions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rack_id')->constrained('wms_racks');
            $table->integer('level_no');
            $table->integer('slot_no');
            $table->string('customer_code')->nullable();
            $table->string('position_code')->unique();
            $table->string('status')->default('EMPTY'); // EMPTY, PARTIAL, FULL
            $table->string('last_item_code')->nullable();
            $table->integer('max_capacity')->default(1); // Added capacity field
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wms_positions');
        Schema::dropIfExists('wms_racks');
        Schema::dropIfExists('wms_warehouses');
    }
};
