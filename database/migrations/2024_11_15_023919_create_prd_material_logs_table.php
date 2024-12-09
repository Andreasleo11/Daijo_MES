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
        Schema::create('prd_material_logs', function (Blueprint $table) {
            $table->id();
            $table->integer('child_id');
            $table->string('process_name');
            $table->datetime('scan_in')->nullable();
            $table->datetime('scan_out')->nullable();
            $table->integer('status')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prd_material_logs');
    }
};
