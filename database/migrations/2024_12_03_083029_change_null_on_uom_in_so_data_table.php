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
        Schema::table('so_datas', function (Blueprint $table) {
            //
            $table->string('sales_uom')->nullable()->change();
            $table->string('sales_pack')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('so_datas', function (Blueprint $table) {
            //
            $table->string('sales_uom')->nullable(false)->change();
            $table->string('sales_pack')->nullable(false)->change();
        });
    }
};
