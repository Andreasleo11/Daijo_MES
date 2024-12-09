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
        Schema::create('so_datas', function (Blueprint $table) {
            $table->id();
            $table->string('doc_num');
            $table->string('customer');
            $table->date('posting_date');
            $table->string('item_code');
            $table->string('item_name');
            $table->integer('quantity');
            $table->string('sales_uom');
            $table->integer('packaging_quantity');
            $table->string('sales_pack');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('so_datas');
    }
};
