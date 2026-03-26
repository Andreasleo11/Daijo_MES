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
        Schema::table('production_scanned_data', function (Blueprint $table) {
            $table->unsignedBigInteger('summary_id')->nullable()->after('processed');
            $table->index('summary_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('production_scanned_data', function (Blueprint $table) {
            $table->dropIndex(['summary_id']);
            $table->dropColumn('summary_id');
        });
    }
};
