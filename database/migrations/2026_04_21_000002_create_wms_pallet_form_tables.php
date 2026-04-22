<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop then recreate to ensure clean structure
        Schema::dropIfExists('wms_pallet_form_details');
        Schema::dropIfExists('wms_pallet_forms');

        Schema::create('wms_pallet_forms', function (Blueprint $table) {
            $table->string('pallet_id')->primary();
            $table->foreignId('position_id')->nullable()->constrained('wms_positions');
            $table->string('part_no');
            $table->string('model_name')->nullable();
            $table->date('prod_date')->nullable();
            $table->string('lot_no')->nullable();
            $table->string('delivery_name')->nullable();
            $table->string('delivery_shift')->nullable();
            $table->integer('box_qty')->default(0);
            $table->decimal('total_pallet_qty', 15, 2)->default(0);
            $table->string('status')->default('STORED'); // STORED, OUT
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        Schema::create('wms_pallet_form_details', function (Blueprint $table) {
            $table->id();
            $table->string('pallet_form_id');
            $table->foreign('pallet_form_id')->references('pallet_id')->on('wms_pallet_forms')->onDelete('cascade');
            $table->string('spk_no');
            $table->decimal('qty', 15, 2)->default(0);
            $table->string('warehouse')->nullable();
            $table->string('label')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wms_pallet_form_details');
        Schema::dropIfExists('wms_pallet_forms');
    }
};
