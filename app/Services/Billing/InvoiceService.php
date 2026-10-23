<?php

namespace App\Services\Billing;

use App\Http\Requests\Billing\CreateInvoiceRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class InvoiceService
{
    public function __construct(
        protected ConsultationFeeService $consultationFeeService,
        protected InvestigationPaymentTransactionService $transactions,
        protected InvestigationItemAllocationService $itemAllocations,
        protected \App\Services\Accounting\AccountingIntegrationService $accounting,
        protected \App\Services\Notification\BillingNotificationService $billingNotificationService
    ) {}

    public function createInvoice(CreateInvoiceRequest $request, string $paymentSource = 'reception')
    {
        $paidAmount = (float) $request->paid_amount;
        $referenceName = trim((string) ($request->reference_name ?? ''));

        $refDoctorId = ! empty($request->ref_doctor_id) ? (int) $request->ref_doctor_id : null;
        $refDoctorName = null;
        if ($refDoctorId) {
            $refDoctorName = DB::table('doctors')->where('id', $refDoctorId)->value('doctor_name');
        }

        $validatedItems = $request->validated('items');
        $rawItems = $request->input('items', []);

        $consultations = [];

        // ── Recalculate consultation fees FIRST so the invoice header
        //    total always matches the actual item amounts. The chamber
        //    fee config is authoritative — a stale/tampered fee sent by
        //    the client is corrected here and the totals below are
        //    recomputed from the corrected items (avoids the item
        //    allocation exceeding the available amount on the split).
        //
        //    Chamber fee is FULLY config-driven: the frontend MUST send
        //    chamber_fee_config_id for every doctor item. There is no
        //    fallback lookup — if a doctor item arrives without one, we
        //    fail loudly instead of silently charging the wrong fee.
        foreach ($validatedItems as $index => &$item) {
            $raw = $rawItems[$index] ?? [];
            $item['chamber_fee_config_id'] = $raw['chamber_fee_config_id'] ?? null;

            if (! empty($item['doctor_id'])) {
                $configId = ! empty($item['chamber_fee_config_id'])
                    ? (int) $item['chamber_fee_config_id']
                    : null;

                if (! $configId) {
                    throw new \InvalidArgumentException(
                        'chamber_fee_config_id is required for doctor consultation "'
                        .($item['service_name'] ?? '').'" (doctor_id: '
                        .$item['doctor_id'].'). Please select a Chamber Type and try again.'
                    );
                }

                $result = $this->consultationFeeService->calculate(
                    (int) $request->patient_id,
                    (int) $item['doctor_id'],
                    $configId
                );

                if (! $result['success']) {
                    throw new \InvalidArgumentException(
                        'Unable to calculate consultation fee: '
                        .($result['message'] ?? 'chamber fee configuration not found.')
                    );
                }

                $item['unit_price'] = $result['fee'];
                $item['amount'] = $result['fee'];

                $consultations[] = [
                    'patient_id' => (int) $request->patient_id,
                    'doctor_id' => (int) $item['doctor_id'],
                    'chamber_fee_config_id' => $configId,
                    'visit_type' => $result['visit_type'],
                    'consultation_fee' => $result['fee'],
                ];
            }
        }
        unset($item);

        Log::info('Consultation data prepared', [
            'count' => count($consultations),
            'details' => $consultations,
        ]);

        // ── Invoice total = sum of the authoritative item amounts ──
        //    (same filter as insertPaymentItems, so the header always
        //    matches the item rows that are actually stored).
        $totalBill = round(
            collect($validatedItems)
                ->filter(fn ($item) => ! empty(trim($item['service_name'] ?? '')))
                ->sum(fn ($item) => (float) $item['amount']),
            2
        );

        // ── Discount: flat (taka) or percent, with backward compatibility ──
        // Old callers send only `discount` (treated as PERCENT). New callers
        // send discount_type + discount_value (raw input) + discount (taka).
        $discountType = (string) $request->input('discount_type', 'percent');
        $discountValue = (float) $request->input('discount_value', 0);
        $discountInput = (float) $request->input('discount', 0);

        if ($discountType === 'flat') {
            $flatAmount = $discountValue > 0 ? $discountValue : $discountInput;
            $discountAmount = round(min($flatAmount, $totalBill), 2);
            $effectivePercent = $totalBill > 0 ? round($discountAmount / $totalBill * 100, 2) : 0;
        } else {
            $pct = min(max($discountValue > 0 ? $discountValue : $discountInput, 0), 100);
            $discountAmount = round($totalBill * $pct / 100, 2);
            $effectivePercent = $pct;
        }

        $netAmount = max(0, $totalBill - $discountAmount);
        // 50% threshold is ALWAYS on the ORIGINAL total_bill — the discount
        // must never lower it (same formula as CreateInvoiceRequest).
        $minRequired = ceil($totalBill * 0.5);

        if ($paidAmount > $netAmount) {
            $message = 'Paid amount cannot exceed net payable of ৳ '.number_format($netAmount, 0).'.';
            if (abs($totalBill - (float) $request->total_bill) > 0.001) {
                $message .= ' The bill total was recalculated from ৳ '
                    .number_format((float) $request->total_bill, 0)
                    .' to ৳ '.number_format($totalBill, 0).'.';
            }
            throw new \InvalidArgumentException($message);
        }

        $insertApprovalStatus = 'approved';

        if ($discountAmount > 0) {
            // Rules 4–5: ANY discount requires a reference name and ALWAYS
            // goes to pending admin approval, regardless of amount paid.
            if (empty($referenceName)) {
                throw new \InvalidArgumentException(
                    'Discount requires a Reference By name for admin approval.'
                );
            }
            $insertApprovalStatus = 'pending';
        } elseif ($paidAmount < $minRequired) {
            // Rules 1–3: the 50%-of-total rule applies only when there is NO
            // discount (a reference name routes it to pending admin approval).
            if (empty($referenceName)) {
                throw new \InvalidArgumentException(
                    'Minimum 50% of the total bill required. Please pay at least ৳ '.number_format($minRequired, 0).'.'
                );
            }
            $insertApprovalStatus = 'pending';
        }

        $dueAmount = max(0, $netAmount - $paidAmount);
        $status = $this->determinePaymentStatus($paidAmount, $netAmount);

        DB::beginTransaction();

        try {
            $billNo = $this->generateBillNumber();

            $insert = [
                'BillNo' => $billNo,
                'PatientId' => $request->patient_id,
                'PatientCode' => $request->patient_code,
                'PatientName' => $request->patient_name,
                'PatientAge' => $request->patient_age,
                'MobileNo' => $request->mobile_no,
                'AdmissionId' => $request->admission_id,
                'TotalBill' => $totalBill,
                'Discount' => $discountAmount,
                'PaidAmount' => $paidAmount,
                'DueAmount' => $dueAmount,
                'PaymentDate' => $request->payment_date,
                'PaymentMethod' => $request->payment_method,
                'CollectedBy' => $request->collected_by,
                'ref_doctor_id' => $refDoctorId,
                'RefDoctor' => $refDoctorName,
                'Status' => $status,
                'reference_name' => $referenceName,
                'approval_status' => $insertApprovalStatus,
                'approved_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // Same guard style as the discount_type block below: only sent
            // when the live column exists (fresh test DBs may lack it).
            // Never taken from user input — the caller passes it explicitly
            // ('reception' default, 'manager' from ManagerCollectionController).
            if (Schema::hasColumn('investigation_payments', 'payment_source')) {
                $insert['payment_source'] = $paymentSource;
            }

            $paymentId = DB::table('investigation_payments')->insertGetId($insert);

            // Audit columns (discount type / raw value / equivalent percent) —
            // only when the columns exist, so the invoice still saves before
            // the migration is run on live.
            if (Schema::hasColumn('investigation_payments', 'discount_type')) {
                DB::table('investigation_payments')->where('ID', $paymentId)->update([
                    'discount_type' => $discountType,
                    'discount_value' => Schema::hasColumn('investigation_payments', 'discount_value')
                        ? $discountValue : null,
                    'discount_percent' => Schema::hasColumn('investigation_payments', 'discount_percent')
                        ? $effectivePercent : null,
                    'updated_at' => now(),
                ]);
            }

            $this->insertPaymentItems($paymentId, $validatedItems, $request->input('delivery_datetime'));
            $allocations = $this->itemAllocations->startInvoice($paymentId, $discountAmount, $paidAmount);
            $this->transactions->recordItems($paymentId, 'cash', $allocations['cash'], $request->payment_date, $request->payment_method, $request->collected_by, 'invoice');
            $this->transactions->recordItems($paymentId, 'discount', $allocations['discount'], $request->payment_date, null, null, 'invoice');
            $this->clearTemporaryBillItems($request->patient_code);

            if (! empty($consultations)) {
                Log::info('Inserting patient_doctor_visits', [
                    'count' => count($consultations),
                    'invoice_id' => $paymentId,
                ]);

                foreach ($consultations as $consultation) {
                    DB::table('patient_doctor_visits')->insert([
                        'patient_id' => $consultation['patient_id'],
                        'doctor_id' => $consultation['doctor_id'],
                        'invoice_id' => $paymentId,
                        'chamber_fee_config_id' => $consultation['chamber_fee_config_id'],
                        'visit_type' => $consultation['visit_type'],
                        'consultation_fee' => $consultation['consultation_fee'],
                        'visit_date' => now(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                Log::info('patient_doctor_visits inserted successfully', [
                    'count' => count($consultations),
                    'invoice_id' => $paymentId,
                ]);
            } else {
                Log::info('No consultations to insert into patient_doctor_visits');
            }

            $invoiceObj = DB::table('investigation_payments')->where('ID', $paymentId)->first();
         //   $this->accounting->postInvestigationInvoice($invoiceObj, $request->payment_date);  // Auto posting to accounting

            DB::commit();

            // Notify after commit so a notification failure never rolls
            // back a successfully posted invoice. Log & swallow only the
            // notification exception — never the billing/accounting result.
            try {
                if ((float) ($invoiceObj->DueAmount ?? 0) > 0) {
                    $this->billingNotificationService->invoiceDue($invoiceObj);
                }
                if (($invoiceObj->approval_status ?? 'approved') === 'pending') {
                    $this->billingNotificationService->pendingApproval($invoiceObj);
                }
            } catch (\Throwable $e) {
                Log::error('Billing notification creation failed', [
                    'invoice_id' => $paymentId,
                    'error' => $e->getMessage(),
                ]);
            }

            return [
                'success' => true,
                'payment_id' => $paymentId,
                'bill_no' => $billNo,
            ];

        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Invoice Creation Failed', [
                'patient_id' => $request->patient_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    private function generateBillNumber(): string
    {
        return DB::transaction(function () {
            $lastBillNo = DB::table('investigation_payments')
                ->lockForUpdate()
                ->orderByDesc('ID')
                ->value('BillNo');

            $nextNum = $lastBillNo ? (intval(substr($lastBillNo, 4)) + 1) : 1;

            return 'INV-'.str_pad($nextNum, 5, '0', STR_PAD_LEFT);
        });
    }

    private function insertPaymentItems(int $paymentId, array $items, ?string $deliveryDateTime = null): void
    {
        $itemsToInsert = [];

        // datetime-local submits "2026-08-10T16:30" — normalize to the
        // "YYYY-MM-DD HH:MM:SS" MySQL datetime shape the column expects.
        $delivery = null;
        if (! empty($deliveryDateTime)) {
            $delivery = str_replace('T', ' ', (string) $deliveryDateTime);
        }

        foreach ($items as $item) {
            if (empty(trim($item['service_name'] ?? ''))) {
                continue;
            }

            $itemsToInsert[] = [
                'PaymentId' => $paymentId,
                'CategoryCode' => $item['category'] ?? null,
                'CategoryName' => $item['category_name'] ?? null,
                'ServiceName' => $item['service_name'],
                'DoctorId' => ! empty($item['doctor_id']) ? (int) $item['doctor_id'] : null,
                'chamber_fee_config_id' => ! empty($item['chamber_fee_config_id'])
                    ? (int) $item['chamber_fee_config_id'] : null,
                'UnitPrice' => $item['unit_price'] ?? 0,
                'Quantity' => $item['quantity'] ?? 1,
                'Amount' => $item['amount'] ?? 0,
                'Remarks' => $item['remarks'] ?? null,
                'DeliveryDateTime' => $delivery,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (! empty($itemsToInsert)) {
            DB::table('investigation_payment_items')->insert($itemsToInsert);
        }
    }

    private function clearTemporaryBillItems(?string $patientCode): void
    {
        if ($patientCode) {
            DB::table('tbl_bill_tmp')
                ->where('PatientCode', $patientCode)
                ->delete();
        }
    }

    private function determinePaymentStatus(float $paidAmount, float $netAmount): string
    {
        if ($paidAmount >= $netAmount) {
            return 'paid';
        } elseif ($paidAmount > 0) {
            return 'partial';
        }

        return 'due';
    }
}
