<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add a type discriminator so the same approval/audit infrastructure
     * covers both post-invoice concessions:
     *   - 'adjustment' : credited against PaidAmount, shown as a separate
     *                    "Due Adjustment" line on the PDF invoice
     *   - 'discount'   : added to the invoice Discount column (net payable
     *                    drops, shows in the standard Discount line/reports)
     */
    public function up(): void
    {
        if (!Schema::hasTable('invoice_due_adjustments')) {
            return;
        }

        Schema::table('invoice_due_adjustments', function (Blueprint $table) {
            $table->enum('type', ['adjustment', 'discount'])
                ->default('adjustment')
                ->after('reason');
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('invoice_due_adjustments') && Schema::hasColumn('invoice_due_adjustments', 'type')) {
            Schema::table('invoice_due_adjustments', function (Blueprint $table) {
                $table->dropColumn('type');
            });
        }
    }
};
