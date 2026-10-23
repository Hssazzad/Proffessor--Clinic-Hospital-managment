<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * OT Collection — payouts made to the operation team.
 *
 * This is an EXPENSE record (money leaving the clinic), the counterpart of the
 * billing module's income postings. One row per payout; the accounting side is
 * a separate double-entry voucher whose RefNumber is stored back here in
 * `ref_number` so the payment and its ledger entry stay linked.
 *
 * `payee_name`, `patient_code` and `patient_name` are deliberate SNAPSHOTS of
 * the master data at payment time. The doctor and patient masters are editable,
 * so resolving the name through a join at report time would silently rewrite
 * historical payouts after somebody is renamed.
 *
 * `expense_account` / `credit_account` snapshot which ledgers were actually
 * used, because config('accounting.ot_expense_account') can be repointed later.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ot_payments')) {
            return;
        }

        Schema::create('ot_payments', function (Blueprint $table) {
            $table->id();

            // ---- Who was paid ----
            $table->string('payee_type', 30)->index();
            $table->unsignedBigInteger('payee_id');
            $table->string('payee_name', 255);

            // ---- Which patient the operation was for ----
            $table->unsignedBigInteger('patient_id')->nullable()->index();
            $table->unsignedBigInteger('admission_id')->nullable()->index();
            $table->string('patient_code', 30)->nullable();
            $table->string('patient_name', 150)->nullable();

            // ---- Money ----
            $table->decimal('amount', 14, 2);
            $table->date('payment_date')->index();
            $table->string('payment_method', 10);          // Cash | Bank
            $table->string('note', 255)->nullable();

            // ---- Accounting link ----
            $table->string('expense_account', 20);         // GL account debited
            $table->string('credit_account', 20);          // GL account credited
            $table->string('ref_number', 50)->nullable()->index();
            $table->string('status', 20)->default('unauthorized');

            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index(['payee_type', 'payee_id']);
            $table->index(['payment_date', 'payee_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ot_payments');
    }
};