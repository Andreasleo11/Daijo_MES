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
            $table->integer('quantity')->nullable();
            $table->integer('final_quantity')->nullable();
            $table->integer('loss_package_quantity')->nullable();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_item_codes', function (Blueprint $table) {
            $table->dropColumn('quantity');
            $table->dropColumn('final_quantity');
            $table->dropColumn('loss_package_quantity');
            $table->dropSoftDeletes();
        });
    }
};
