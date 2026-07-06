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
        Schema::table('wms_warehouses', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('wms_racks', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('wms_positions', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('wms_pallet_forms', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('wms_pallet_form_details', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wms_warehouses', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('wms_racks', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('wms_positions', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('wms_pallet_forms', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('wms_pallet_form_details', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
