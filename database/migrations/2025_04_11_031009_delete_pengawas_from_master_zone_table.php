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
        Schema::table('master_zone', function (Blueprint $table) {
            $table->dropColumn(['pengawas', 'start_date', 'end_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_zone', function (Blueprint $table) {
            $table->string('pengawas')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
        });
    }
};
