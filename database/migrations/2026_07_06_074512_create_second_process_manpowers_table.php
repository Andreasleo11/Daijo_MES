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
        Schema::create('second_process_manpowers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained('second_process_reports')->onDelete('cascade');
            $table->string('role'); // loading, sprayer, checker
            $table->integer('no');
            $table->string('name')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('second_process_manpowers');
    }
};
