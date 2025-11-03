<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('holiday_schedules', function (Blueprint $table) {
            $table->date('date')->change(); // Change 'date' column to date type
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('holiday_schedules', function (Blueprint $table) {
            $table->dateTime('date')->change(); // Revert back to datetime if needed
        });
    }
};
