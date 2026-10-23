<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates `investigation_payment_items` when missing (fresh test databases).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('investigation_payment_items')) {
            return;
        }

        Schema::create('investigation_payment_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_id')->nullable()->index();
            $table->string('item_name', 150)->nullable();
            $table->decimal('quantity', 10, 2)->default(1);
            $table->decimal('rate', 12, 2)->default(0);
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('Remarks', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('investigation_payment_items');
    }
};
