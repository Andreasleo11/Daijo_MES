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
        Schema::table('daily_item_codes', function (Blueprint $table) {
            $table->integer('actual_quantity')->after('loss_package_quantity');
            $table->dropTimestamps();
            $table->dropSoftDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_item_codes', function (Blueprint $table) {
            $table->dropColumn('actual_quantity');
            $table->timestamps();
            $table->softDeletes();
        });
    }
};
