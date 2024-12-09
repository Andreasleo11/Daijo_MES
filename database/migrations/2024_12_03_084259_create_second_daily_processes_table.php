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
        Schema::create('second_daily_processes', function (Blueprint $table) {
            $table->id();
            $table->date('plan_date');
            $table->string('line');
            $table->string('item_code');
            $table->string('item_description');
            $table->integer('quantity_hour')->nullable();
            $table->double('process_time')->nullable();
            $table->integer('quantity_plan')->nullable();
            $table->string('pic');
            $table->string('customer');
            $table->integer('shift');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('second_daily_processes');
    }
};
