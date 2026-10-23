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
        if (!Schema::hasTable('investigation_payment_items')) { return; }

        Schema::table('investigation_payment_items', function (Blueprint $table) {
            $table->dateTime('DeliveryDateTime')->nullable()->after('Remarks');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('investigation_payment_items')) { return; }

        Schema::table('investigation_payment_items', function (Blueprint $table) {
            $table->dropColumn('DeliveryDateTime');
        });
    }
};
