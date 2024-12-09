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
        Schema::create('prd_list_all_master_items', function (Blueprint $table) {
            $table->id();
            $table->string('item_code');
            $table->string('item_description');
            $table->integer('group_type');
            $table->string('uom')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prd_list_all_master_items');
    }
};
