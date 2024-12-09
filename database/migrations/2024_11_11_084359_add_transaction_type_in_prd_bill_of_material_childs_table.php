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
            $table->string('action_type')->nullable()->after('measure');
            $table->string('status')->default('Not Started')->after('measure');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prd_bill_of_material_childs', function (Blueprint $table) {
            $table->dropColumn('action_type');
            $table->dropColumn('status');
        });
    }
};
