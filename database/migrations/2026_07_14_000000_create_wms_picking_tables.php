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
        Schema::create('wms_picking_headers', function (Blueprint $table) {
            $table->id();
            $table->string('picking_no')->unique();
            $table->string('doc_num')->nullable(); // SO/DO number if imported
            $table->string('status')->default('PENDING'); // PENDING, COMPLETED, CANCELLED
            $table->foreignId('created_by')->nullable();
            $table->timestamps();
        });

        Schema::create('wms_picking_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('picking_header_id')->constrained('wms_picking_headers')->onDelete('cascade');
            $table->string('item_code');
            $table->string('model_name')->nullable();
            $table->string('spk_no')->nullable();
            $table->string('label')->nullable();
            $table->string('pallet_id')->nullable();
            $table->string('position_code')->nullable();
            $table->double('qty_to_pick', 15, 2)->default(0.00);
            $table->double('qty_picked', 15, 2)->default(0.00);
            $table->boolean('is_picked')->default(false);
            $table->integer('fifo_seq')->nullable();
            $table->string('status')->default('AVAILABLE'); // AVAILABLE, STOCK_SHORTAGE, OUT_OF_STOCK
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wms_picking_details');
        Schema::dropIfExists('wms_picking_headers');
    }
};
