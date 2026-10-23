<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Chamber Fee is fully config-driven: the ONLY contract between the
     * frontend, the temporary bill, the invoice items and the visit record
     * is chamber_fee_config_id. This migration guarantees the column exists
     * on every table that must carry it, so new chamber types can be added
     * to chamber_fee_configs without any code change.
     */
    public function up(): void
    {
        foreach (['tbl_bill_tmp', 'patient_doctor_visits', 'investigation_payment_items'] as $table) {
            if (!Schema::hasTable($table)) {
                continue;
            }
            if (!Schema::hasColumn($table, 'chamber_fee_config_id')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->unsignedBigInteger('chamber_fee_config_id')->nullable();
                });
            }
        }
    }

    public function down(): void
    {
        foreach (['tbl_bill_tmp', 'patient_doctor_visits', 'investigation_payment_items'] as $table) {
            if (!Schema::hasTable($table)) {
                continue;
            }
            if (Schema::hasColumn($table, 'chamber_fee_config_id')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->dropColumn('chamber_fee_config_id');
                });
            }
        }
    }
};