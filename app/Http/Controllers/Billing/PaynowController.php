<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use App\Services\Billing\InvestigationPaymentTransactionService;
use App\Services\Billing\InvestigationItemAllocationService;
use App\Support\PaymentSourceFilter;

class PaynowController extends Controller
{
    public function __construct(
        private InvestigationPaymentTransactionService $transactions,
        private InvestigationItemAllocationService $itemAllocations,
        private \App\Services\Accounting\AccountingIntegrationService $accounting,
        private \App\Services\Notification\BillingNotificationService $billingNotificationService
    )
    {
    }
    // ─── Constants ────────────────────────────────────────────────
    private const PER_PAGE_DEFAULT  = 20;
    private const PER_PAGE_MAX      = 200;
    private const SEARCH_LIMIT      = 20;
    private const DUE_INVOICES_MAX  = 100;
    private const SCHEMA_CACHE_TTL  = 3600;
    private const SCHEMA_CACHE_KEY  = 'schema_cols_investigation_payments';

    // ─────────────────────────────────────────────────────────────
    // index
    // ─────────────────────────────────────────────────────────────
    public function index()
    {
        $references = DB::table('patient_ref')
            ->where('active', 1)
            ->orderBy('Name')
            ->get(['ID', 'Name', 'ref_type']);

        // Collected By - OfficeEmployee যারা active
        $collectors = DB::table('patient_ref')
            ->where('ref_type', 'OfficeEmployee')
            ->where('active', 1)
            ->orderBy('Name')
            ->get(['ID', 'Name']);

        return view('billing.paynow.index', compact('references', 'collectors'));
    }

    // ─────────────────────────────────────────────────────────────
    // GET /Billing/Paynow/ajax/list
    // ─────────────────────────────────────────────────────────────
    public function list(Request $request)
    {
        $q       = trim((string) $request->input('q', ''));
        $showAll = $request->boolean('show_all', false);
        $source  = PaymentSourceFilter::normalize($request->input('payment_source'));
        $perPage = min(
            max(1, (int) $request->input('per_page', self::PER_PAGE_DEFAULT)),
            self::PER_PAGE_MAX
        );
        $page   = max(1, (int) $request->input('page', 1));
        $offset = ($page - 1) * $perPage;

        $query = DB::table('investigation_payments as inv')
            ->select($this->invoiceSelectColumns('inv'));

        PaymentSourceFilter::applyToQuery($query, $source, 'inv.payment_source');

        if (! $showAll) {
            $query->where('inv.DueAmount', '>', 0);
        }

        if ($q !== '') {
            $like = '%' . $q . '%';
            $query->where(function ($sub) use ($like) {
                $sub->where('inv.BillNo',       'like', $like)
                    ->orWhere('inv.PatientName', 'like', $like)
                    ->orWhere('inv.PatientCode', 'like', $like)
                    ->orWhere('inv.MobileNo',    'like', $like);
            });
        }

        $total = (clone $query)->count();

        $rows = $query
            ->orderByDesc('inv.ID')
            ->offset($offset)
            ->limit($perPage)
            ->get()
            ->map(fn($row) => $this->appendNetBill($row));

        return response()->json([
            'success' => true,
            'data'    => $rows,
            'meta'    => [
                'total'        => $total,
                'per_page'     => $perPage,
                'current_page' => $page,
                'last_page'    => (int) ceil($total / max($perPage, 1)),
                'from'         => $total ? ($offset + 1) : 0,
                'to'           => min($offset + $perPage, $total),
            ],
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // GET /Billing/Paynow/ajax/search-patient
    // ─────────────────────────────────────────────────────────────
    public function searchPatient(Request $request)
    {
        $q      = trim((string) $request->input('q', ''));
        $source = PaymentSourceFilter::normalize($request->input('payment_source'));

        // শুধুমাত্র যেসব patient-এর Due আছে তারাই দেখানো হবে
        $dueSub = DB::table('investigation_payments as inv')
            ->where('inv.DueAmount', '>', 0)
            ->whereRaw('(inv.PatientCode = p.patientcode OR inv.PatientId = p.id)')
            ->selectRaw('SUM(inv.DueAmount)');
        PaymentSourceFilter::applyToQuery($dueSub, $source, 'inv.payment_source');

        $query = DB::table('patients as p')
            ->select('p.id', 'p.patientname', 'p.patientcode', 'p.age', 'p.gender', 'p.mobile_no')
            ->selectSub($dueSub, 'due_total')
            ->whereExists(function ($sub) use ($source) {
                $sub->select(DB::raw(1))
                    ->from('investigation_payments as inv')
                    ->where('inv.DueAmount', '>', 0)
                    ->where(function ($w) {
                        $w->whereColumn('inv.PatientCode', '=', 'p.patientcode')
                            ->orWhereColumn('inv.PatientId', '=', 'p.id');
                    });
                PaymentSourceFilter::applyToQuery($sub, $source, 'inv.payment_source');
            });

        if ($q !== '') {
            $like = '%' . $q . '%';
            $query->where(function ($sub) use ($like) {
                $sub->where('p.patientname',  'like', $like)
                    ->orWhere('p.patientcode', 'like', $like)
                    ->orWhere('p.mobile_no',   'like', $like);
            });
        }

        $rows = $query->orderByDesc('p.id')->limit(self::SEARCH_LIMIT)->get();

        return response()->json([
            'success' => true,
            'data'    => $rows,
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // GET /Billing/Paynow/ajax/due-invoices/{patientId}
    // ─────────────────────────────────────────────────────────────
    public function getDueInvoices(Request $request, $patientId)
    {
        $q           = trim((string) $request->input('q', ''));
        $patientCode = trim((string) $request->input('patient_code', ''));
        $source      = PaymentSourceFilter::normalize($request->input('payment_source'));
        $perPage     = min(
            max(1, (int) $request->input('per_page', self::DUE_INVOICES_MAX)),
            self::PER_PAGE_MAX
        );

        $query = DB::table('investigation_payments as inv')
            ->select($this->invoiceSelectColumns('inv'))
            ->where('inv.DueAmount', '>', 0);

        PaymentSourceFilter::applyToQuery($query, $source, 'inv.payment_source');

        if ($patientCode !== '') {
            $query->where('inv.PatientCode', $patientCode);
        } else {
            $patientIdColumn = $this->resolveExistingColumn(
                'investigation_payments',
                ['PatientId', 'PatientID', 'patient_id']
            );

            if ($patientId !== '__ALL__' && $patientIdColumn) {
                $query->where('inv.' . $patientIdColumn, $patientId);
            }
        }

        if ($q !== '') {
            $like = '%' . $q . '%';
            $query->where(function ($sub) use ($like) {
                $sub->where('inv.BillNo',       'like', $like)
                    ->orWhere('inv.PatientName', 'like', $like)
                    ->orWhere('inv.PatientCode', 'like', $like)
                    ->orWhere('inv.MobileNo',    'like', $like);
            });
        }

        $rows = $query
            ->orderBy('inv.ID')   // oldest first — bulk distribution-এর জন্য consistent order
            ->limit($perPage)
            ->get()
            ->map(fn($row) => $this->appendNetBill($row));

        return response()->json([
            'success' => true,
            'data'    => $rows,
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // POST /Billing/Paynow/store   (single invoice payment)
    // ─────────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'invoice_id'     => 'required|integer|min:1',
            'paying_amount'  => 'required|numeric|min:0.01',
            'payment_date'   => 'required|date',
            'payment_method' => 'required|in:cash,mobile_banking,card,bank_transfer,cheque',
            'collected_by'   => 'nullable|string|max:100',
            'discount_type'  => 'nullable|in:percent,flat',
            'discount_value' => 'nullable|numeric|min:0',
            'reference_id'   => 'nullable|integer|min:1',
            'reference_new_name' => 'nullable|string|max:100',
            'note'           => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ], 422);
        }

        $paymentColumns = Cache::remember(
            self::SCHEMA_CACHE_KEY,
            self::SCHEMA_CACHE_TTL,
            fn() => Schema::getColumnListing('investigation_payments')
        );

        DB::beginTransaction();

        try {
            $invoice = DB::table('investigation_payments')
                ->where('ID', $request->invoice_id)
                ->lockForUpdate()
                ->first();

            if (! $invoice) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Invoice not found.'], 404);
            }

            $paying = (float) $request->paying_amount;
            $due    = (float) ($invoice->DueAmount ?? 0);

            if ($due <= 0) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'This invoice has already been fully paid.'], 422);
            }

            if ($paying <= 0 || $paying > $due) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Invalid amount. Due is ' . number_format($due, 0)], 422);
            }

            // ─── Handle Discount ──────────────────────────────────
            $discountValue = (float) ($request->discount_value ?? 0);
            $discountType  = $request->discount_type ?? 'percent';
            $discountAmount = 0;
            $referenceId = null;
            $requestedAmount = 0;
            $discountCapped = false;

            if ($discountValue > 0) {
                // Check reference
                if ((int) $request->reference_id < 1 && trim((string) $request->reference_new_name) === '') {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Reference By required — member select করুন বা নাম লিখুন।'
                    ], 422);
                }

                // Calculate discount
                $total = (float) ($invoice->TotalBill ?? 0);
                $requestedAmount = $discountType === 'flat'
                    ? $discountValue
                    : round($total * $discountValue / 100, 2);

                if ($requestedAmount > $due) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'আপনি ' . number_format($requestedAmount, 2) . ' টাকা ডিসকাউন্ট দিতে চাচ্ছেন, কিন্তু Due আছে মাত্র ' . number_format($due, 2) . ' টাকা।',
                        'warning' => true,
                        'requested' => round($requestedAmount, 2),
                        'max_allowed' => round($due, 2),
                    ], 422);
                }

                // Pay + Discount একসাথে Due-এর বেশি হতে পারবে না —
                // Discount-কে (Due − Paying) পর্যন্ত cap করা হয়।
                $maxDiscount   = max(0.0, $due - $paying);
                $discountAmount = min($requestedAmount, $maxDiscount);
                $discountCapped = $requestedAmount > $maxDiscount;

                // Get reference ID
                $referenceId = (int) $request->reference_id;
                if ($referenceId < 1 && !empty($request->reference_new_name)) {
                    $referenceId = DB::table('patient_ref')->insertGetId([
                        'Name'       => trim((string) $request->reference_new_name),
                        'ref_type'   => 'Other/Guest',
                        'active'     => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            // ─── Update Invoice ──────────────────────────────────
            $oldPaid   = (float) ($invoice->PaidAmount ?? 0);
            $oldDue    = $due;
            $oldDiscount = (float) ($invoice->Discount ?? 0);

            $newPaid   = $oldPaid + $paying;
            $newDue    = $oldDue - $paying - $discountAmount;
            $newDue    = max(0.0, $newDue);
            $newDiscount = $oldDiscount + $discountAmount;
            $newStatus = $newDue <= 0 ? 'paid' : 'partial';

            $collectedBy = $request->input('collected_by')
                ?: ($invoice->CollectedBy ?? $invoice->collected_by ?? null);

            $update = [];
            $this->putIfColumnExists($update, $paymentColumns, 'PaidAmount',         $newPaid);
            $this->putIfColumnExists($update, $paymentColumns, 'paid_amount',         $newPaid);
            $this->putIfColumnExists($update, $paymentColumns, 'DueAmount',           $newDue);
            $this->putIfColumnExists($update, $paymentColumns, 'due_amount',          $newDue);
            $this->putIfColumnExists($update, $paymentColumns, 'Discount',            $newDiscount);
            $this->putIfColumnExists($update, $paymentColumns, 'discount',            $newDiscount);
            $this->putIfColumnExists($update, $paymentColumns, 'Status',              $newStatus);
            $this->putIfColumnExists($update, $paymentColumns, 'status',              $newStatus);
            $this->putIfColumnExists($update, $paymentColumns, 'PaymentMethod',       $request->payment_method);
            $this->putIfColumnExists($update, $paymentColumns, 'payment_method',      $request->payment_method);
            $this->putIfColumnExists($update, $paymentColumns, 'CollectedBy',         $collectedBy);
            $this->putIfColumnExists($update, $paymentColumns, 'collected_by',        $collectedBy);
            $this->putIfColumnExists($update, $paymentColumns, 'PaymentDate',         $request->payment_date);
            $this->putIfColumnExists($update, $paymentColumns, 'payment_date',        $request->payment_date);
            $this->putIfColumnExists($update, $paymentColumns, 'fin_paid_amount',     $newPaid);
            $this->putIfColumnExists($update, $paymentColumns, 'fin_due_amount',      $newDue);
            $this->putIfColumnExists($update, $paymentColumns, 'payment_status',      $newStatus);
            $this->putIfColumnExists($update, $paymentColumns, 'fin_last_payment_at', $request->payment_date);
            $this->putIfColumnExists($update, $paymentColumns, 'updated_at',          now());

            DB::table('investigation_payments')->where('ID', $request->invoice_id)->update($update);
            $discountItems = $this->itemAllocations->apply((int) $invoice->ID, $discountAmount, 'discount');
            $cashItems = $this->itemAllocations->apply((int) $invoice->ID, $paying, 'cash');
            $this->transactions->recordItems((int) $invoice->ID, 'cash', $cashItems, $request->payment_date, $request->payment_method, $collectedBy, 'paynow');
            $this->transactions->recordItems((int) $invoice->ID, 'discount', $discountItems, $request->payment_date, null, null, 'paynow');

            // ─── Insert Discount History ─────────────────────────
            if ($discountAmount > 0 && $referenceId) {
                DB::table('invoice_discount_adjustments')->insert([
                    'invoice_id'        => (int) $request->invoice_id,
                    'old_discount'      => round($oldDiscount, 2),
                    'new_discount'      => round($newDiscount, 2),
                    'adjustment_amount' => round($discountAmount, 2),
                    'old_due'           => round($oldDue, 2),
                    'new_due'           => round($newDue, 2),
                    'type'              => 'adjustment',
                    'reference_id'      => $referenceId,
                    'approved_by'       => null,
                    'note'              => $request->input('note') ?: null,
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ]);
            }

            //   $lastTx = DB::table('investigation_payment_transactions')->where('invoice_id', $invoice->ID)->orderByDesc('id')->first();
            //   $txId = $lastTx ? $lastTx->id : time();
            //   $this->accounting->postInvestigationDuePayment($invoice->BillNo, $txId, (float) $paying, $request->payment_date);  // Auto posting to accounting (OFF - same as normal invoice posting)

            DB::commit();

            DuelistFinalController::clearSummaryCache();

            // Notify after commit so a notification failure never rolls
            // back a successfully persisted payment.
            try {
                $refName = trim((string) $request->reference_new_name);
                if (empty($refName) && !empty($referenceId)) {
                    $refName = (string) (DB::table('patient_ref')->where('ID', $referenceId)->value('Name') ?? '');
                }
                if (!empty($referenceId) || $refName !== '') {
                    $this->billingNotificationService->paymentWithReference(
                        $invoice,
                        $refName !== '' ? $refName : null,
                        !empty($referenceId) ? (int) $referenceId : null,
                        (float) $paying
                    );
                }
            } catch (\Throwable $e) {
                Log::error('Billing notification creation failed', [
                    'invoice_id' => $request->invoice_id,
                    'error'      => $e->getMessage(),
                ]);
            }

            return response()->json([
                'success'    => true,
                'invoice_id' => (int) $request->invoice_id,
                'new_status' => $newStatus,
                'due_left'   => $newDue,
                'paid'       => $newPaid,
                'discount_applied' => $discountAmount,
                'discount_requested' => round($requestedAmount, 2),
                'discount_capped'    => $discountCapped,
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('PaynowController@store failed: ' . $e->getMessage(), [
                'invoice_id' => $request->invoice_id,
                'amount'     => $request->paying_amount,
            ]);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────
    // POST /Billing/Paynow/store-bulk
    //
    // Bulk payment — একটি transaction-এ সব due invoice update।
    // Amount oldest invoice থেকে distribute হয়।
    // Response-এ updated invoice list থাকে (combined receipt-এর জন্য)।
    // ─────────────────────────────────────────────────────────────
    public function storeBulk(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'patient_code'   => 'required|string|max:50',
            'paying_amount'  => 'required|numeric|min:0.01',
            'payment_date'   => 'required|date',
            'payment_method' => 'required|in:cash,mobile_banking,card,bank_transfer,cheque',
            'collected_by'   => 'nullable|string|max:100',
            'invoice_ids'    => 'required|array|min:1',
            'invoice_ids.*'  => 'integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ], 422);
        }

        $paymentColumns = Cache::remember(
            self::SCHEMA_CACHE_KEY,
            self::SCHEMA_CACHE_TTL,
            fn() => Schema::getColumnListing('investigation_payments')
        );

        DB::beginTransaction();

        try {
            // Oldest-first order — row lock সহ সব due invoice নিয়ে আসছি
            $invoices = DB::table('investigation_payments')
                ->whereIn('ID', $request->invoice_ids)
                ->where('PatientCode', $request->patient_code)
                ->where('DueAmount', '>', 0)
                ->orderBy('ID')      // oldest first
                ->lockForUpdate()
                ->get();

            if ($invoices->isEmpty()) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'No due invoice found.'], 422);
            }

            $totalDue = $invoices->sum(fn($inv) => (float) ($inv->DueAmount ?? 0));
            $paying   = (float) $request->paying_amount;

            if ($paying <= 0) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Invalid amount.'], 422);
            }

            if ($paying > $totalDue) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Amount exceeds total due of ৳ ' . number_format($totalDue, 0),
                ], 422);
            }

            $remaining   = $paying;
            $collectedBy = $request->input('collected_by') ?: null;
            $updated     = [];   // receipt-এর জন্য return করব

            foreach ($invoices as $invoice) {
                if ($remaining <= 0) break;

                $due         = (float) ($invoice->DueAmount ?? 0);
                $allocate    = min($remaining, $due);
                $remaining  -= $allocate;

                $newDue    = max(0.0, $due - $allocate);
                $newPaid   = ((float) ($invoice->PaidAmount ?? 0)) + $allocate;
                $newStatus = $newDue <= 0 ? 'paid' : 'partial';
                $cb        = $collectedBy ?? ($invoice->CollectedBy ?? $invoice->collected_by ?? null);

                $row = [];
                $this->putIfColumnExists($row, $paymentColumns, 'PaidAmount',         $newPaid);
                $this->putIfColumnExists($row, $paymentColumns, 'paid_amount',         $newPaid);
                $this->putIfColumnExists($row, $paymentColumns, 'DueAmount',           $newDue);
                $this->putIfColumnExists($row, $paymentColumns, 'due_amount',          $newDue);
                $this->putIfColumnExists($row, $paymentColumns, 'Status',              $newStatus);
                $this->putIfColumnExists($row, $paymentColumns, 'status',              $newStatus);
                $this->putIfColumnExists($row, $paymentColumns, 'PaymentMethod',       $request->payment_method);
                $this->putIfColumnExists($row, $paymentColumns, 'payment_method',      $request->payment_method);
                $this->putIfColumnExists($row, $paymentColumns, 'CollectedBy',         $cb);
                $this->putIfColumnExists($row, $paymentColumns, 'collected_by',        $cb);
                $this->putIfColumnExists($row, $paymentColumns, 'PaymentDate',         $request->payment_date);
                $this->putIfColumnExists($row, $paymentColumns, 'payment_date',        $request->payment_date);
                $this->putIfColumnExists($row, $paymentColumns, 'fin_paid_amount',     $newPaid);
                $this->putIfColumnExists($row, $paymentColumns, 'fin_due_amount',      $newDue);
                $this->putIfColumnExists($row, $paymentColumns, 'payment_status',      $newStatus);
                $this->putIfColumnExists($row, $paymentColumns, 'fin_last_payment_at', $request->payment_date);
                $this->putIfColumnExists($row, $paymentColumns, 'updated_at',          now());

                DB::table('investigation_payments')->where('ID', $invoice->ID)->update($row);
                $cashItems = $this->itemAllocations->apply((int) $invoice->ID, $allocate, 'cash');
                $this->transactions->recordItems((int) $invoice->ID, 'cash', $cashItems, $request->payment_date, $request->payment_method, $cb, 'paynow_bulk');

                //   $lastTx = DB::table('investigation_payment_transactions')->where('invoice_id', $invoice->ID)->orderByDesc('id')->first();
                //   $txId = $lastTx ? $lastTx->id : time();
                //   $this->accounting->postInvestigationDuePayment($invoice->BillNo, $txId, (float) $allocate, $request->payment_date);  // Auto posting to accounting (OFF - same as normal invoice posting)

                // Receipt-এর জন্য snapshot রাখা হচ্ছে
                $updated[] = [
                    'id'         => (int) $invoice->ID,
                    'bill_no'    => $invoice->BillNo    ?? '-',
                    'patient'    => $invoice->PatientName ?? '-',
                    'total_bill' => (float) ($invoice->TotalBill ?? 0),
                    'discount'   => (float) ($invoice->Discount  ?? 0),
                    'allocated'  => round($allocate, 2),
                    'new_paid'   => round($newPaid,   2),
                    'new_due'    => round($newDue,    2),
                    'status'     => $newStatus,
                ];
            }

            DB::commit();

            DuelistFinalController::clearSummaryCache();

            return response()->json([
                'success'       => true,
                'total_paid'    => round($paying - $remaining, 2),
                'invoices'      => $updated,
                'invoice_count' => count($updated),
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('PaynowController@storeBulk failed: ' . $e->getMessage(), [
                'patient_code' => $request->patient_code,
                'amount'       => $request->paying_amount,
            ]);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────
    // GET /Billing/Paynow/references
    // ─────────────────────────────────────────────────────────────
    public function references()
    {
        $data = DB::table('patient_ref')
            ->where('active', 1)
            ->orderBy('Name')
            ->get(['ID', 'Name', 'ref_type']);

        return response()->json(['data' => $data]);
    }

    // ─────────────────────────────────────────────────────────────
    // POST /Billing/Paynow/adjust-discount
    //
    // Due-তে সরাসরি discount প্রয়োগ — invoice-র Discount column-এ
    // jog hobe, DueAmount kombe, and invoice_discount_adjustments
    // history-তে old/new snapshot সহ একটি row insert হবে (atomic)।
    // discount_type: percent → original TotalBill-এর উপর, flat → সরাসরি;
    // উভয় ক্ষেত্রেই current DueAmount-এর বেশি হতে পারবে না (cap)।
    // ─────────────────────────────────────────────────────────────
    public function adjustDiscount(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'invoice_id'        => 'required|integer|min:1',
            'discount_type'     => 'required|in:percent,flat',
            'discount_value'    => 'required|numeric|min:0.01',
            'reference_id'      => 'nullable|integer|min:1',
            'reference_new_name'=> 'nullable|string|max:100',
            'note'              => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ], 422);
        }

        if ((int) $request->reference_id < 1 && trim((string) $request->reference_new_name) === '') {
            return response()->json([
                'success' => false,
                'message' => 'Reference By required — member select করুন বা নাম লিখুন।',
            ], 422);
        }

        DB::beginTransaction();

        try {
            $invoice = DB::table('investigation_payments')
                ->where('ID', $request->invoice_id)
                ->lockForUpdate()
                ->first();

            if (! $invoice) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Invoice not found.'], 404);
            }

            $total = (float) ($invoice->TotalBill ?? 0);
            $due   = (float) ($invoice->DueAmount ?? 0);

            if ($due <= 0) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'This invoice has already been fully paid.'], 422);
            }

            $value = (float) $request->discount_value;

            // Calculate requested discount (before cap)
            $requestedAmount = $request->discount_type === 'flat'
                ? $value
                : round($total * $value / 100, 2);

            // Check if requested discount exceeds due
            if ($requestedAmount > $due) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'আপনি ' . number_format($requestedAmount, 2) . ' টাকা ডিসকাউন্ট দিতে চাচ্ছেন, কিন্তু Due আছে মাত্র ' . number_format($due, 2) . ' টাকা।',
                    'warning' => true,
                    'requested' => round($requestedAmount, 2),
                    'max_allowed' => round($due, 2),
                    'due' => round($due, 2),
                ], 422);
            }

            // % → original TotalBill-এর উপর (CreateInvoice-এর computeDiscount-এর মতো),
            // flat → সরাসরি টাকার অঙ্ক; দুটোই current DueAmount-এ cap।
            $amount = min($requestedAmount, $due);

            if ($amount <= 0) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Invalid discount amount.'], 422);
            }

            // reference_id: পোস্ট করা int সরাসরি ব্যবহার; "Others" typed নাম
            // হলে আগে patient_ref-এ (ref_type 'Other/Guest') insert → তার ID।
            $referenceId = (int) $request->reference_id;
            if ($referenceId < 1) {
                $referenceId = DB::table('patient_ref')->insertGetId([
                    'Name'       => trim((string) $request->reference_new_name),
                    'ref_type'   => 'Other/Guest',
                    'active'     => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $oldDiscount = (float) ($invoice->Discount ?? 0);
            $oldDue      = $due;

            $newDiscount = $oldDiscount + $amount;
            $newDue      = max(0.0, $total - $newDiscount - (float) ($invoice->PaidAmount ?? 0));
            $newStatus   = $newDue <= 0 ? 'paid' : 'partial';

            $paymentColumns = Cache::remember(
                self::SCHEMA_CACHE_KEY,
                self::SCHEMA_CACHE_TTL,
                fn() => Schema::getColumnListing('investigation_payments')
            );

            $update = [];
            $this->putIfColumnExists($update, $paymentColumns, 'Discount',   $newDiscount);
            $this->putIfColumnExists($update, $paymentColumns, 'discount',   $newDiscount);
            $this->putIfColumnExists($update, $paymentColumns, 'DueAmount',  $newDue);
            $this->putIfColumnExists($update, $paymentColumns, 'due_amount', $newDue);
            $this->putIfColumnExists($update, $paymentColumns, 'Status',     $newStatus);
            $this->putIfColumnExists($update, $paymentColumns, 'status',     $newStatus);
            $this->putIfColumnExists($update, $paymentColumns, 'updated_at', now());

            DB::table('investigation_payments')->where('ID', $request->invoice_id)->update($update);
            $discountItems = $this->itemAllocations->apply((int) $invoice->ID, $amount, 'discount');
            $this->transactions->recordItems((int) $invoice->ID, 'discount', $discountItems, now()->toDateString(), null, null, 'paynow_discount');

            DB::table('invoice_discount_adjustments')->insert([
                'invoice_id'        => (int) $request->invoice_id,
                'old_discount'      => round($oldDiscount, 2),
                'new_discount'      => round($newDiscount, 2),
                'adjustment_amount' => round($amount, 2),
                'old_due'           => round($oldDue, 2),
                'new_due'           => round($newDue, 2),
                'type'              => 'adjustment',
                'reference_id'      => $referenceId,
                'approved_by'       => null,
                'note'              => $request->input('note') ?: null,
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);

            DB::commit();

            DuelistFinalController::clearSummaryCache();

            return response()->json([
                'success'    => true,
                'invoice_id' => (int) $request->invoice_id,
                'amount'     => round($amount, 2),
                'requested'  => round($requestedAmount, 2),
                'new_due'    => round($newDue, 2),
                'new_status' => $newStatus,
                'message'    => $requestedAmount > $due
                    ? 'আপনি ' . number_format($requestedAmount, 2) . ' টাকা চেয়েছিলেন, কিন্তু Due ছিল ' . number_format($due, 2) . ' টাকা। ' . number_format($amount, 2) . ' টাকা প্রয়োগ করা হয়েছে (Due সম্পূর্ণ Clear)।'
                    : 'Discount applied successfully!',
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('PaynowController@adjustDiscount failed: ' . $e->getMessage(), [
                'invoice_id' => $request->invoice_id,
                'amount'     => $request->discount_value,
            ]);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────
    // GET /Billing/Paynow/discount-history/{invoiceId}
    // ─────────────────────────────────────────────────────────────
    public function discountHistory($invoiceId)
    {
        $rows = DB::table('invoice_discount_adjustments as a')
            ->leftJoin('patient_ref as r',  'r.ID',  '=', 'a.reference_id')
            ->leftJoin('patient_ref as ap', 'ap.ID', '=', 'a.approved_by')
            ->where('a.invoice_id', $invoiceId)
            ->orderByDesc('a.id')
            ->select(
                'a.adjustment_amount',
                'a.old_due',
                'a.new_due',
                'r.Name  as reference_name',
                'ap.Name as approved_by_name',
                'a.created_at',
                'a.note'
            )
            ->get();

        return response()->json(['data' => $rows]);
    }

    // ─────────────────────────────────────────────────────────────
    // show / print
    // ─────────────────────────────────────────────────────────────
    public function show(int $id)
    {
        $invoice = DB::table('investigation_payments')->where('ID', $id)->first();
        if (! $invoice) abort(404, 'Invoice not found.');
        $items = DB::table('investigation_payment_items')->where('PaymentId', $id)->get();
        return view('billing.payment_show', compact('invoice', 'items'));
    }

    public function print(int $id)
    {
        $payment = DB::table('investigation_payments')->where('ID', $id)->first();
        if (! $payment) abort(404, 'Payment not found.');
        $items = DB::table('investigation_payment_items')->where('PaymentId', $id)->get();
        return view('billing.invoice_print', compact('payment', 'items'));
    }

    // ─── Private helpers ──────────────────────────────────────────

    private function appendNetBill(object $row): object
    {
        $row->NetBill = ((float) ($row->TotalBill ?? 0)) - ((float) ($row->Discount ?? 0));
        return $row;
    }

    private function invoiceSelectColumns(string $alias = 'inv'): array
    {
        $p = $alias ? $alias . '.' : '';

        $columns = [
            $p . 'ID',
            $p . 'BillNo',
            $p . 'TotalBill',
            $p . 'Discount',
            $p . 'PaidAmount',
            $p . 'DueAmount',
            $p . 'Status',
            $p . 'PaymentDate',
            $p . 'PatientName',
            $p . 'PatientCode',
            $p . 'MobileNo',
        ];

        $columns[] = Schema::hasColumn('investigation_payments', 'InvoiceType')
            ? $p . 'InvoiceType'
            : DB::raw("'' as InvoiceType");

        $patientIdColumn = $this->resolveExistingColumn(
            'investigation_payments',
            ['PatientId', 'PatientID', 'patient_id']
        );

        $columns[] = PaymentSourceFilter::hasColumn()
            ? $p . 'payment_source'
            : DB::raw("NULL as payment_source");

        $columns[] = $patientIdColumn
            ? $p . $patientIdColumn . ' as PatientId'
            : DB::raw('NULL as PatientId');

        return $columns;
    }

    private function resolveExistingColumn(string $table, array $candidates): ?string
    {
        foreach ($candidates as $candidate) {
            if (Schema::hasColumn($table, $candidate)) return $candidate;
        }
        return null;
    }

    private function putIfColumnExists(array &$row, array $columns, string $column, $value): void
    {
        if (in_array($column, $columns, true)) {
            $row[$column] = $value;
        }
    }
}
