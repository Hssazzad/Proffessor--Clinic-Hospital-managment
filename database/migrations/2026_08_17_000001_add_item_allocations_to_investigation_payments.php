<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('investigation_payment_items')) { return; }

        Schema::table('investigation_payment_items', function (Blueprint $table) {
            if (!Schema::hasColumn('investigation_payment_items', 'DiscountAmount')) $table->decimal('DiscountAmount', 12, 2)->default(0);
            if (!Schema::hasColumn('investigation_payment_items', 'PaidAmount')) $table->decimal('PaidAmount', 12, 2)->default(0);
            if (!Schema::hasColumn('investigation_payment_items', 'DueAmount')) $table->decimal('DueAmount', 12, 2)->default(0);
        });

        if (Schema::hasTable('investigation_payment_transactions') && !Schema::hasColumn('investigation_payment_transactions', 'payment_item_id')) {
            Schema::table('investigation_payment_transactions', fn (Blueprint $table) => $table->unsignedBigInteger('payment_item_id')->nullable()->after('invoice_id')->index());
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('investigation_payment_items')) { return; }

        Schema::table('investigation_payment_items', function (Blueprint $table) {
            $table->dropColumn(['DiscountAmount', 'PaidAmount', 'DueAmount']);
        });
        if (Schema::hasTable('investigation_payment_transactions') && Schema::hasColumn('investigation_payment_transactions', 'payment_item_id')) {
            Schema::table('investigation_payment_transactions', fn (Blueprint $table) => $table->dropColumn('payment_item_id'));
        }
    }
};
