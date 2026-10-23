<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds `payment_source` to `investigation_payments`.
 *
 * Guarded with Schema::hasColumn so it is a safe no-op on the live
 * database where the column (VARCHAR(20) NULL) already exists.
 * Semantics: NULL = legacy rows, 'reception' = normal Create Payment,
 * 'manager' = Manager Collection page.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('investigation_payments')) {
            return;
        }

        if (! Schema::hasColumn('investigation_payments', 'payment_source')) {
            Schema::table('investigation_payments', function (Blueprint $table) {
                $table->string('payment_source', 20)->nullable()->after('approval_status');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('investigation_payments')
            && Schema::hasColumn('investigation_payments', 'payment_source')) {
            Schema::table('investigation_payments', function (Blueprint $table) {
                $table->dropColumn('payment_source');
            });
        }
    }
};
