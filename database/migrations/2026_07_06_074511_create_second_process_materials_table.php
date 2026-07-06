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
        Schema::create('second_process_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained('second_process_reports')->onDelete('cascade');
            $table->string('type'); // 'paint' or 'part'
            $table->string('item_name');
            $table->string('lot_number')->nullable();
            $table->string('visco')->nullable();
            $table->integer('qty')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('second_process_materials');
    }
};
