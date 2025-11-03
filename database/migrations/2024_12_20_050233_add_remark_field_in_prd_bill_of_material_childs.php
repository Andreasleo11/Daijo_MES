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
        Schema::table('prd_bill_of_material_childs', function (Blueprint $table) {
            $table->string('remark')->after('action_type')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prd_bill_of_material_childs', function (Blueprint $table) {
            $table->dropColumn('remark');
        });
    }
};
