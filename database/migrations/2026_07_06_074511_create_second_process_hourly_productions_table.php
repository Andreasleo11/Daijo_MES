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
        Schema::create('second_process_hourly_productions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained('second_process_reports')->onDelete('cascade');
            $table->integer('hour_ke');
            $table->integer('ok_qty')->default(0);
            $table->integer('acumulasi_qty')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('second_process_hourly_productions');
    }
};
