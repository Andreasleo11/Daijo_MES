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
        Schema::create('alc_pe_master_data', function (Blueprint $table) {
            $table->id();
            $table->string('part_code');
            $table->string('part_name');
            $table->string('qad');
            $table->string('ukuran_label');
            $table->string('alc_code');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alc_pe_master_data');
    }
};
