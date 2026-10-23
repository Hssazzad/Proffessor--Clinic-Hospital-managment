<?php

namespace App\Services\Billing;

use Illuminate\Support\Facades\DB;

class InvestigationPaymentTransactionService
{
    public function record(int $invoiceId, string $type, float $amount, $date, ?string $method = null, ?string $collector = null, ?string $source = null): void
    {
        if ($amount == 0.0) {
            return;
        }

        DB::table('investigation_payment_transactions')->insert([
            'invoice_id'     => $invoiceId,
            'type'           => $type,
            'amount'         => round($amount, 2),
            'transaction_date' => $date,
            'payment_method' => $method,
            'collected_by'   => $collector,
            'source'         => $source,
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);
    }

    public function recordItems(int $invoiceId, string $type, array $allocations, $date, ?string $method = null, ?string $collector = null, ?string $source = null): void
    {
        foreach ($allocations as $itemId => $amount) {
            if ($amount == 0.0) continue;
            DB::table('investigation_payment_transactions')->insert([
                'invoice_id' => $invoiceId, 'payment_item_id' => $itemId, 'type' => $type,
                'amount' => $amount, 'transaction_date' => $date, 'payment_method' => $method,
                'collected_by' => $collector, 'source' => $source, 'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }
}
