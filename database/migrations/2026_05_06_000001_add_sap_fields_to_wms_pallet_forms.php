<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wms_pallet_forms', function (Blueprint $table) {
            $table->integer('sap_sync_status')->default(0)->after('remarks'); // 0: Pending, 1: Success, 2: Failed/Partial
            $table->text('sap_error_msg')->nullable()->after('sap_sync_status');
            $table->timestamp('sap_sync_at')->nullable()->after('sap_error_msg');
        });
    }

    public function down(): void
    {
        Schema::table('wms_pallet_forms', function (Blueprint $table) {
            $table->dropColumn(['sap_sync_status', 'sap_error_msg', 'sap_sync_at']);
        });
    }
};
