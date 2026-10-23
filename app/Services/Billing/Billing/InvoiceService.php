<?php

namespace App\Services\Billing;

use App\Http\Requests\Billing\CreateInvoiceRequest;
use App\Models\ChamberFeeConfig;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InvoiceService
{
    public function __construct(
        protected ConsultationFeeService $consultationFeeService
    ) {}

    public function createInvoice(CreateInvoiceRequest $request)
    {
        $totalBill  = (float) $request->total_bill;
        $discount   = (float) $request->input('discount', 0);
        $paidAmount = (float) $request->paid_amount;
        $referenceName = $request->reference_name ?? null;

        $netAmount   = max(0, $totalBill - $discount);
        $minRequired = ceil($netAmount * 0.5);

        if ($paidAmount > $netAmount) {
            throw new \InvalidArgumentException(
                'Paid amount cannot exceed net payable of ৳ ' . number_format($netAmount, 0) . '.'
            );
        }

        $insertApprovalStatus = 'approved';

        if (empty($referenceName)) {
            if ($paidAmount < $minRequired) {
                throw new \InvalidArgumentException(
                    'Minimum 50% required. Please pay at least ৳ ' . number_format($minRequired, 0) . '.'
                );
            }
        } else {
            if ($paidAmount < $minRequired) {
                $insertApprovalStatus = 'pending';
            }
        }

        $dueAmount = max(0, $netAmount - $paidAmount);
        $status    = $this->determinePaymentStatus($paidAmount, $netAmount);

        $validatedItems = $request->validated('items');
        $rawItems = $request->input('items', []);

        $consultations = [];

        foreach ($validatedItems as $index => &$item) {
            $raw = $rawItems[$index] ?? [];
            $item['chamber_fee_config_id'] = $raw['chamber_fee_config_id'] ?? null;

            if (!empty($item['doctor_id'])) {
                $configId = !empty($item['chamber_fee_config_id'])
                    ? (int) $item['chamber_fee_config_id']
                    : $this->resolveChamberFeeConfigId();

                if (!$configId) {
                    Log::warning('Skipping consultation fee calculation — no chamber_fee_config_id found', [
                        'doctor_id' => $item['doctor_id'],
                        'service_name' => $item['service_name'] ?? '',
                    ]);
                    continue;
                }

                $result = $this->consultationFeeService->calculate(
                    (int) $request->patient_id,
                    (int) $item['doctor_id'],
                    $configId
                );

                if ($result['success']) {
                    $item['unit_price'] = $result['fee'];
                    $item['amount'] = $result['fee'];

                    $consultations[] = [
                        'patient_id'            => (int) $request->patient_id,
                        'doctor_id'             => (int) $item['doctor_id'],
                        'chamber_fee_config_id' => $configId,
                        'visit_type'            => $result['visit_type'],
                        'consultation_fee'      => $result['fee'],
                    ];
                }
            }
        }
        unset($item);

        Log::info('Consultation data prepared', [
            'count' => count($consultations),
            'details' => $consultations,
        ]);

        DB::beginTransaction();

        try {
            $billNo = $this->generateBillNumber();

            $paymentId = DB::table('investigation_payments')->insertGetId([
                'BillNo'        => $billNo,
                'PatientId'     => $request->patient_id,
                'PatientCode'   => $request->patient_code,
                'PatientName'   => $request->patient_name,
                'PatientAge'    => $request->patient_age,
                'MobileNo'      => $request->mobile_no,
                'AdmissionId'   => $request->admission_id,
                'TotalBill'     => $totalBill,
                'Discount'      => $discount,
                'PaidAmount'    => $paidAmount,
                'DueAmount'     => $dueAmount,
                'PaymentDate'   => $request->payment_date,
                'PaymentMethod' => $request->payment_method,
                'CollectedBy'   => $request->collected_by,
                'Status'        => $status,
                'reference_name' => $referenceName,
                'approval_status' => $insertApprovalStatus,
                'approved_by' => null,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);

            $this->insertPaymentItems($paymentId, $validatedItems);
            $this->clearTemporaryBillItems($request->patient_code);

            if (!empty($consultations)) {
                Log::info('Inserting patient_doctor_visits', [
                    'count' => count($consultations),
                    'invoice_id' => $paymentId,
                ]);

                foreach ($consultations as $consultation) {
                    DB::table('patient_doctor_visits')->insert([
                        'patient_id'            => $consultation['patient_id'],
                        'doctor_id'             => $consultation['doctor_id'],
                        'invoice_id'            => $paymentId,
                        'chamber_fee_config_id' => $consultation['chamber_fee_config_id'],
                        'visit_type'            => $consultation['visit_type'],
                        'consultation_fee'      => $consultation['consultation_fee'],
                        'visit_date'            => now(),
                        'created_at'            => now(),
                        'updated_at'            => now(),
                    ]);
                }

                Log::info('patient_doctor_visits inserted successfully', [
                    'count' => count($consultations),
                    'invoice_id' => $paymentId,
                ]);
            } else {
                Log::info('No consultations to insert into patient_doctor_visits');
            }

            DB::commit();

            return [
                'success'    => true,
                'payment_id' => $paymentId,
                'bill_no'    => $billNo,
            ];

        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Invoice Creation Failed', [
                'patient_id' => $request->patient_id,
                'error'      => $e->getMessage(),
                'trace'      => $e->getTraceAsString()
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
            return 'INV-' . str_pad($nextNum, 5, '0', STR_PAD_LEFT);
        });
    }

    private function insertPaymentItems(int $paymentId, array $items): void
    {
        $itemsToInsert = [];

        foreach ($items as $item) {
            if (empty(trim($item['service_name'] ?? ''))) {
                continue;
            }

            $itemsToInsert[] = [
                'PaymentId'    => $paymentId,
                'CategoryCode' => $item['category'] ?? null,
                'CategoryName' => $item['category_name'] ?? null,
                'ServiceName'  => $item['service_name'],
                'DoctorId'     => !empty($item['doctor_id']) ? (int) $item['doctor_id'] : null,
                'UnitPrice'    => $item['unit_price'] ?? 0,
                'Quantity'     => $item['quantity'] ?? 1,
                'Amount'       => $item['amount'] ?? 0,
                'Remarks'      => $item['remarks'] ?? null,
                'created_at'   => now(),
                'updated_at'   => now(),
            ];
        }

        if (!empty($itemsToInsert)) {
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

    private function resolveChamberFeeConfigId(): ?int
    {
        $config = ChamberFeeConfig::where('status', 1)->first();
        return $config ? (int) $config->id : null;
    }
}
