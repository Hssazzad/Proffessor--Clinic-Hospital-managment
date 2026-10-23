<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Support\PaymentSourceFilter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class DuelistFinalController extends Controller
{
    // Summary cache key — একটাই জায়গায় রাখা হয়েছে
    private const SUMMARY_CACHE_KEY = 'duelist_summary';
    private const SUMMARY_CACHE_TTL = 300;

    public function index()
    {
        return view('billing.duelist_final.index');
    }

    // -------------------------------------------------------
    // Cache clear — PaynowController থেকে call হয়
    // -------------------------------------------------------
    public static function clearSummaryCache(): void
    {
        Cache::forget(self::SUMMARY_CACHE_KEY);
        Cache::forget(self::SUMMARY_CACHE_KEY . '_all');
        Cache::forget(self::SUMMARY_CACHE_KEY . '_due');

        foreach (PaymentSourceFilter::SOURCES as $source) {
            Cache::forget(self::SUMMARY_CACHE_KEY . "_{$source}");
            Cache::forget(self::SUMMARY_CACHE_KEY . "_{$source}_all");
            Cache::forget(self::SUMMARY_CACHE_KEY . "_{$source}_due");
        }
    }

    // -------------------------------------------------------
    // Due List (Paginated)
    // -------------------------------------------------------
    public function list(Request $request)
    {
        $perPage = (int) ($request->input('per_page', 20));
        $q       = trim((string) $request->input('q', ''));
        $showAll = $request->boolean('show_all');
        $source  = PaymentSourceFilter::normalize($request->input('payment_source'));

        // Per-invoice rows (not grouped by patient) so each invoice
        // appears on its own and only invoices with due are shown by default.
        $query = DB::table('investigation_payments as ip')
            ->join('patients as p', 'p.id', '=', 'ip.PatientId')
            ->select(
                'ip.ID as ID',
                'ip.ID as InvoiceId',
                'p.id as PatientId',
                'p.patientname as PatientName',
                'p.patientcode as PatientCode',
                'p.mobile_no as MobileNo',
                'ip.BillNo as BillNo',
                'ip.PaymentDate as PaymentDate',
                'ip.created_at as created_at',
                'ip.payment_source',
                'ip.TotalBill as TotalBill',
                DB::raw('IFNULL(ip.Discount,0) as Discount'),
                DB::raw('IFNULL(ip.PaidAmount,0) as PaidAmount'),
                DB::raw('IFNULL(ip.DueAmount,0) as DueAmount'),
                DB::raw('(ip.TotalBill - IFNULL(ip.Discount,0)) as NetBill'),
                DB::raw('CASE
                    WHEN IFNULL(ip.DueAmount,0) <= 0 THEN "paid"
                    WHEN IFNULL(ip.PaidAmount,0) > 0 THEN "partial"
                    ELSE "due"
                 END as Status')
            );

        PaymentSourceFilter::applyToQuery($query, $source, 'ip.payment_source');

        // Only show invoices that still have due by default.
        // "All" must be explicitly selected to show paid/settled invoices too.
        if (!$showAll) {
            $query->where('ip.DueAmount', '>', 0);
        }

        if ($q !== '') {
            $like = "%{$q}%";
            $query->where(function ($qb) use ($like) {
                $qb->where('p.patientname',  'like', $like)
                   ->orWhere('p.patientcode', 'like', $like)
                   ->orWhere('p.mobile_no',   'like', $like)
                   ->orWhere('ip.BillNo',     'like', $like);
            });
        }

        $query->orderByDesc('ip.DueAmount');

        $paginated = $query->paginate($perPage);

        return response()->json([
            'success' => true,
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

    // -------------------------------------------------------
    // Summary Cards
    // -------------------------------------------------------
    public function summary(Request $request)
    {
        $showAll = $request->boolean('show_all');
        $source  = PaymentSourceFilter::normalize($request->input('payment_source'));

        $cacheKey = self::SUMMARY_CACHE_KEY . '_' . $source . ($showAll ? '_all' : '_due');

        $data = Cache::remember($cacheKey, self::SUMMARY_CACHE_TTL, function () use ($showAll, $source) {
            $query = DB::table('investigation_payments as ip')
                ->join('patients as p', 'p.id', '=', 'ip.PatientId')
                ->selectRaw('
                    COUNT(DISTINCT p.id)         AS total_patients,
                    COUNT(DISTINCT ip.ID)        AS total_invoices,
                    SUM(IFNULL(ip.DueAmount, 0)) AS total_due,
                    SUM(IFNULL(ip.TotalBill, 0))  AS total_bill,
                    SUM(IFNULL(ip.PaidAmount, 0)) AS total_paid
                ');

            PaymentSourceFilter::applyToQuery($query, $source, 'ip.payment_source');

            if (!$showAll) {
                $query->where('ip.DueAmount', '>', 0);
            }

            return $query->first();
        });

        return response()->json([
            'success'        => true,
            'total_patients' => (int)   ($data->total_patients ?? 0),
            'total_due'      => (float) ($data->total_due      ?? 0),
            'total_bill'     => (float) ($data->total_bill     ?? 0),
            'total_paid'     => (float) ($data->total_paid     ?? 0),
            'total_invoices' => (int)   ($data->total_invoices ?? 0),
        ]);
    }

    // -------------------------------------------------------
    // Patient Details Modal
    // -------------------------------------------------------
    public function patientDetails(Request $request, $patientId)
    {
        $patientId = (int) $patientId;
        $source    = PaymentSourceFilter::normalize($request->input('payment_source'));

        if ($patientId <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid patient ID',
            ], 422);
        }

        $patient = DB::table('patients')->where('id', $patientId)->first();

        if (!$patient) {
            return response()->json([
                'success' => false,
                'message' => 'Patient not found',
            ], 404);
        }

        $query = DB::table('investigation_payments')
            ->select(
                'ID',
                'BillNo',
                'TotalBill',
                'Discount',
                'PaidAmount',
                'DueAmount',
                'PaymentDate',
                'payment_source',
                'CollectedBy'
            )
            ->where('PatientId', $patientId);

        PaymentSourceFilter::applyToQuery($query, $source, 'payment_source');

        $rows = $query->orderByDesc('ID')->get();

        $bills = $rows->map(function ($r) {
            return [
                'ID'         => $r->ID,
                'BillNo'     => $r->BillNo,
                'TotalBill'  => (float) $r->TotalBill,
                'Discount'   => (float) ($r->Discount ?? 0),
                'NetBill'    => (float) $r->TotalBill - (float) ($r->Discount ?? 0),
                'PaidAmount' => (float) ($r->PaidAmount ?? 0),
                'DueAmount'  => (float) ($r->DueAmount  ?? 0),
            ];
        });

        $payments = $rows->filter(fn($r) => (float) $r->PaidAmount > 0)
            ->map(function ($r) {
                return [
                    'ID'          => $r->ID,
                    'BillNo'      => $r->BillNo,
                    'PaymentDate' => $r->PaymentDate,
                    'amount'      => (float) $r->PaidAmount,
                    'CollectedBy' => $r->CollectedBy,
                ];
            })
            ->values();

        return response()->json([
            'success'       => true,
            'patient'       => [
                'name' => $patient->patientname,
            ],
            'bills'         => $bills,
            'payments'      => $payments,
            'total_due'     => (float) $rows->sum('DueAmount'),
            'total_payment' => (float) $rows->sum('PaidAmount'),
        ]);
    }
}