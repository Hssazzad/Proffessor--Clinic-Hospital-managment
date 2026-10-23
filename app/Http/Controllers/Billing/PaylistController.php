<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Support\PaymentSourceFilter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaylistController extends Controller
{
    // ============================================================
    // GET /Billing/Paylist
    // ============================================================
    public function index()
    {
        return view('billing.paylist.index');
    }

    // ============================================================
    // AJAX: Search patients + invoices (Bill No)
    // ============================================================
    public function search(Request $request)
    {
        $term = trim((string) $request->input('q', ''));
        if ($term === '') {
            return response()->json([]);
        }

        $source = PaymentSourceFilter::normalize($request->input('payment_source'));
        $like = '%' . $term . '%';
        $groups = [];

        $patientsQuery = DB::table('patients')
            ->select('id', 'patientname', 'patientcode', 'mobile_no')
            ->where(function ($q) use ($like) {
                $q->where('patientname', 'like', $like)
                  ->orWhere('patientcode', 'like', $like)
                  ->orWhere('mobile_no', 'like', $like);
            });

        if ($source !== PaymentSourceFilter::SOURCE_ALL) {
            $patientsQuery->whereExists(function ($sub) use ($source) {
                $sub->select(DB::raw(1))
                    ->from('investigation_payments as ip')
                    ->whereColumn('ip.PatientId', 'patients.id');
                PaymentSourceFilter::applyToQuery($sub, $source, 'ip.payment_source');
            });
        }

        $patients = $patientsQuery
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        if ($patients->isNotEmpty()) {
            $groups[] = [
                'text'     => 'Patients',
                'children' => $patients->map(fn ($p) => [
                    'id'         => 'p:' . $p->id,
                    'text'       => trim($p->patientname . ' (' . $p->patientcode . ')'),
                    'type'       => 'patient',
                    'patient_id' => $p->id,
                ])->values(),
            ];
        }

        $invoicesQuery = DB::table('investigation_payments as p')
            ->select('p.ID', 'p.BillNo', 'p.PatientName', 'p.PatientCode')
            ->where(function ($q) use ($like) {
                $q->where('p.BillNo', 'like', $like)
                  ->orWhere('p.PatientName', 'like', $like)
                  ->orWhere('p.PatientCode', 'like', $like)
                  ->orWhere('p.MobileNo', 'like', $like);
            });

        PaymentSourceFilter::applyToQuery($invoicesQuery, $source, 'p.payment_source');

        $invoices = $invoicesQuery
            ->orderByDesc('p.ID')
            ->limit(15)
            ->get();

        if ($invoices->isNotEmpty()) {
            $groups[] = [
                'text'     => 'Invoices',
                'children' => $invoices->map(fn ($inv) => [
                    'id'      => 'i:' . $inv->ID,
                    'text'    => $inv->BillNo . ' - ' . $inv->PatientName,
                    'type'    => 'invoice',
                    'bill_no' => $inv->BillNo,
                ])->values(),
            ];
        }

        return response()->json($groups);
    }

    // ============================================================
    // AJAX: Payment List (Date grouped, paginated)
    // POST /Billing/Paylist/list
    // ============================================================
    public function list(Request $request)
    {
        $perPage    = (int) ($request->input('per_page', 20));
        $q          = trim((string) $request->input('q', ''));
        $patientId  = $request->input('patient_id');
        $dateMode   = $request->input('date_mode', 'all'); // default: 'all' (no date filter)
        $dateColumn = 't.transaction_date';
        $singleDate = $request->input('single_date', now()->toDateString());
        $dateFrom   = $request->input('date_from', now()->toDateString());
        $dateTo     = $request->input('date_to', now()->toDateString());
        $source     = PaymentSourceFilter::normalize($request->input('payment_source'));

        $transactionTotals = DB::table('investigation_payment_transactions as t')
            ->select(
                't.invoice_id',
                DB::raw("DATE(t.transaction_date) as transaction_date"),
                DB::raw("GROUP_CONCAT(t.id) as TransactionIds"),
                DB::raw("SUM(t.amount) as TransactionAmount"),
                DB::raw("SUM(CASE WHEN t.type = 'cash' THEN t.amount ELSE 0 END) as PaidAmount"),
                DB::raw("SUM(CASE WHEN t.type = 'discount' THEN t.amount ELSE 0 END) as DiscountAmount")
            );

        // -- Date Filter ------------------------------------------
        // 'all' = no date filter (show all time)
        if ($dateMode === 'range') {
            $transactionTotals->whereDate($dateColumn, '>=', $dateFrom)
                ->whereDate($dateColumn, '<=', $dateTo);
        } elseif ($dateMode === 'single') {
            $transactionTotals->whereDate($dateColumn, $singleDate);
        }

        $transactionTotals->groupBy('t.invoice_id', DB::raw('DATE(t.transaction_date)'));

        $query = DB::query()
            ->fromSub($transactionTotals, 't')
            ->join('investigation_payments as p', 'p.ID', '=', 't.invoice_id')
            ->select(
                'p.ID',
                'p.BillNo',
                'p.PatientId',
                'p.PatientCode',
                'p.PatientName',
                'p.MobileNo',
                'p.TotalBill as InvoiceTotalBill',
                'p.DueAmount',
                'p.PaidAmount as InvoicePaidAmount',
                'p.payment_source',
                't.transaction_date as PaymentDate',
                't.TransactionIds',
                't.TransactionAmount',
                't.PaidAmount',
                't.DiscountAmount',
                't.transaction_date as GroupDate'
            );

        PaymentSourceFilter::applyToQuery($query, $source, 'p.payment_source');

        if ($patientId) {
            $query->where('p.PatientId', $patientId);
        }

        // -- Text Search ------------------------------------------
        if ($q !== '') {
            $like = '%' . $q . '%';
            $query->where(function ($q2) use ($like) {
                     $q2->where('p.PatientName', 'like', $like)
                         ->orWhere('p.PatientCode', 'like', $like)
                         ->orWhere('p.MobileNo',    'like', $like)
                         ->orWhere('p.BillNo',      'like', $like);
            });
        }

        $query->orderByDesc('GroupDate')
            ->orderByDesc('p.ID');

        $paginated = $query->paginate($perPage);

        // -- Group by Date ----------------------------------------
        $grouped = [];
        foreach ($paginated->items() as $row) {
            $row->GroupKey = $row->ID . ':' . $row->GroupDate;
            $grouped[$row->GroupDate][] = $row;
        }

        return response()->json([
            'data'    => $grouped,
            'flat'    => $paginated->items(),
            'meta'    => [
                'total'        => $paginated->total(),
                'per_page'     => $paginated->perPage(),
                'current_page' => $paginated->currentPage(),
                'last_page'    => $paginated->lastPage(),
                'from'         => $paginated->firstItem() ?? 0,
                'to'           => $paginated->lastItem()  ?? 0,
            ],
        ]);
    }

    // ============================================================
    // AJAX: Summary Strip
    // POST /Billing/Paylist/summary
    // ============================================================
    public function summary(Request $request)
    {
        $patientId  = $request->input('patient_id');
        $dateMode   = $request->input('date_mode', 'all'); // default: 'all'
        $dateColumn = 't.transaction_date';
        $singleDate = $request->input('single_date', now()->toDateString());
        $dateFrom   = $request->input('date_from', now()->toDateString());
        $dateTo     = $request->input('date_to', now()->toDateString());
        $source     = PaymentSourceFilter::normalize($request->input('payment_source'));

        $q         = trim((string) $request->input('q', ''));
        $invoiceQuery = DB::table('investigation_payments as p')
            ->whereExists(function ($subquery) use ($dateMode, $dateFrom, $dateTo, $singleDate) {
                $subquery->selectRaw('1')
                    ->from('investigation_payment_transactions as t')
                    ->whereColumn('t.invoice_id', 'p.ID')
                    ->when($dateMode === 'range', fn ($q) => $q->whereDate('t.transaction_date', '>=', $dateFrom)->whereDate('t.transaction_date', '<=', $dateTo))
                    ->when($dateMode === 'single', fn ($q) => $q->whereDate('t.transaction_date', $singleDate));
            });

        $transactionQuery = DB::table('investigation_payment_transactions as t')
            ->join('investigation_payments as p', 'p.ID', '=', 't.invoice_id');

        PaymentSourceFilter::applyToQuery($invoiceQuery, $source, 'p.payment_source');
        PaymentSourceFilter::applyToQuery($transactionQuery, $source, 'p.payment_source');

        // -- Date Filter ------------------------------------------
        if ($dateMode === 'range') {
            $transactionQuery->whereDate($dateColumn, '>=', $dateFrom)
                ->whereDate($dateColumn, '<=', $dateTo);
        } elseif ($dateMode === 'single') {
            $transactionQuery->whereDate($dateColumn, $singleDate);
        }
        // 'all' => no date filter

        // -- Patient Filter ---------------------------------------
        if ($patientId) {
            $invoiceQuery->where('p.PatientId', $patientId);
            $transactionQuery->where('p.PatientId', $patientId);
        }

        if ($q !== '') {
            $like = '%' . $q . '%';
            $filter = function ($q2) use ($like) {
                $q2->where('p.PatientName', 'like', $like)
                   ->orWhere('p.PatientCode', 'like', $like)
                   ->orWhere('p.MobileNo',    'like', $like)
                   ->orWhere('p.BillNo',      'like', $like);
            };
            $invoiceQuery->where($filter);
            $transactionQuery->where(function ($q2) use ($filter) {
                $filter($q2);
            });
        }

        $invoiceSummary = (clone $invoiceQuery)->selectRaw('
            COUNT(*) as total_invoices,
            COUNT(DISTINCT p.PatientId) as total_patients,
            SUM(p.TotalBill) as total_bill,
            SUM(p.DueAmount) as total_due
        ')->first();
        $transactionSummary = (clone $transactionQuery)->selectRaw("SUM(CASE WHEN t.type = 'cash' THEN t.amount ELSE 0 END) as total_paid")
            ->selectRaw("SUM(CASE WHEN t.type = 'discount' THEN t.amount ELSE 0 END) as total_discount")
            ->first();

        return response()->json([
            'total_invoices'  => (int)   ($invoiceSummary->total_invoices  ?? 0),
            'total_patients'  => (int)   ($invoiceSummary->total_patients  ?? 0),
            'total_bill'      => (float) ($invoiceSummary->total_bill      ?? 0),
            'total_paid'      => (float) ($transactionSummary->total_paid      ?? 0),
            'total_due'       => (float) ($invoiceSummary->total_due       ?? 0),
            'total_discount'  => (float) ($transactionSummary->total_discount  ?? 0),
        ]);
    }

    // ============================================================
    // AJAX: Delete an invoice (double entry / wrong entry cleanup)
    // POST /Billing/Paylist/ajax/delete
    // ============================================================
    public function destroy(Request $request)
    {
        $id = (int) $request->input('id');
        if ($id <= 0) {
            return response()->json(['success' => false, 'message' => 'Invalid invoice id.'], 422);
        }

        $payment = DB::table('investigation_payments')->where('ID', $id)->first();
        if (! $payment) {
            return response()->json(['success' => false, 'message' => 'Invoice not found.'], 404);
        }

        DB::transaction(function () use ($id) {
            DB::table('investigation_payment_items')->where('PaymentId', $id)->delete();
            DB::table('investigation_payment_transactions')->where('invoice_id', $id)->delete();
            DB::table('investigation_payments')->where('ID', $id)->delete();
        });

        return response()->json([
            'success' => true,
            'message' => 'Invoice ' . ($payment->BillNo ?? '#' . $id) . ' deleted successfully.',
        ]);
    }
}
