<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates `investigation_payments` when missing (fresh test databases).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('investigation_payments')) {
            return;
        }

        Schema::create('investigation_payments', function (Blueprint $table) {
            $table->id();
            $table->string('InvoiceNo', 50)->nullable();
            $table->string('PatientCode', 30)->nullable();
            $table->string('PatientName', 150)->nullable();
            $table->decimal('TotalAmount', 12, 2)->default(0);
            $table->decimal('Discount', 12, 2)->default(0);
            $table->decimal('NetAmount', 12, 2)->default(0);
            $table->decimal('PaidAmount', 12, 2)->default(0);
            $table->decimal('DueAmount', 12, 2)->default(0);
            $table->unsignedBigInteger('CollectedBy')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('investigation_payments');
    }
};
