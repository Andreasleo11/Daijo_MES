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
        Schema::create('spk_masters', function (Blueprint $table) {
            $table->id();
            $table->string('spk_number');
            $table->date('post_date');
            $table->date('due_date');
            $table->string('production_status');
            $table->string('item_code');
            $table->integer('planned_quantity');
            $table->integer('completed_quantity');
            $table->string('warehouse');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spk_masters');
    }
};
