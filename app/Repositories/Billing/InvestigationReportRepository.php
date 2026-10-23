<?php

namespace App\Repositories\Billing;

use App\Models\InvestigationPayment;
use App\Repositories\Billing\Contracts\InvestigationReportRepositoryInterface;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class InvestigationReportRepository implements InvestigationReportRepositoryInterface
{
    /**
     * configMain rows (Code + Name), memoized per request.
     *
     * @return \Illuminate\Support\Collection
     */
    private function mainCategories(): Collection
    {
        return once(fn () => DB::table('configMain')
            ->select('Code', 'Name')
            ->orderBy('Code')
            ->get());
    }

    /**
     * Canonical category list in display order, derived at runtime from the
     * configMain table so that a newly added main category automatically
     * appears in the report (no hardcoded list to maintain). 'Consultation'
     * (code 9999) and 'Others' (fallback bucket) are appended when absent.
     *
     * @return string[]
     */
    private function canonicalCategories(): array
    {
        return once(fn () => $this->mainCategories()
            ->pluck('Name')
            ->map(fn ($name) => trim((string) $name))
            ->reject(fn ($name) => $name === '')
            ->push('Consultation')
            ->push('Others')
            ->unique()
            ->values()
            ->all());
    }

    /**
     * SQL CASE expression that maps each item row to a canonical category.
     *
     * Priority order:
     *  1. ServiceName pattern for ECG — repairs the legacy row where the
     *     "ECG" service was stored under Pathology (configSub main 103).
     *  2. Consultation (code 9999, not present in configMain).
     *  3. Every configMain category by stored CategoryCode — generated at
     *     runtime, so new main categories are picked up automatically.
     *  4. Anything else lands in "Others".
     */
    private function categoryExpression(): string
    {
        $cases = "CASE
            WHEN UPPER(TRIM(ipi.ServiceName)) LIKE '%ECG%'
              OR UPPER(TRIM(ipi.ServiceName)) LIKE '%ELECTROCARDIOGRAM%'
              OR UPPER(TRIM(ipi.ServiceName)) LIKE '%ECHO%' THEN 'ECG'
            WHEN ipi.CategoryCode = 9999
              OR UPPER(TRIM(ipi.CategoryName)) IN ('DOCTOR CONSULTATION', 'CONSULTATION') THEN 'Consultation'";

        foreach ($this->mainCategories() as $row) {
            if ((int) $row->Code === 105) {
                continue; // "Others" is the fallback bucket; ELSE handles it
            }

            $name = str_replace("'", "''", trim((string) $row->Name));

            $cases .= "\n            WHEN ipi.CategoryCode = {$row->Code} THEN '{$name}'";
        }

        return $cases . "\n            ELSE 'Others'\n        END";
    }

    /**
     * Per-bill item total (sum of item Amounts). Used as the settlement
     * denominator so a bill's recognized revenue ALWAYS equals the cash
     * received (PaidAmount), even when legacy rows have items that do not
     * sum up to the header TotalBill.
     */
    private function itemTotalsSubquery(): Builder
    {
        return DB::table('investigation_payment_items')
            ->selectRaw('PaymentId')
            ->selectRaw('SUM(COALESCE(Amount, 0)) AS item_sum')
            ->groupBy('PaymentId');
    }

    /**
     * Inner query: immutable cash/discount events allocated proportionally to
     * invoice items. Header balances are mutable, so never use them for a
     * collection report.
     */
    private function categorizedSubquery(array $filters)
    {
        $query = DB::table('investigation_payment_transactions as ipt')
            ->join('investigation_payments', 'investigation_payments.ID', '=', 'ipt.invoice_id')
            ->join('investigation_payment_items as ipi', 'ipi.PaymentId', '=', 'investigation_payments.ID')
            ->joinSub($this->itemTotalsSubquery(), 'item_totals', 'item_totals.PaymentId', '=', 'investigation_payments.ID')
            ->where(function ($query) {
                $query->whereNull('ipt.payment_item_id')
                    ->orWhereColumn('ipt.payment_item_id', 'ipi.ID');
            })
            ->whereIn('ipt.type', ['cash', 'discount'])
            ->whereNotIn('investigation_payments.payment_status', ['cancelled', 'refunded'])
            ->when($filters['date_from'] ?? null, fn ($q, $date) => $q->whereDate('ipt.transaction_date', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($q, $date) => $q->whereDate('ipt.transaction_date', '<=', $date));

        return $query
            ->selectRaw("{$this->categoryExpression()} AS category")
            ->selectRaw('investigation_payments.PatientId')
            ->selectRaw('investigation_payments.PatientName')
            ->selectRaw('investigation_payments.PatientCode')
            ->selectRaw('investigation_payments.ID AS payment_id')
            ->selectRaw('ipi.ID AS item_id')
            ->selectRaw('ipi.ServiceName')
            ->selectRaw('ipt.transaction_date')
            ->selectRaw("CASE WHEN ipt.payment_item_id IS NOT NULL THEN ipt.amount WHEN item_totals.item_sum > 0 THEN COALESCE(ipi.Amount, 0) * ipt.amount / item_totals.item_sum ELSE 0 END AS TransactionAmount")
            ->selectRaw("CASE WHEN ipt.type = 'cash' THEN CASE WHEN ipt.payment_item_id IS NOT NULL THEN ipt.amount WHEN item_totals.item_sum > 0 THEN COALESCE(ipi.Amount, 0) * ipt.amount / item_totals.item_sum ELSE 0 END ELSE 0 END AS Amount")
            ->selectRaw("CASE WHEN ipt.type = 'discount' THEN CASE WHEN ipt.payment_item_id IS NOT NULL THEN ipt.amount WHEN item_totals.item_sum > 0 THEN COALESCE(ipi.Amount, 0) * ipt.amount / item_totals.item_sum ELSE 0 END ELSE 0 END AS DiscountAmount");
    }

    public function getCategoryTotals(array $filters): Collection
    {
        $sub = $this->categorizedSubquery($filters);

        $rows = DB::table($sub, 't')
            ->selectRaw('t.category')
            ->selectRaw('COUNT(DISTINCT t.item_id) AS tests')
            ->selectRaw('COUNT(DISTINCT t.PatientId) AS patients')
            ->selectRaw('SUM(t.TransactionAmount) AS gross')
            ->selectRaw('SUM(t.DiscountAmount) AS discount')
            ->selectRaw('SUM(t.Amount) AS revenue')
            ->groupBy('t.category')
            ->get()
            ->keyBy('category');

        $ordered = collect();

        foreach ($this->canonicalCategories() as $category) {
            $row = $rows->get($category);
            $ordered->push((object) [
                'category' => $category,
                'tests'    => (int) ($row->tests ?? 0),
                'patients' => (int) ($row->patients ?? 0),
                'gross'    => (float) ($row->gross ?? 0),
                'discount' => (float) ($row->discount ?? 0),
                'revenue'  => (float) ($row->revenue ?? 0),
            ]);
        }

        $totalPatients = (int) DB::table($sub, 't')
            ->selectRaw('COUNT(DISTINCT t.PatientId) AS patients')
            ->value('patients');

        $ordered->push((object) [
            'category' => 'Total',
            'tests'    => $ordered->sum('tests'),
            'patients' => $totalPatients,
            'gross'    => $ordered->sum('gross'),
            'discount' => $ordered->sum('discount'),
            'revenue'  => $ordered->sum('revenue'),
        ]);

        return $ordered;
    }

    public function getServiceBreakdown(?string $category, array $filters): Collection
    {
        $sub = $this->categorizedSubquery($filters);

        $query = DB::table($sub, 't');

        if ($category) {
            $query = $query->selectRaw('t.ServiceName')
                ->selectRaw('COUNT(DISTINCT t.item_id) AS tests')
                ->selectRaw('SUM(t.TransactionAmount) AS gross')
                ->selectRaw('SUM(t.DiscountAmount) AS discount')
                ->selectRaw('SUM(t.Amount) AS revenue')
                ->where('t.category', $category)
                ->groupBy('t.ServiceName')
                ->orderByDesc('revenue');

            return $query->get()->map(function ($row) use ($category) {
                $row->category = $category;

                return $row;
            })->values();
        }

        $order = array_flip($this->canonicalCategories());

        return $query->selectRaw('t.category')
            ->selectRaw('t.ServiceName')
            ->selectRaw('COUNT(DISTINCT t.item_id) AS tests')
            ->selectRaw('SUM(t.TransactionAmount) AS gross')
            ->selectRaw('SUM(t.DiscountAmount) AS discount')
            ->selectRaw('SUM(t.Amount) AS revenue')
            ->groupBy('t.category')
            ->groupBy('t.ServiceName')
            ->get()
            ->sortBy(function ($row) use ($order) {
                return [$order[$row->category] ?? 99, -(float) $row->revenue];
            })
            ->values();
    }

    public function getPatientCounts(array $filters, string $grouping): Collection
    {
        $periodExpr = $grouping === 'monthly'
            ? "DATE_FORMAT(t.transaction_date, '%Y-%m')"
            : 'DATE(t.transaction_date)';

        $sub = $this->categorizedSubquery($filters);
        $sub = DB::table($sub, 't')->select('t.*')->selectRaw("{$periodExpr} AS period");

        return DB::table($sub, 't')
            ->selectRaw('t.period')
            ->selectRaw('COUNT(DISTINCT t.payment_id) AS invoices')
            ->selectRaw('COUNT(DISTINCT t.PatientId) AS patients')
            ->selectRaw('SUM(t.TransactionAmount) AS gross')
            ->selectRaw('SUM(t.DiscountAmount) AS discount')
            ->selectRaw('SUM(t.Amount) AS revenue')
            ->groupBy('t.period')
            ->orderBy('t.period')
            ->get();
    }

    /**
     * Patient-wise pathology/investigation breakdown: which patients took
     * which tests. This is an administrative "who did what" view rather
     * than a revenue view, so it reads invoice items by their service date
     * rather than payment transactions. It does NOT filter by payment status
     * or amount. A patient's row appears
     * here as soon as a test was performed, regardless of whether the bill
     * is due, partial, or paid.
     *
     * Optional $category narrows to one diagnostic category (e.g.
     * "Pathology"); null returns all categories.
     */
    public function getPatientTestBreakdown(array $filters, ?string $category = null): Collection
    {
        $sub = InvestigationPayment::query()
            ->join('investigation_payment_items as ipi', 'ipi.PaymentId', '=', 'investigation_payments.ID')
            ->paymentDateBetween($filters['date_from'] ?? null, $filters['date_to'] ?? null)
            ->excludeCancelledRefunded()
            ->selectRaw("{$this->categoryExpression()} AS category")
            ->selectRaw('investigation_payments.PatientId')
            ->selectRaw('investigation_payments.PatientName')
            ->selectRaw('investigation_payments.PatientCode')
            ->selectRaw('ipi.ServiceName')
            ->toBase();

        $query = DB::table($sub, 't');

        if ($category) {
            $query = $query->where('t.category', $category);
        }

        return $query
            ->selectRaw('t.PatientId')
            ->selectRaw('t.PatientName')
            ->selectRaw('t.PatientCode')
            ->selectRaw('GROUP_CONCAT(DISTINCT t.ServiceName ORDER BY t.ServiceName SEPARATOR \', \') AS tests')
            ->selectRaw('COUNT(*) AS test_count')
            ->groupBy('t.PatientId')
            ->groupBy('t.PatientName')
            ->groupBy('t.PatientCode')
            ->orderBy('t.PatientName')
            ->get();
    }

    public function getCategories(): array
    {
        return $this->canonicalCategories();
    }
}
