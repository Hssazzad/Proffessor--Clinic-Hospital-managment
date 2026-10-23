<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('investigation_payment_transactions')) {
            return;
        }

        Schema::create('investigation_payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_id');
            $table->string('type', 20); // cash or discount
            $table->decimal('amount', 12, 2);
            $table->date('transaction_date');
            $table->string('payment_method', 50)->nullable();
            $table->string('collected_by', 100)->nullable();
            $table->string('source', 50)->nullable();
            $table->timestamps();
            $table->index(['transaction_date', 'type']);
            $table->index('invoice_id');
        });

        // Legacy invoices only have the current balances, not individual
        // payment dates. Preserve those balances once as opening entries.
        DB::table('investigation_payments')->orderBy('ID')->each(function ($invoice) {
            $date = $invoice->PaymentDate ?: now()->toDateString();

            foreach (['cash' => 'PaidAmount', 'discount' => 'Discount'] as $type => $column) {
                $amount = (float) ($invoice->{$column} ?? 0);
                if ($amount <= 0) {
                    continue;
                }

                DB::table('investigation_payment_transactions')->insert([
                    'invoice_id' => $invoice->ID,
                    'type' => $type,
                    'amount' => round($amount, 2),
                    'transaction_date' => $date,
                    'payment_method' => $type === 'cash' ? ($invoice->PaymentMethod ?? null) : null,
                    'collected_by' => $invoice->CollectedBy ?? null,
                    'source' => 'opening_balance',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('investigation_payment_transactions');
    }
};
