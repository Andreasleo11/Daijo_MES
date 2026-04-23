<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Update wms_pallet_form_details — tambah kolom multi-item & no-label
        Schema::table('wms_pallet_form_details', function (Blueprint $table) {
            // Kolom item per box (multi-item support)
            $table->string('part_no', 100)->nullable()->after('pallet_form_id');
            $table->string('model_name', 255)->nullable()->after('part_no');

            // No-label support
            $table->boolean('is_no_label')->default(false)->after('label');
            $table->string('no_label_reason', 100)->nullable()->after('is_no_label');

            // Jadikan spk_no nullable (untuk no-label rows)
            $table->string('spk_no')->nullable()->change();
            // label sudah nullable dari schema lama
        });

        // 2. Jadikan part_no & model_name di header nullable (untuk MIXED pallet)
        Schema::table('wms_pallet_forms', function (Blueprint $table) {
            $table->string('part_no')->nullable()->change();
            $table->string('model_name')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('wms_pallet_form_details', function (Blueprint $table) {
            $table->dropColumn(['part_no', 'model_name', 'is_no_label', 'no_label_reason']);
            $table->string('spk_no')->nullable(false)->change();
        });

        Schema::table('wms_pallet_forms', function (Blueprint $table) {
            $table->string('part_no')->nullable(false)->change();
            $table->string('model_name')->nullable(false)->change();
        });
    }
};
