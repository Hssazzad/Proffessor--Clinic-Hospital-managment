<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('prescriptions')) {
            return;
        }

        if (!Schema::hasColumn('prescriptions', 'oe_nursing_id')) {
            Schema::table('prescriptions', function (Blueprint $table) {
                $table->unsignedBigInteger('oe_nursing_id')->nullable()->index();
            });
        }

        if (!Schema::hasColumn('prescriptions', 'oe_pulse')) {
            Schema::table('prescriptions', function (Blueprint $table) {
                $table->string('oe_pulse', 50)->nullable();
            });
        }

        if (!Schema::hasColumn('prescriptions', 'oe_bp')) {
            Schema::table('prescriptions', function (Blueprint $table) {
                $table->string('oe_bp', 50)->nullable();
            });
        }

        if (!Schema::hasColumn('prescriptions', 'oe_temp')) {
            Schema::table('prescriptions', function (Blueprint $table) {
                $table->string('oe_temp', 50)->nullable();
            });
        }

        if (!Schema::hasColumn('prescriptions', 'oe_weight')) {
            Schema::table('prescriptions', function (Blueprint $table) {
                $table->string('oe_weight', 50)->nullable();
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('prescriptions')) {
            return;
        }

        foreach (['oe_nursing_id', 'oe_pulse', 'oe_bp', 'oe_temp', 'oe_weight'] as $column) {
            if (Schema::hasColumn('prescriptions', $column)) {
                Schema::table('prescriptions', function (Blueprint $table) use ($column) {
                    $table->dropColumn($column);
                });
            }
        }
    }
};
