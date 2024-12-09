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
        Schema::table('second_daily_processes', function (Blueprint $table) {
            $table->float('quantity_hour')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('second_daily_processes', function (Blueprint $table) {
            //
            $table->integer('quantity_hour')->change();
        });
    }
};
