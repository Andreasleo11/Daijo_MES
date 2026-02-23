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
        Schema::create('delsched_process_logs', function (Blueprint $table) {
            $table->id();
            $table->string('process_key')->unique(); // 'delsched_main' atau 'delsched_wip'
            $table->string('current_step')->default('idle'); // idle, step1, step2, ...
            $table->string('status')->default('idle'); // idle, running, done, failed
            $table->text('message')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delsched_process_logs');
    }
};
