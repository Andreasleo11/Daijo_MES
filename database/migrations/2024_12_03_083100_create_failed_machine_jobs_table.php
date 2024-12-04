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
        Schema::create('failed_machine_jobs', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('machine_id');
            $table->string('spk_no');
            $table->integer('target');
            $table->integer('outstanding');
            $table->longText('reason')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('failed_machine_jobs');
    }
};
