<?php

namespace App\Services\Billing;

use Illuminate\Support\Facades\DB;

class DueAdjustmentService
{
    public function __construct(
        private InvestigationPaymentTransactionService $transactions,
        private InvestigationItemAllocationService $itemAllocations
    )
    {
    }
    /**
     * Create a concession request for an invoice.
     *
     * type 'adjustment' -> credited against PaidAmount (separate PDF line)
     * type 'discount'   -> added to the invoice Discount column
     *
     * Admins (accounttype 99) get it applied immediately (approved);
     * staff requests stay pending until an admin approves.
     */
    public function create(int $invoiceId, float $amount, ?string $reason, int $createdBy, bool $admin, string $type = 'adjustment'): array
    {
        if (!in_array($type, ['adjustment', 'discount'], true)) {
            throw new \InvalidArgumentException('Unknown concession type.');
        }
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Amount must be greater than zero.');
        }

        return DB::transaction(function () use ($invoiceId, $amount, $reason, $createdBy, $admin, $type) {
            $invoice = DB::table('investigation_payments')
                ->where('ID', $invoiceId)
                ->lockForUpdate()
                ->first();

            if (!$invoice) {
                throw new \InvalidArgumentException('Invoice not found.');
            }

            $due = (float) $invoice->DueAmount;
            if ($due <= 0) {
                throw new \InvalidArgumentException('This invoice has no outstanding due.');
            }
            if ($amount > $due) {
                throw new \InvalidArgumentException(
                    'Amount cannot exceed current due of ৳ ' . number_format($due, 0) . '.'
                );
            }

            $status  = $admin ? 'approved' : 'pending';
            $applied = $admin;

            $id = DB::table('invoice_due_adjustments')->insertGetId([
                'invoice_id'       => $invoiceId,
                'adjustment_amount'=> round($amount, 2),
                'type'             => $type,
                'reason'           => $reason,
                'created_by'       => $createdBy,
                'approved_by'      => $admin ? $createdBy : null,
                'approval_status'  => $status,
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);

            if ($applied) {
                $this->apply($invoiceId, $invoice, $amount, $type);
            }

            return ['id' => (int) $id, 'approval_status' => $status, 'applied' => $applied, 'type' => $type];
        });
    }

    public function approve(int $adjustmentId, int $approvedBy): array
    {
        return DB::transaction(function () use ($adjustmentId, $approvedBy) {
            $adjustment = DB::table('invoice_due_adjustments')
                ->where('id', $adjustmentId)
                ->lockForUpdate()
                ->first();

            if (!$adjustment) {
                throw new \InvalidArgumentException('Concession not found.');
            }
            if ($adjustment->approval_status !== 'pending') {
                throw new \InvalidArgumentException('Only pending concessions can be approved.');
            }

            $amount = (float) $adjustment->adjustment_amount;
            $type   = $adjustment->type ?? 'adjustment';

            $invoice = DB::table('investigation_payments')
                ->where('ID', $adjustment->invoice_id)
                ->lockForUpdate()
                ->first();

            if (!$invoice) {
                throw new \InvalidArgumentException('Invoice not found.');
            }

            $due = (float) $invoice->DueAmount;
            if ($amount > $due) {
                throw new \InvalidArgumentException(
                    'Amount ৳ ' . number_format($amount, 0) . ' exceeds current due of ৳ ' . number_format($due, 0) . '.'
                );
            }

            DB::table('invoice_due_adjustments')->where('id', $adjustmentId)->update([
                'approval_status' => 'approved',
                'approved_by'     => $approvedBy,
                'updated_at'      => now(),
            ]);

            $this->apply($adjustment->invoice_id, $invoice, $amount, $type);

            return ['id' => (int) $adjustmentId, 'approval_status' => 'approved'];
        });
    }

    public function reject(int $adjustmentId): array
    {
        return DB::transaction(function () use ($adjustmentId) {
            $adjustment = DB::table('invoice_due_adjustments')
                ->where('id', $adjustmentId)
                ->lockForUpdate()
                ->first();

            if (!$adjustment) {
                throw new \InvalidArgumentException('Concession not found.');
            }
            if ($adjustment->approval_status !== 'pending') {
                throw new \InvalidArgumentException('Only pending concessions can be rejected.');
            }

            DB::table('invoice_due_adjustments')->where('id', $adjustmentId)->update([
                'approval_status' => 'rejected',
                'updated_at'      => now(),
            ]);

            return ['id' => (int) $adjustmentId, 'approval_status' => 'rejected'];
        });
    }

    /**
     * Reverse an applied (approved, not yet reversed) concession.
     * Never deletes or overwrites: creates a linked reversal row,
     * marks the original as reversed and restores the invoice.
     */
    public function reverse(int $adjustmentId, int $userId, ?string $reason): array
    {
        return DB::transaction(function () use ($adjustmentId, $userId, $reason) {
            $adjustment = DB::table('invoice_due_adjustments')
                ->where('id', $adjustmentId)
                ->lockForUpdate()
                ->first();

            if (!$adjustment) {
                throw new \InvalidArgumentException('Concession not found.');
            }
            if ($adjustment->approval_status !== 'approved') {
                throw new \InvalidArgumentException('Only applied (approved) concessions can be reversed.');
            }
            if ($adjustment->reversal_of_id !== null) {
                throw new \InvalidArgumentException('This is a reversal record and cannot be reversed again.');
            }

            $amount = (float) $adjustment->adjustment_amount;
            $type   = $adjustment->type ?? 'adjustment';

            $invoice = DB::table('investigation_payments')
                ->where('ID', $adjustment->invoice_id)
                ->lockForUpdate()
                ->first();

            if (!$invoice) {
                throw new \InvalidArgumentException('Invoice not found.');
            }

            $now = now();

            DB::table('invoice_due_adjustments')->insert([
                'invoice_id'       => $adjustment->invoice_id,
                'adjustment_amount'=> -1 * round($amount, 2),
                'type'             => $type,
                'reason'           => 'Reversal of #' . $adjustmentId
                    . ($reason ? ' — ' . $reason : ''),
                'created_by'       => $userId,
                'approved_by'      => $userId,
                'approval_status'  => 'approved',
                'reversal_of_id'   => $adjustmentId,
                'created_at'       => $now,
                'updated_at'       => $now,
            ]);

            DB::table('invoice_due_adjustments')->where('id', $adjustmentId)->update([
                'approval_status' => 'reversed',
                'reversed_at'     => $now,
                'reversed_by'     => $userId,
                'updated_at'      => $now,
            ]);

            $this->restore($adjustment->invoice_id, $invoice, $amount, $type);

            return ['id' => (int) $adjustmentId, 'approval_status' => 'reversed'];
        });
    }

    /**
     * Net applied adjustment amount for an invoice (type 'adjustment' only —
     * discounts live in the Discount column, not this ledger).
     * Only non-reversed original rows count: reversal rows exist to cancel
     * their original (which becomes 'reversed'), so the sum is the net effect.
     */
    public function activeAdjustmentTotal(int $invoiceId): float
    {
        return (float) DB::table('invoice_due_adjustments')
            ->where('invoice_id', $invoiceId)
            ->where('approval_status', 'approved')
            ->where('type', 'adjustment')
            ->whereNull('reversal_of_id')
            ->sum('adjustment_amount');
    }

    /**
     * Apply an approved concession to the invoice.
     *
     * investigation_payments has a BEFORE UPDATE trigger that recomputes
     * DueAmount = TotalBill - Discount - PaidAmount on every write and
     * rejects negative amounts or PaidAmount > net payable, so concessions
     * must flow through the stored columns:
     *   - 'discount'   : Discount += amount   (net payable drops)
     *   - 'adjustment' : PaidAmount += amount (zero-cash credit)
     */
    private function apply(int $invoiceId, object $invoice, float $amount, string $type): void
    {
        if ($type === 'discount') {
            $newDisc = (float) $invoice->Discount + $amount;
            $newDue  = max(0, (float) $invoice->TotalBill - $newDisc - (float) $invoice->PaidAmount);
        } else {
            $newDisc = (float) $invoice->Discount;
            $newPaid = (float) $invoice->PaidAmount + $amount;
            $newDue  = max(0, (float) $invoice->TotalBill - $newDisc - $newPaid);
        }

        $status = $newDue <= 0 ? 'paid' : 'partial';

        DB::table('investigation_payments')->where('ID', $invoiceId)->update([
            'Discount'   => $newDisc,
            'PaidAmount' => $newPaid ?? (float) $invoice->PaidAmount,
            'Status'     => $status,
            'updated_at' => now(),
        ]);

        $items = $this->itemAllocations->apply($invoiceId, $amount, $type);
        $this->transactions->recordItems($invoiceId, $type, $items, now()->toDateString(), null, null, 'due_adjustment');
    }

    /**
     * Pull a reversed concession back out of the invoice.
     */
    private function restore(int $invoiceId, object $invoice, float $amount, string $type): void
    {
        if ($type === 'discount') {
            $newDisc = max(0, (float) $invoice->Discount - $amount);
            $newPaid = (float) $invoice->PaidAmount;
        } else {
            $newDisc = (float) $invoice->Discount;
            $newPaid = max(0, (float) $invoice->PaidAmount - $amount);
        }

        $newDue = max(0, (float) $invoice->TotalBill - $newDisc - $newPaid);
        $status = $newDue <= 0 ? 'paid' : ($newPaid > 0 ? 'partial' : 'due');

        DB::table('investigation_payments')->where('ID', $invoiceId)->update([
            'Discount'   => $newDisc,
            'PaidAmount' => $newPaid,
            'Status'     => $status,
            'updated_at' => now(),
        ]);

        $items = $this->itemAllocations->reverse($invoiceId, $amount, $type);
        $this->transactions->recordItems($invoiceId, $type, array_map(fn ($value) => -$value, $items), now()->toDateString(), null, null, 'due_adjustment_reversal');
    }
}
