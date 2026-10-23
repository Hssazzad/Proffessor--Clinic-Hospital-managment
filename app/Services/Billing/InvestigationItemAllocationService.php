<?php

namespace App\Services\Billing;

use Illuminate\Support\Facades\DB;

class InvestigationItemAllocationService
{
    public function startInvoice(int $invoiceId, float $discount, float $paid): array
    {
        $items = $this->items($invoiceId);
        $discounts = $this->split($items, $discount, 'Amount');
        $afterDiscount = $items->map(fn ($item) => (object) [
            'ID' => $item->ID,
            'available' => (float) $item->Amount - $discounts[$item->ID],
        ]);
        $payments = $this->split($afterDiscount, $paid, 'available');

        foreach ($items as $item) {
            $itemDiscount = $discounts[$item->ID];
            $itemPaid = $payments[$item->ID];
            DB::table('investigation_payment_items')->where('ID', $item->ID)->update([
                'DiscountAmount' => $itemDiscount,
                'PaidAmount' => $itemPaid,
                'DueAmount' => round((float) $item->Amount - $itemDiscount - $itemPaid, 2),
            ]);
        }

        return ['discount' => $discounts, 'cash' => $payments];
    }

    public function apply(int $invoiceId, float $amount, string $type): array
    {
        $items = $this->items($invoiceId);
        $this->ensureStarted($invoiceId, $items);
        $items = $this->items($invoiceId);
        $allocations = $this->split($items, $amount, 'DueAmount');

        foreach ($items as $item) {
            $allocated = $allocations[$item->ID];
            DB::table('investigation_payment_items')->where('ID', $item->ID)->update([
                $type === 'discount' ? 'DiscountAmount' : 'PaidAmount' => round((float) $item->{$type === 'discount' ? 'DiscountAmount' : 'PaidAmount'} + $allocated, 2),
                'DueAmount' => round((float) $item->DueAmount - $allocated, 2),
            ]);
        }

        return $allocations;
    }

    public function reverse(int $invoiceId, float $amount, string $type): array
    {
        $items = $this->items($invoiceId);
        $field = $type === 'discount' ? 'DiscountAmount' : 'PaidAmount';
        $allocations = $this->split($items, $amount, $field);

        foreach ($items as $item) {
            $allocated = $allocations[$item->ID];
            DB::table('investigation_payment_items')->where('ID', $item->ID)->update([
                $field => round((float) $item->{$field} - $allocated, 2),
                'DueAmount' => round((float) $item->DueAmount + $allocated, 2),
            ]);
        }

        return $allocations;
    }

    private function ensureStarted(int $invoiceId, $items): void
    {
        if ($items->sum(fn ($item) => (float) $item->PaidAmount + (float) $item->DiscountAmount + (float) $item->DueAmount) > 0) {
            return;
        }

        $invoice = DB::table('investigation_payments')->where('ID', $invoiceId)->first();
        $this->startInvoice($invoiceId, (float) ($invoice->Discount ?? 0), (float) ($invoice->PaidAmount ?? 0));
    }

    private function items(int $invoiceId)
    {
        return DB::table('investigation_payment_items')->where('PaymentId', $invoiceId)->orderBy('ID')->lockForUpdate()->get();
    }

    /** Split money to 2 decimals; the leftover cents go to the largest remainders. */
    private function split($items, float $amount, string $field): array
    {
        $totalCents = (int) round($items->sum(fn ($item) => (float) $item->{$field}) * 100);
        $amountCents = (int) round($amount * 100);
        if ($amountCents < 0 || $amountCents > $totalCents) {
            throw new \InvalidArgumentException('Item allocation exceeds the available amount.');
        }

        $rows = $items->map(function ($item) use ($field, $amountCents, $totalCents) {
            $capacity = (int) round((float) $item->{$field} * 100);
            $value = $totalCents ? $amountCents * $capacity : 0;
            return ['id' => $item->ID, 'cents' => intdiv($value, $totalCents ?: 1), 'remainder' => $value % ($totalCents ?: 1)];
        })->sortByDesc('remainder')->values();

        $left = $amountCents - $rows->sum('cents');
        if ($left > 0) {
            $rows = $rows->map(function ($row, $i) use ($left) {
                if ($i < $left) {
                    $row['cents']++;
                }
                return $row;
            });
        }

        return $rows->mapWithKeys(fn ($row) => [$row['id'] => $row['cents'] / 100])->all();
    }
}
