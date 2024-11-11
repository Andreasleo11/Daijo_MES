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
        Schema::create('prd_bill_of_material_childs', function (Blueprint $table) {
            $table->id();
            $table->integer('parent_id');
            $table->string('item_code');
            $table->string('item_description');
            $table->integer('quantity');
            $table->string('measure');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prd_bill_of_material_childs');
    }
};
