<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('machine_jobs', function (Blueprint $table) {
            $table->unsignedBigInteger('dic_id')->nullable()->after('item_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('machine_jobs', function (Blueprint $table) {
            $table->dropColumn('dic_id');
        });
    }
};
