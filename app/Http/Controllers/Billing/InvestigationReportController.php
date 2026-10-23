<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Repositories\Billing\Contracts\InvestigationReportRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvestigationReportController extends Controller
{
    public function __construct(
        protected InvestigationReportRepositoryInterface $repo
    ) {
    }

    public function index(Request $request)
    {
        $filters = $request->validate([
            'date_from' => 'nullable|date',
            'date_to'   => 'nullable|date|after_or_equal:date_from',
            'category'  => 'nullable|in:' . implode(',', $this->repo->getCategories()),
            'grouping'  => 'nullable|in:daily,monthly',
        ]);

        $filters['date_from'] ??= now()->startOfMonth()->toDateString();
        $filters['date_to']   ??= now()->toDateString();
        $filters['grouping']  ??= 'daily';
        $filters['category']  ??= null;

        $categoryTotals    = $this->repo->getCategoryTotals($filters);
        $serviceBreakdown  = $this->repo->getServiceBreakdown($filters['category'] ?? null, $filters);
        $patientCounts     = $this->repo->getPatientCounts($filters, $filters['grouping']);
        $categories        = $this->repo->getCategories();
        $patientBreakdown  = $this->repo->getPatientTestBreakdown($filters, $filters['category'] ?? null);

        $totalRow = $categoryTotals->last();

        $summary = [
            'revenue'   => (float) ($totalRow->revenue ?? 0),
            'discount'  => (float) ($totalRow->discount ?? 0),
            'gross'     => (float) ($totalRow->gross ?? 0),
            'tests'     => (int) ($totalRow->tests ?? 0),
            'patients'  => (int) ($totalRow->patients ?? 0),
            'invoices'  => (int) $patientCounts->sum('invoices'),
        ];

        return view('billing.investigation_report.index', compact(
            'filters',
            'categoryTotals',
            'serviceBreakdown',
            'patientCounts',
            'categories',
            'patientBreakdown',
            'summary'
        ));
    }

    public function transactionHistory(Request $request)
    {
        $filters = $request->validate([
            'date_from' => 'nullable|date',
            'date_to'   => 'nullable|date|after_or_equal:date_from',
            'type'      => 'nullable|in:cash,discount',
            'search'    => 'nullable|string|max:100',
            'due_only'  => 'nullable|boolean',
        ]);

        $filters['date_from'] ??= now()->startOfMonth()->toDateString();
        $filters['date_to'] ??= now()->toDateString();
        $filters['type'] ??= null;
        $filters['search'] ??= null;
        $filters['due_only'] = $request->boolean('due_only');

        $query = DB::table('investigation_payment_transactions as t')
            ->join('investigation_payments as p', 'p.ID', '=', 't.invoice_id')
            ->whereDate('t.transaction_date', '>=', $filters['date_from'])
            ->whereDate('t.transaction_date', '<=', $filters['date_to'])
            ->when($filters['type'], fn ($q, $type) => $q->where('t.type', $type))
            ->when($filters['due_only'], fn ($q) => $q->whereIn('t.source', ['paynow', 'paynow_bulk', 'paynow_discount', 'due_adjustment', 'due_adjustment_reversal']))
            ->when($filters['search'], function ($q, $search) {
                $like = '%' . $search . '%';
                $q->where(function ($q) use ($like) {
                    $q->where('p.BillNo', 'like', $like)
                        ->orWhere('p.PatientName', 'like', $like)
                        ->orWhere('p.PatientCode', 'like', $like)
                        ->orWhere('p.MobileNo', 'like', $like);
                });
            });

        $summary = (clone $query)
            ->selectRaw("SUM(CASE WHEN t.type = 'cash' THEN t.amount ELSE 0 END) AS cash")
            ->selectRaw("SUM(CASE WHEN t.type = 'discount' THEN t.amount ELSE 0 END) AS discount")
            ->first();

        $dueCollectionQuery = clone $query;

        $transactions = $query
            ->select('t.transaction_date', 't.type', 't.amount', 't.payment_method', 't.collected_by', 't.source', 'p.BillNo', 'p.PatientName', 'p.PatientCode')
            ->orderByDesc('t.transaction_date')
            ->orderByDesc('t.id')
            ->paginate(50)
            ->withQueryString();

        $itemTotals = DB::table('investigation_payment_items')
            ->selectRaw('PaymentId, SUM(COALESCE(Amount, 0)) AS item_sum')
            ->groupBy('PaymentId');

        $dueCollectionByService = $dueCollectionQuery
            ->join('investigation_payment_items as i', 'i.PaymentId', '=', 'p.ID')
            ->joinSub($itemTotals, 'item_totals', 'item_totals.PaymentId', '=', 'p.ID')
            ->where('t.type', 'cash')
            ->where(function ($query) {
                $query->whereNull('t.payment_item_id')
                    ->orWhereColumn('t.payment_item_id', 'i.ID');
            })
            ->selectRaw("COALESCE(NULLIF(i.CategoryName, ''), 'Others') AS category")
            ->selectRaw('i.ServiceName AS service')
            ->selectRaw('SUM(CASE WHEN t.payment_item_id IS NOT NULL THEN t.amount WHEN item_totals.item_sum > 0 THEN i.Amount * t.amount / item_totals.item_sum ELSE 0 END) AS collected')
            ->groupBy('i.CategoryName', 'i.ServiceName')
            ->orderBy('category')
            ->orderByDesc('collected')
            ->get();

        return view('billing.investigation_report.transaction_history', compact('filters', 'summary', 'transactions', 'dueCollectionByService'));
    }
}
