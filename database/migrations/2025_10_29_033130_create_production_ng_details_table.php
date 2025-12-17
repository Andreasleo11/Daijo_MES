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
        Schema::create('production_ng_details', function (Blueprint $table) {
            $table->id();
            $table->integer('hourly_remark_id');
            $table->integer('ng_type_id');
            $table->integer('ng_quantity');
            $table->string('ng_remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_ng_details');
    }
};
