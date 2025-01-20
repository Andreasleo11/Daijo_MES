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
        Schema::create('holiday_schedules', function (Blueprint $table) {
            $table->id();
            $table->datetime('date');
            $table->string('description');
            $table->string('injection');
            $table->string('second_process');
            $table->string('assembly');
            $table->string('moulding');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('holiday_schedules');
    }
};
