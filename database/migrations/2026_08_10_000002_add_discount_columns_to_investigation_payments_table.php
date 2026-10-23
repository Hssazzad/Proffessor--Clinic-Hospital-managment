<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('investigation_payments')) {
            return;
        }

        if (!Schema::hasColumn('investigation_payments', 'discount_type')) {
            Schema::table('investigation_payments', function (Blueprint $table) {
                $table->string('discount_type')->nullable()->default('percent')->after('Discount');
            });
        }

        if (!Schema::hasColumn('investigation_payments', 'discount_value')) {
            Schema::table('investigation_payments', function (Blueprint $table) {
                $table->decimal('discount_value', 10, 2)->nullable()->after('discount_type');
            });
        }

        if (!Schema::hasColumn('investigation_payments', 'discount_percent')) {
            Schema::table('investigation_payments', function (Blueprint $table) {
                $table->decimal('discount_percent', 5, 2)->nullable()->after('discount_value');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('investigation_payments')) {
            return;
        }

        foreach (['discount_type', 'discount_value', 'discount_percent'] as $column) {
            if (Schema::hasColumn('investigation_payments', $column)) {
                Schema::table('investigation_payments', function (Blueprint $table) use ($column) {
                    $table->dropColumn($column);
                });
            }
        }
    }
};
