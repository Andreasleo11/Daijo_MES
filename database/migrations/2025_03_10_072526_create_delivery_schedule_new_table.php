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
        Schema::create('delivery_schedule_new', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->integer('so_number')->nullable();
            $table->string('customer_code')->nullable();
            $table->date('delivery_date');
            $table->string('item_code');
            $table->integer('delivery_quantity');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_schedule_new');
    }
};
