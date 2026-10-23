<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tbl_bill_tmp')) {
            return;
        }
        if (Schema::hasColumn('tbl_bill_tmp', 'chamber_fee_config_id')) {
            return;
        }
        Schema::table('tbl_bill_tmp', function (Blueprint $table) {
            $table->unsignedBigInteger('chamber_fee_config_id')->nullable()->after('DoctorId');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('tbl_bill_tmp') || !Schema::hasColumn('tbl_bill_tmp', 'chamber_fee_config_id')) {
            return;
        }
        Schema::table('tbl_bill_tmp', function (Blueprint $table) {
            $table->dropColumn('chamber_fee_config_id');
        });
    }
};