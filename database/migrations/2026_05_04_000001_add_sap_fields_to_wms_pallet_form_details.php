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
        Schema::table('wms_pallet_form_details', function (Blueprint $blueprint) {
            $blueprint->tinyInteger('sap_sync_status')->default(0)->comment('0: Pending, 1: Success, 2: Error');
            $blueprint->text('sap_error_msg')->nullable();
            $blueprint->timestamp('sap_sync_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wms_pallet_form_details', function (Blueprint $blueprint) {
            $blueprint->dropColumn(['sap_sync_status', 'sap_error_msg', 'sap_sync_at']);
        });
    }
};
