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
        Schema::create('second_process_ng_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained('second_process_reports')->onDelete('cascade');
            $table->string('ng_name');
            $table->integer('hour_1')->default(0);
            $table->integer('hour_2')->default(0);
            $table->integer('hour_3')->default(0);
            $table->integer('hour_4')->default(0);
            $table->integer('hour_5')->default(0);
            $table->integer('hour_6')->default(0);
            $table->integer('hour_7')->default(0);
            $table->integer('hour_8')->default(0);
            $table->integer('hour_9')->default(0);
            $table->integer('hour_10')->default(0);
            $table->integer('hour_11')->default(0);
            $table->integer('hour_12')->default(0);
            $table->integer('total_ng')->default(0);
            $table->string('ng_input_item')->nullable();
            $table->integer('ng_input_qty')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('second_process_ng_records');
    }
};
