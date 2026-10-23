<?php

namespace App\Services\Notification;

use App\Models\BillingNotification;

/**
 * Turns billing events into BillingNotification rows.
 * Invoice payloads are stdClass rows from `investigation_payments`
 * (columns: ID, BillNo, PatientName, PatientCode, DueAmount, ...).
 */
class BillingNotificationService
{
    public function invoiceDue(\stdClass $invoice): void
    {
        $due = (float) ($invoice->DueAmount ?? 0);

        BillingNotification::create([
            'type'           => 'invoice_due',
            'title'          => 'Invoice Due',
            'message'        => sprintf(
                'Invoice %s for %s has a due amount of ৳ %s.',
                $invoice->BillNo ?? '-',
                $invoice->PatientName ?? '-',
                number_format($due, 2)
            ),
            'invoice_id'     => $invoice->ID ?? null,
            'bill_no'        => $invoice->BillNo ?? null,
            'patient_name'   => $invoice->PatientName ?? null,
            'patient_code'   => $invoice->PatientCode ?? null,
            'reference_name' => null,
            'amount'         => $due,
            'is_read'        => false,
            'notified_at'    => now(),
        ]);
    }

    public function paymentWithReference(
        \stdClass $invoice,
        ?string $referenceName,
        ?int $referenceId,
        $paymentAmount = null
    ): void {
        if (empty($referenceId) && empty($referenceName)) {
            return;
        }

        $amount = $paymentAmount !== null ? (float) $paymentAmount : null;

        BillingNotification::create([
            'type'           => 'payment_reference',
            'title'          => 'Payment via Reference',
            'message'        => sprintf(
                'Payment of ৳ %s received for invoice %s (%s) via reference %s.',
                $amount !== null ? number_format($amount, 2) : '-',
                $invoice->BillNo ?? '-',
                $invoice->PatientName ?? '-',
                $referenceName ?? '-'
            ),
            'invoice_id'     => $invoice->ID ?? null,
            'bill_no'        => $invoice->BillNo ?? null,
            'patient_name'   => $invoice->PatientName ?? null,
            'patient_code'   => $invoice->PatientCode ?? null,
            'reference_name' => $referenceName ?? null,
            'amount'         => $amount,
            'is_read'        => false,
            'notified_at'    => now(),
        ]);
    }

    public function pendingApproval(\stdClass $invoice): void
    {
        $amount = (float) ($invoice->DueAmount ?? ($invoice->TotalBill ?? 0));

        BillingNotification::create([
            'type'           => 'pending_approval',
            'title'          => 'Pending Approval',
            'message'        => sprintf(
                'Invoice %s for %s is pending admin approval (৳ %s).',
                $invoice->BillNo ?? '-',
                $invoice->PatientName ?? '-',
                number_format($amount, 2)
            ),
            'invoice_id'     => $invoice->ID ?? null,
            'bill_no'        => $invoice->BillNo ?? null,
            'patient_name'   => $invoice->PatientName ?? null,
            'patient_code'   => $invoice->PatientCode ?? null,
            'reference_name' => $invoice->RefDoctor ?? $invoice->reference_name ?? null,
            'amount'         => $amount,
            'is_read'        => false,
            'notified_at'    => now(),
        ]);
    }

    public function overdueReminder(\stdClass $invoice): void
    {
        $due = (float) ($invoice->DueAmount ?? 0);

        // Do not create a second UNREAD overdue reminder for the same invoice.
        BillingNotification::updateOrCreate(
            [
                'type'       => 'overdue_reminder',
                'invoice_id' => $invoice->ID ?? null,
                'is_read'    => false,
            ],
            [
                'title'          => 'Overdue Reminder',
                'message'        => sprintf(
                    'Invoice %s for %s is overdue by ৳ %s.',
                    $invoice->BillNo ?? '-',
                    $invoice->PatientName ?? '-',
                    number_format($due, 2)
                ),
                'bill_no'        => $invoice->BillNo ?? null,
                'patient_name'   => $invoice->PatientName ?? null,
                'patient_code'   => $invoice->PatientCode ?? null,
                'reference_name' => null,
                'amount'         => $due,
                'notified_at'    => now(),
            ]
        );
    }
}
