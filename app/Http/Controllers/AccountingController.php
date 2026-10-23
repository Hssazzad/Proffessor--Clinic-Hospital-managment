<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Payment;
use App\Models\Accounting\AcctGltypeInfo;
use App\Models\Accounting\AcctTblBalsheetGroup;
use App\Models\Accounting\AcctTblBalsheetSubGroup;
use App\Models\Accounting\AcctTblBalsheetAccount;
use App\Models\Patient;
use App\Services\Accounting\GlType;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AccountingController extends Controller
{
    public function showGeneralLedger()
    {
        $rows = DB::table('acct_tbl_balsheet_group as g')
            ->leftJoin('acct_tbl_balsheet_account as a', 'a.gl_group', '=', 'g.gl_group')
            ->leftJoin('acct_tbl_balance as b', 'b.AccountNo', '=', 'a.AccountNo')
            ->select('g.gl_type', 'g.gl_group_name', 'g.ID')
            ->selectRaw('COALESCE(SUM(b.Balance), 0) as balance')
            ->groupBy('g.ID', 'g.gl_type', 'g.gl_group_name')
            ->orderBy('g.gl_type')
            ->orderBy('g.ID')
            ->get();

        
        $liabilities = $rows->where('gl_type', GlType::LIABILITY)->values();
        $assets = $rows->where('gl_type', GlType::ASSET)->values();
        $income = $rows->where('gl_type', GlType::INCOME)->values();
        $expenses = $rows->where('gl_type', GlType::EXPENDITURE)->values();

        $totalIncome = $income->sum('balance');
        $totalExpenditure = $expenses->sum('balance');
       
        $netIncome = $totalIncome - $totalExpenditure;
        $totalLiabEquity = $liabilities->sum('balance') + $netIncome;

        return view('accounting.show_general_ledger', [
            'income' => $income,
            'expenses' => $expenses,
            'assets' => $assets,
            'liabilities' => $liabilities,
            'totalIncome' => $totalIncome,
            'totalExpenditure' => $totalExpenditure,
            'netIncome' => $netIncome,
            'totalAsset' => $assets->sum('balance'),
            'totalLiability' => $liabilities->sum('balance'),
            'totalLiabEquity' => $totalLiabEquity,
        ]);
    }

    public function incomeStatement()
    {
        return view('accounting.income_statement', $this->incomeStatementData());
    }

 
    public function monthlyProfit(Request $request)
    {
        $month = (string) $request->input('month', now()->format('Y-m'));

       
        if (! preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month)) {
            $month = now()->format('Y-m');
        }

        $start = Carbon::parse($month . '-01')->startOfMonth()->toDateString();
        $end   = Carbon::parse($month . '-01')->endOfMonth()->toDateString();

        $rows = function (int $glType) use ($start, $end) {
            return DB::table('acct_tbl_balsheet_account as a')
                ->join('acct_tbl_transaction as t', 't.AccountNo', '=', 'a.AccountNo')
                ->where('a.gl_type', $glType)
                ->whereDate('t.PostingDate', '>=', $start)
                ->whereDate('t.PostingDate', '<=', $end)
                ->select('a.AccountNo', 'a.title as label')
                ->selectRaw('COALESCE(SUM(t.CrAmount),0) as cr, COALESCE(SUM(t.DrAmount),0) as dr')
                ->groupBy('a.AccountNo', 'a.title')
                ->orderBy('a.AccountNo')
                ->get();
        };

        // Income is credit-natured, expenditure is debit-natured.
        $income = $rows(GlType::INCOME)->map(function ($r) {
            $r->amount = (float) $r->cr - (float) $r->dr;
            return $r;
        })->filter(fn ($r) => abs($r->amount) > 0.004)->values();

        $expense = $rows(GlType::EXPENDITURE)->map(function ($r) {
            $r->amount = (float) $r->dr - (float) $r->cr;
            return $r;
        })->filter(fn ($r) => abs($r->amount) > 0.004)->values();

        $totalIncome  = $income->sum('amount');
        $totalExpense = $expense->sum('amount');
        $net          = $totalIncome - $totalExpense;

        // TODO: admitted patients / operations - wire to the admission and OT
        // tables once their names are known. Until then they can be passed in
        // the URL (?admitted=76&operations=58) for printing.
        $admitted   = $request->input('admitted');
        $operations = $request->input('operations');

        return view('accounting.monthly_profit', compact(
            'month', 'income', 'expense', 'totalIncome', 'totalExpense', 'net', 'admitted', 'operations'
        ));
    }

    public function cobProcess()
    {
        $closings = DB::table('acct_tbl_closebalance as c')
            ->leftJoin('acct_tbl_balsheet_account as a', 'a.AccountNo', '=', 'c.AccountNo')
            ->select('c.*', 'a.title as account_title')
            ->orderByDesc('c.EventDate')
            ->orderBy('c.AccountNo')
            ->get();

        return view('accounting.cob_process', compact('closings'));
    }

    /**
     * Investigation Accounting Reconciliation
     *
     * Billing side  : investigation_payments table
     * Accounting side: acct_tbl_balance for the 4 investigation GL accounts
     *
     * Correct formula:
     *   GL Income (40601101)                     = TotalBill sum
     *   GL Discount (51501101)     [expenditure] = Discount sum
     *   GL Receivable (20701101)   [asset/due]   = DueAmount sum
     *   ──────────────────────────────────────────────────────────
     *   GL Net Cash from inv. = Income − Discount − Receivable
     *   Billing Net Cash      = Gross − Discount − Due (= PaidAmount)
     *
     * NOTE: GL Cash account (205101101) is shown for reference only.
     * It may include opening-balance entries and other non-investigation
     * cash flows, so it is NOT used as the comparison target.
     */
    public function investigationReconciliation(Request $request)
    {
        $dateFrom = $request->input('date_from');
        $dateTo   = $request->input('date_to');

        // ── Billing-side summary (investigation_payments table) ──────────────
        $query = DB::table('investigation_payments')
            ->whereNotIn('payment_status', ['cancelled', 'refunded']);

        if ($dateFrom) {
            $query->whereDate('PaymentDate', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('PaymentDate', '<=', $dateTo);
        }

        $row = (clone $query)
            ->selectRaw('
                COALESCE(SUM(TotalBill),    0) AS gross_income,
                COALESCE(SUM(Discount),     0) AS total_discount,
                COALESCE(SUM(PaidAmount),   0) AS total_paid,
                COALESCE(SUM(DueAmount),    0) AS total_due,
                COUNT(*)                        AS total_invoices
            ')
            ->first();

        $grossIncome   = (float) ($row->gross_income   ?? 0);
        $totalDiscount = (float) ($row->total_discount ?? 0);
        $totalPaid     = (float) ($row->total_paid     ?? 0);
        $totalDue      = (float) ($row->total_due      ?? 0);
        $totalInvoices = (int)   ($row->total_invoices ?? 0);

        // Billing net: Gross − Discount − Due  (should == PaidAmount)
        $billingNetCash = round($grossIncome - $totalDiscount - $totalDue, 2);

        // ── GL Accounting balances (all-time, full ledger) ───────────────────
        $glIncome     = (float) (DB::table('acct_tbl_balance')->where('AccountNo', '40601101')->value('Balance') ?? 0);
        $glDiscount   = (float) (DB::table('acct_tbl_balance')->where('AccountNo', '51501101')->value('Balance') ?? 0);
        $glReceivable = (float) (DB::table('acct_tbl_balance')->where('AccountNo', '20701101')->value('Balance') ?? 0);
        // Cash shown for reference — may include opening balance / other entries
        $glCash       = (float) (DB::table('acct_tbl_balance')->where('AccountNo', '205101101')->value('Balance') ?? 0);

        // GL net cash from investigations = Income − Discount − Receivable
        // This is the correct counter-part to billing PaidAmount
        $glNetCash = round($glIncome - $glDiscount - $glReceivable, 2);

        // ── Differences (billing vs GL) ─────────────────────────────────────
        $diffIncome    = round($grossIncome   - $glIncome,     2);
        $diffDiscount  = round($totalDiscount - $glDiscount,   2);
        $diffReceiv    = round($totalDue      - $glReceivable, 2);
        $diffNetCash   = round($billingNetCash - $glNetCash,   2);   // main reconciliation line

        // Opening-balance component in GL Cash (informational)
        $glCashNonInv = round($glCash - $glNetCash, 2);   // e.g. opening balance etc.

        // ── Expenditure total from accounting (all expense accounts) ─────────
        // Gives admin a daily profit view: Income − all expenditure
        $totalGlExpenditure = (float) DB::table('acct_tbl_balsheet_group as g')
            ->leftJoin('acct_tbl_balsheet_account as a', 'a.gl_group', '=', 'g.gl_group')
            ->leftJoin('acct_tbl_balance as b', 'b.AccountNo', '=', 'a.AccountNo')
            ->where('g.gl_type', 5)  // gl_type 5 = Expenditure
            ->sum('b.Balance');

        $glNetProfit = round($glIncome - $totalGlExpenditure, 2);

        // ── Per-day breakdown ────────────────────────────────────────────────
        $dailyRows = (clone $query)
            ->selectRaw('
                DATE(PaymentDate)              AS pay_date,
                COUNT(*)                       AS invoices,
                COALESCE(SUM(TotalBill),   0)  AS gross,
                COALESCE(SUM(Discount),    0)  AS discount,
                COALESCE(SUM(PaidAmount),  0)  AS paid,
                COALESCE(SUM(DueAmount),   0)  AS due
            ')
            ->groupByRaw('DATE(PaymentDate)')
            ->orderByRaw('DATE(PaymentDate) DESC')
            ->get();

        return view('accounting.investigation_reconciliation', compact(
            // billing side
            'grossIncome', 'totalDiscount', 'totalPaid', 'totalDue',
            'totalInvoices', 'billingNetCash',
            // GL side
            'glIncome', 'glDiscount', 'glReceivable', 'glCash',
            'glNetCash', 'glCashNonInv',
            // differences
            'diffIncome', 'diffDiscount', 'diffReceiv', 'diffNetCash',
            // profit/loss
            'totalGlExpenditure', 'glNetProfit',
            // daily
            'dailyRows', 'dateFrom', 'dateTo'
        ));
    }


    /**
     * Expenditure only.
     *
     * This used to return the combined income statement verbatim, so the
     * "Expenditure Statement" menu item and the "Income Statement" menu item
     * rendered the identical page.
     */
    public function expenditureStatement()
    {
        $expenditure = $this->groupBalancesByGlType(GlType::EXPENDITURE);

        return view('accounting.expenditure_statement', [
            'expenditure'      => $expenditure,
            'totalExpenditure' => $expenditure->sum('balance'),
            'asOnDate'         => '',
            'printDate'        => '',
        ]);
    }

    /**
     * Balances rolled up to gl_group for one gl_type.
     */
    private function groupBalancesByGlType(int $glType)
    {
        return DB::table('acct_tbl_balsheet_group as g')
            ->leftJoin('acct_tbl_balsheet_account as a', 'a.gl_group', '=', 'g.gl_group')
            ->leftJoin('acct_tbl_balance as b', 'b.AccountNo', '=', 'a.AccountNo')
            ->where('g.gl_type', $glType)
            ->select('g.gl_group', 'g.gl_group_name')
            ->selectRaw('COALESCE(SUM(b.Balance), 0) as balance')
            ->groupBy('g.ID', 'g.gl_group', 'g.gl_group_name')
            ->orderBy('g.gl_group')
            ->get();
    }

    /**
     * Total balance across every account whose group has one of the given gl_types.
     */
    private function totalBalanceForGlTypes(array $glTypes): float
    {
        return (float) DB::table('acct_tbl_balsheet_group as g')
            ->leftJoin('acct_tbl_balsheet_account as a', 'a.gl_group', '=', 'g.gl_group')
            ->leftJoin('acct_tbl_balance as b', 'b.AccountNo', '=', 'a.AccountNo')
            ->whereIn('g.gl_type', $glTypes)
            ->sum('b.Balance');
    }

    private function incomeStatementData(): array
    {
        $expenditure = $this->groupBalancesByGlType(GlType::EXPENDITURE);
        $income = $this->groupBalancesByGlType(GlType::INCOME);

        return [
            'expenditure' => $expenditure,
            'income' => $income,
            'totalExpenditure' => $expenditure->sum('balance'),
            'totalIncome' => $income->sum('balance'),
            'netIncome' => $income->sum('balance') - $expenditure->sum('balance'),
            'asOnDate' => '',
            'printDate' => '',
        ];
    }

    public function balanceSheet()
    {
        return view('accounting.balance_sheet', $this->balanceSheetData());
    }

    private function balanceSheetData(): array
    {
        $sections = (array) config('accounting.balance_sheet');

        $liabilities = $this->groupBalancesByGlType(GlType::LIABILITY);
        $assets = $this->groupBalancesByGlType(GlType::ASSET);

        $totalLiability = $this->totalBalanceForGlTypes($sections['liability_types'] ?? [1, 4]);
        $totalAsset = $this->totalBalanceForGlTypes($sections['asset_types'] ?? [2, 5]);

        $incomeTotal = $this->totalBalanceForGlTypes([GlType::INCOME]);
        $expenseTotal = $this->totalBalanceForGlTypes([GlType::EXPENDITURE]);

        return compact('liabilities', 'assets', 'totalLiability', 'totalAsset','incomeTotal','expenseTotal') + [
            'netIncome' => $incomeTotal - $expenseTotal,
            'asOnDate' => '', 'printDate' => '',
        ];
    }

    public function subGroupSummary()
    {
        return view('accounting.subgroup_summary', [
            'subgroups' => AcctTblBalsheetSubGroup::orderBy('gl_sub_group_name')->get(),
            'accounts' => collect(),
        ]);
    }

    public function showSubGroupSummary(Request $request)
    {
        $accounts = AcctTblBalsheetAccount::where('gl_sub_group', $request->input('subgroup'))
            ->leftJoin('acct_tbl_balance as b', 'b.AccountNo', '=', 'acct_tbl_balsheet_account.AccountNo')
            ->select('acct_tbl_balsheet_account.*', DB::raw('COALESCE(b.Balance, 0) as current_balance'))
            ->orderBy('AccountNo')->get();
        return view('accounting.subgroup_summary', [
            'subgroups' => AcctTblBalsheetSubGroup::orderBy('gl_sub_group_name')->get(),
            'accounts' => $accounts,
            'selected' => $request->input('subgroup'),
        ]);
    }

    public function showGroupDetails(Request $request)
    {
        $accounts = AcctTblBalsheetAccount::where('gl_group', $request->input('gl_group'))
            ->leftJoin('acct_tbl_balance as b', 'b.AccountNo', '=', 'acct_tbl_balsheet_account.AccountNo')
            ->select('acct_tbl_balsheet_account.*', DB::raw('COALESCE(b.Balance, 0) as current_balance'))
            ->orderBy('gl_sub_group')->orderBy('AccountNo')->get();
        return view('accounting.group_summary', ['accounts' => $accounts]);
    }

    public function netProfitLoss()
    {
        $groups = DB::table('acct_tbl_balsheet_group as g')
            ->leftJoin('acct_tbl_balsheet_account as a', 'a.gl_group', '=', 'g.gl_group')
            ->leftJoin('acct_tbl_balance as b', 'b.AccountNo', '=', 'a.AccountNo')
            ->select('g.gl_group_name', 'g.tradingaccount', DB::raw('COALESCE(SUM(b.Balance), 0) as balance'))
            ->whereIn('g.tradingaccount', [901, 902, 903, 904, 905])
            ->groupBy('g.ID', 'g.gl_group_name', 'g.tradingaccount')->orderBy('g.gl_group')->get();
        return view('accounting.profit_loss', ['groups' => $groups]);
    }

    
    public function updateBalances()
    {
        DB::transaction(function () {
            DB::statement('UPDATE acct_tbl_balsheet_account a LEFT JOIN acct_tbl_balance b ON b.AccountNo = a.AccountNo SET a.Balance = COALESCE(b.Balance, 0)');
            DB::statement('UPDATE acct_tbl_balsheet_sub_group s SET s.Balance = (SELECT COALESCE(SUM(a.Balance), 0) FROM acct_tbl_balsheet_account a WHERE a.gl_sub_group = s.gl_sub_group)');
            DB::statement('UPDATE acct_tbl_balsheet_group g SET g.Balance = (SELECT COALESCE(SUM(s.Balance), 0) FROM acct_tbl_balsheet_sub_group s WHERE s.gl_group = g.gl_group)');
        });
        return back()->with('success', 'Balances updated successfully.');
    }

   
    public function addgroup()
    {
        $gltype = AcctGltypeInfo::orderBy('code', 'desc')->get();
        return view('accounting.addgroup', compact('gltype'));
    }

    public function nextGlGroup(Request $request)
    {
        $glType = (int) $request->input('gl_type');
        $lastNumber = AcctTblBalsheetGroup::where('gl_type', $glType)->max('gl_group');

        if ($lastNumber) {
            $next = $lastNumber + 1;
        } else {
            $next = ($glType * 100) + 1;
        }

        return response($next);
    }

    public function addGroupAction(Request $request)
    {
        $validated = $request->validate([
            'gl_type'       => 'required|integer',
            'gl_group'      => 'required|integer|unique:acct_tbl_balsheet_group,gl_group',
            'gl_group_name' => 'required|string|max:50',
            'tradingac'     => 'nullable|integer',
        ]);

        AcctTblBalsheetGroup::create([
            'gl_type'        => $validated['gl_type'],
            'gl_group'       => $validated['gl_group'],
            'gl_group_name'  => $validated['gl_group_name'],
            'tradingaccount' => $request->input('tradingac', 0),
            'Balance'        => 0,
        ]);

        $rs_result = AcctTblBalsheetGroup::where('gl_type', $validated['gl_type'])
            ->orderBy('gl_group')
            ->get();

        return view('accounting.gl_group_list_for_gl_create', ['rs_result' => $rs_result]);
    }

    public function getGroupList(Request $request)
    {
        $glType = $request->input('gl_type');

        if ($glType != '') {
            $rs_result = AcctTblBalsheetGroup::where('gl_type', $glType)
                ->orderBy('gl_group')
                ->get();

            return view('accounting.gl_group_list_for_gl_create', [
                'rs_result' => $rs_result,
            ]);
        }

        return response('');
    }

   
    public function editGroup(AcctTblBalsheetGroup $group)
    {
        return view('accounting.addgroup', [
            'gltype' => AcctGltypeInfo::orderBy('code', 'desc')->get(),
            'group'  => $group,
        ]);
    }

    public function updateGroup(Request $request, AcctTblBalsheetGroup $group)
    {
        $oldCode = (int) $group->gl_group;

        $validated = $request->validate([
            'gl_type'       => 'required|integer',
            'gl_group'      => ['required', 'integer', Rule::unique('acct_tbl_balsheet_group', 'gl_group')->ignore($group->ID, 'ID')],
            'gl_group_name' => 'required|string|max:50',
            'tradingac'     => 'nullable|integer',
        ], [
            'gl_group.unique' => 'Another group already uses that code.',
        ]);

        $newCode = (int) $validated['gl_group'];

       
        DB::transaction(function () use ($request, $group, $validated, $oldCode, $newCode) {
            $group->update([
                'gl_type'        => $validated['gl_type'],
                'gl_group'       => $newCode,
                'gl_group_name'  => $validated['gl_group_name'],
                'tradingaccount' => $request->input('tradingac', 0),
            ]);

            if ($newCode !== $oldCode) {
                AcctTblBalsheetSubGroup::where('gl_group', $oldCode)
                    ->update(['gl_group' => $newCode]);
                AcctTblBalsheetAccount::where('gl_group', $oldCode)
                    ->update(['gl_group' => $newCode]);
            }
        });

        return redirect()->route('accounting.addgroup')->with('success', 'Group updated successfully.');
    }

    public function deleteGroup(Request $request)
    {
        $code = $request->input('gl_group');

        $used = AcctTblBalsheetSubGroup::where('gl_group', $code)->exists()
            || AcctTblBalsheetAccount::where('gl_group', $code)->exists();

        if (! $used) {
            AcctTblBalsheetGroup::where('gl_group', $code)->delete();
        }

        return redirect()->route('accounting.addgroup')->with(
            $used ? 'error' : 'success',
            $used ? 'Group has sub groups / accounts and was not deleted.' : 'Group deleted successfully.'
        );
    }



    public function addSubGroup()
    {
        $gltype = AcctGltypeInfo::orderBy('code', 'desc')
            ->select('code', 'name')
            ->get();

        return view('accounting.addsubgroup', compact('gltype'));
    }

    public function getGroupCodeByGlType(Request $request)
    {
        $glType = $request->input('gl_type');

        if ($glType === '' || $glType === null) {
            return response('');
        }

        $rows = DB::table('acct_tbl_balsheet_group')
            ->where('gl_type', $glType)
            ->orderBy('gl_group_name')
            ->get(['gl_group', 'gl_group_name']);

        $output = '<option value="">Select GL Group</option>';
        foreach ($rows as $row) {
            $output .= '<option value="' . e($row->gl_group) . '">' . e($row->gl_group_name) . '</option>';
        }

        return response($output);
    }

    public function nextGlSubGroup(Request $request)
    {
        $gl_group = (int) $request->input('gl_group');

        $lastNumber = AcctTblBalsheetSubGroup::where('gl_group', $gl_group)->max('gl_sub_group');

        if ($lastNumber) {
            $next = $lastNumber + 1;
        } else {
            $next = ($gl_group * 100) + 1;
        }

        return response($next);
    }

    public function genGlSubGroupCode(Request $request)
    {
        return $this->nextGlSubGroup($request);
    }

    public function addSubGroupAction(Request $request)
    {
        $validated = $request->validate([
            'gl_type'           => 'required|integer',
            'gl_group'          => 'required|integer',
            'gl_sub_group'      => 'required|integer|unique:acct_tbl_balsheet_sub_group,gl_sub_group',
            'gl_sub_group_name' => 'required|string|max:50',
            'tradingac'         => 'nullable|integer',
        ]);

        AcctTblBalsheetSubGroup::create([
            'gl_type'           => $validated['gl_type'],
            'gl_group'          => $validated['gl_group'],
            'gl_sub_group'      => $validated['gl_sub_group'],
            'gl_sub_group_name' => $validated['gl_sub_group_name'],
            'tradingaccount'    => $request->input('tradingac', 0),
            'Balance'           => 0,
        ]);

        $rs_result = AcctTblBalsheetSubGroup::where('gl_group', $validated['gl_group'])
            ->orderBy('gl_sub_group')
            ->get();

        return view('accounting.gl_sub_group_list', ['rs_result' => $rs_result]);
    }

    public function getSubGroupList(Request $request)
    {
        $glGroup = $request->input('gl_group');

        if ($glGroup === '' || $glGroup === null) {
            return response('');
        }

        $rs_result = AcctTblBalsheetSubGroup::where('gl_group', $glGroup)
            ->orderBy('gl_sub_group')
            ->get();

        return view('accounting.gl_sub_group_list', ['rs_result' => $rs_result]);
    }

    
    public function addAccount()
    {
        $gltype = AcctGltypeInfo::orderBy('code', 'desc')
            ->select('code', 'name')
            ->get();

        return view('accounting.addaccount', compact('gltype'));
    }

    public function addAccountAction(Request $request)
    {
        $validated = $request->validate([
            'gl_type'      => ['required', 'integer'],
            'gl_group'     => ['required', 'integer', 'exists:acct_tbl_balsheet_group,gl_group'],
            'gl_sub_group' => ['required', 'integer', 'exists:acct_tbl_balsheet_sub_group,gl_sub_group'],
          
            'AccountNo'    => ['required', 'string', 'max:20', 'unique:acct_tbl_balsheet_account,AccountNo'],
            'title'        => ['required', 'string', 'max:50'],
        ], [
            'AccountNo.unique'    => 'That account number already exists in the chart of accounts.',
            'gl_group.exists'     => 'The selected GL group does not exist.',
            'gl_sub_group.exists' => 'The selected GL sub group does not exist.',
        ]);

        
        $subGroup = AcctTblBalsheetSubGroup::where('gl_sub_group', $validated['gl_sub_group'])->first();

        if ($subGroup && ((int) $subGroup->gl_group !== (int) $validated['gl_group']
            || (int) $subGroup->gl_type !== (int) $validated['gl_type'])) {
            return back()
                ->withInput()
                ->withErrors(['gl_sub_group' => 'The selected sub group belongs to a different GL group or GL type.']);
        }

    
        AcctTblBalsheetAccount::create([
            'gl_type'      => $validated['gl_type'],
            'gl_group'     => $validated['gl_group'],
            'gl_sub_group' => $validated['gl_sub_group'],
            'AccountNo'    => trim($validated['AccountNo']),
            'title'        => $validated['title'],
            'AccountType'  => 0,
            'Balance'      => 0.00,
        ]);

        $rs_result = AcctTblBalsheetAccount::where('gl_group', $validated['gl_group'])
            ->where('gl_sub_group', $validated['gl_sub_group'])
            ->orderBy('AccountNo')
            ->get();
        
        $gl_group     = $validated['gl_group'];
        $gl_sub_group = $validated['gl_sub_group'];

        if ($gl_group === '' || $gl_group === null) {
            return response('');
        }

        $rs_result = $this->accountListRows((int) $gl_group, (int) $gl_sub_group);

        return view('accounting.account_list_table', ['rs_result' => $rs_result]);
        
       // return view('accounting.account_list', ['rs_result' => $rs_result]);
    }

    public function getSubGroupByGlGroup(Request $request)
    {
        $gl_group = $request->input('gl_group');

        if ($gl_group === '' || $gl_group === null) {
            return response('');
        }

        $rows = AcctTblBalsheetSubGroup::where('gl_group', $gl_group)
            ->orderBy('gl_sub_group')
            ->get(['gl_sub_group', 'gl_sub_group_name']);

        $output = '<option value="">Select GL Sub Group</option>';
        foreach ($rows as $row) {
            $output .= '<option value="' . e($row->gl_sub_group) . '">' 
                     . e($row->gl_sub_group_name) . '</option>';
        }

        return response($output);
    }

    public function nextAccountNo(Request $request)
    {
        $gl_group     = $request->input('gl_group');
        $gl_sub_group = $request->input('gl_sub_group');

        $count = AcctTblBalsheetAccount::where('gl_group', $gl_group)
            ->where('gl_sub_group', $gl_sub_group)
            ->count();

        if ($count > 0) {
            $last = AcctTblBalsheetAccount::where('gl_group', $gl_group)
                ->where('gl_sub_group', $gl_sub_group)
                ->max('AccountNo');

            $nextcode = $last + 1;
        } else {
            // CI logic: gl_sub_group . "101"
            $nextcode = $gl_sub_group . '101';
        }

        return response($nextcode);
    }

    private function accountListRows(int $glGroup = 0, int $glSubGroup = 0)
    {
        $query = DB::table('acct_tbl_balsheet_account as a')
            ->leftJoin('acct_tbl_balance as b', 'b.AccountNo', '=', 'a.AccountNo')
            ->leftJoin('acct_gltype_info as gt', 'gt.code', '=', 'a.gl_type')
            ->select(
                'a.ID',
                'a.AccountNo',
                'a.title',
                'a.gl_type',
                'a.gl_group',
                'a.gl_sub_group',
                'gt.name as gl_type_name',
                DB::raw('COALESCE(b.Balance, 0) as balance')
            );

        if ($glGroup > 0) {
            $query->where('a.gl_group', $glGroup);
        }

        if ($glSubGroup > 0) {
            $query->where('a.gl_sub_group', $glSubGroup);
        }

        return $query->orderBy('a.AccountNo')->get();
    }

    public function getAccountList(Request $request)
    {
        $gl_group     = $request->input('gl_group');
        $gl_sub_group = $request->input('gl_sub_group');

        if ($gl_group === '' || $gl_group === null) {
            return response('');
        }

        $rs_result = $this->accountListRows((int) $gl_group, (int) $gl_sub_group);

        return view('accounting.account_list_table', ['rs_result' => $rs_result]);
    }

    public function accountList()
    {
        return view('accounting.account_list', [
            'rs_result' => $this->accountListRows(),
        ]);
    }

    public function editAccount(AcctTblBalsheetAccount $account)
    {
        return view('accounting.editaccount', [
            'account' => $account,
        ]);
    }

    public function updateAccount(Request $request, AcctTblBalsheetAccount $account)
    {
        $data = $request->validate([
            'title' => 'required|string|max:50',
        ]);
        $account->update($data);
        return redirect()->route('accounting.accountlist')->with('success', 'Account updated successfully.');
    }

    public function deleteAccount(Request $request)
    {
        $accountNo = $request->input('AccountNo');

        if ($accountNo === null || trim((string) $accountNo) === '') {
            return redirect()->route('accounting.accountlist')->with('error', 'No account number supplied.');
        }

        $blockers = $this->accountReferences((string) $accountNo);

        if (! empty($blockers)) {
            return redirect()->route('accounting.accountlist')->with(
                'error',
                'Not deleted - the account is still referenced by: ' . implode(', ', $blockers) . '.'
            );
        }

        AcctTblBalsheetAccount::where('AccountNo', $accountNo)->delete();

        return redirect()->route('accounting.accountlist')->with('success', 'Deleted Successfully');
    }

   
    private function accountReferences(string $accountNo): array
    {
        $found = [];
        $schema = DB::getSchemaBuilder();

        // acct_tbl_balance with non-zero balance
        if ($schema->hasTable('acct_tbl_balance')) {
            $hasNonZero = DB::table('acct_tbl_balance')
                ->where('AccountNo', $accountNo)
                ->where('Balance', '!=', 0)
                ->exists();

            if ($hasNonZero) {
                $found[] = 'a non-zero balance';
            }
        }

        $singleColumn = [
            'acct_tbl_transaction'       => 'posted transactions',
            'acct_tbl_transaction_tmp'   => 'unposted transactions',
            'acct_tbl_openning_balance'  => 'an opening balance',
        ];

        foreach ($singleColumn as $table => $label) {
            if ($schema->hasTable($table) && DB::table($table)->where('AccountNo', $accountNo)->exists()) {
                $found[] = $label;
            }
        }

        // This table names the two sides separately instead of using AccountNo.
        if ($schema->hasTable('acct_tbl_unauth_transaction')) {
            $pending = DB::table('acct_tbl_unauth_transaction')
                ->where('DrAccount', $accountNo)
                ->orWhere('CrAccount', $accountNo)
                ->exists();

            if ($pending) {
                $found[] = 'pending vouchers awaiting authorization';
            }
        }

        return $found;
    }

    public function editSubGroup(AcctTblBalsheetSubGroup $subgroup)
    {
        return view('accounting.addsubgroup', [
            'gltype' => AcctGltypeInfo::orderByDesc('code')->select('code', 'name')->get(),
            'subgroup' => $subgroup,
        ]);
    }

    public function updateSubGroup(Request $request, AcctTblBalsheetSubGroup $subgroup)
    {
        $oldCode = (int) $subgroup->gl_sub_group;

        $data = $request->validate([
            'gl_sub_group' => [
                'required',
                'integer',
                Rule::unique('acct_tbl_balsheet_sub_group', 'gl_sub_group')->ignore($subgroup->ID, 'ID'),
            ],
            'gl_sub_group_name' => ['required', 'string', 'max:50'],
        ], [
            'gl_sub_group.unique' => 'Another sub group already uses that code.',
        ]);

        $newCode = (int) $data['gl_sub_group'];

        // Renumbering the sub group used to leave its accounts pointing at the
        // old code, so they disappeared from the sub group listing and from
        // every report that joins accounts to sub groups. Move the children in
        // the same transaction as the parent.
        DB::transaction(function () use ($subgroup, $data, $oldCode, $newCode) {
            $subgroup->update($data);

            if ($newCode !== $oldCode) {
                AcctTblBalsheetAccount::where('gl_sub_group', $oldCode)
                    ->update(['gl_sub_group' => $newCode]);
            }
        });

        return redirect()->route('accounting.subgrouplist')->with('success', 'Sub group updated successfully.');
    }

    public function subgroupList()
    {
        return view('accounting.gl_sub_group_list', [
            'rs_result' => AcctTblBalsheetSubGroup::orderBy('gl_sub_group')->get(),
        ]);
    }

    public function deleteSubGroup(Request $request)
    {
        $code = $request->input('gl_sub_group');
        $used = AcctTblBalsheetAccount::where('gl_sub_group', $code)->exists();
        if (!$used) {
            AcctTblBalsheetSubGroup::where('gl_sub_group', $code)->delete();
        }
        return redirect()->route('accounting.subgrouplist')->with($used ? 'error' : 'success', $used
            ? 'Sub group has accounts and was not deleted.' : 'Sub group deleted successfully.');
    }

    public function initialBalance()
    {
        return view('accounting.add_initial_balance', [
            'accounts' => AcctTblBalsheetAccount::orderBy('title')->get(['AccountNo', 'title']),
        ]);
    }


    public function initialBalanceAction(Request $request)
    {
        $data = $request->validate([
            'CreditAccount' => ['required', 'exists:acct_tbl_balsheet_account,AccountNo'],
            'Amount'        => ['required', 'numeric', 'gt:0'],
            'Date'          => ['required', 'date'],
            'Type'          => ['nullable', 'in:Dr,Cr'],
        ], [
            'CreditAccount.exists' => 'That account does not exist in the chart of accounts.',
            'Amount.gt'            => 'The opening balance must be greater than zero.',
        ]);

        $accountNo = $data['CreditAccount'];
        $amount    = (float) $data['Amount'];
        $date      = $data['Date'];

        $account = AcctTblBalsheetAccount::where('AccountNo', $accountNo)->firstOrFail();

        // Determine normal balance direction from gl_type if not explicitly supplied
        $explicitType = $data['Type'] ?? null;
        if ($explicitType) {
            $isDebitOpening = ($explicitType === 'Dr');
        } else {
            // Normal balance: Asset/Expense = Debit; Liability/Income = Credit
            $isDebitOpening = in_array((int) $account->gl_type, [2, 5]);
        }

        // Contra account: a Liability-type equity account (e.g. 10101101)
        $contraAccount = config('accounting.opening_balance_contra_account');

        if (empty($contraAccount)) {
            return back()->with('error', 'Opening balance contra account is not configured. Set opening_balance_contra_account in config/accounting.php.');
        }

        if (! AcctTblBalsheetAccount::where('AccountNo', $contraAccount)->exists()) {
            return back()->with('error', 'Opening balance equity account (' . $contraAccount . ') does not exist in the chart of accounts.');
        }

        // Prevent duplicate opening balance
        $alreadyRecorded = DB::table('acct_tbl_openning_balance')
            ->where('AccountNo', $accountNo)
            ->exists();

        if ($alreadyRecorded) {
            return back()
                ->withInput()
                ->with('error', 'This account already has an opening balance. Edit the existing one instead of adding another.');
        }

        DB::transaction(function () use ($accountNo, $amount, $date, $isDebitOpening, $contraAccount) {
            // 1. Authorised voucher
            $voucherId = DB::table('acct_tbl_voucher')->insertGetId([
                'VoucherNo'   => 'OPB-' . time(),
                'Date'        => $date,
                'Status'      => 'Authorized',
                'Narration'   => 'Opening Balance',
                'ScreenType'  => 'OB',
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

            // 2. Post both legs
            if ($isDebitOpening) {
                // Target = Dr, Equity = Cr
                DB::table('acct_tbl_transaction')->insert([
                    'VoucherID'     => $voucherId,
                    'AccountNo'     => $accountNo,
                    'DrAmount'      => $amount,
                    'CrAmount'      => 0,
                    'Balance'       => $amount,
                    'PostingDate'   => $date,
                    'Status'        => 'Authorized',
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
                DB::table('acct_tbl_transaction')->insert([
                    'VoucherID'     => $voucherId,
                    'AccountNo'     => $contraAccount,
                    'DrAmount'      => 0,
                    'CrAmount'      => $amount,
                    'Balance'       => -$amount,
                    'PostingDate'   => $date,
                    'Status'        => 'Authorized',
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
            } else {
                // Target = Cr, Equity = Dr
                DB::table('acct_tbl_transaction')->insert([
                    'VoucherID'     => $voucherId,
                    'AccountNo'     => $accountNo,
                    'DrAmount'      => 0,
                    'CrAmount'      => $amount,
                    'Balance'       => $amount,
                    'PostingDate'   => $date,
                    'Status'        => 'Authorized',
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
                DB::table('acct_tbl_transaction')->insert([
                    'VoucherID'     => $voucherId,
                    'AccountNo'     => $contraAccount,
                    'DrAmount'      => $amount,
                    'CrAmount'      => 0,
                    'Balance'       => $amount,
                    'PostingDate'   => $date,
                    'Status'        => 'Authorized',
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
            }

            // 3. Synchronise acct_tbl_balance (upsert by AccountNo)
            DB::table('acct_tbl_balance')->updateOrInsert(
                ['AccountNo' => $accountNo],
                ['Balance' => $amount, 'updated_at' => now()]
            );

            // 4. Contra account balance
            $contraNet = DB::table('acct_tbl_transaction')
                ->where('AccountNo', $contraAccount)
                ->where('Status', 'Authorized')
                ->selectRaw('SUM(DrAmount) - SUM(CrAmount) as net')
                ->value('net');

            DB::table('acct_tbl_balance')->updateOrInsert(
                ['AccountNo' => $contraAccount],
                ['Balance' => $contraNet ?? 0, 'updated_at' => now()]
            );

            // 5. Reference row for getOpeningBalance() fallback
            DB::table('acct_tbl_openning_balance')->insert([
                'AccountNo'   => $accountNo,
                'Balance'     => $amount,
                'Date'        => $date,
                'PostingDate' => $date,
                'AutoPost'    => 0,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        });

        return back()->with('success', 'Opening balance posted successfully.');
    }

    public function getGlCodeByAccount(Request $request)
    {
        $account = AcctTblBalsheetAccount::where('AccountNo', $request->input('OldAccountNo'))->first();
        $groups = AcctTblBalsheetGroup::where('gl_type', $account?->gl_type)->orderBy('gl_group_name')->get();
        return response()->view('accounting.options.groups', ['groups' => $groups]);
    }

    public function getSubGlCodeByGlCode(Request $request)
    {
        $groups = AcctTblBalsheetSubGroup::where('gl_group', $request->input('NewGlCode'))
            ->orderBy('gl_sub_group_name')->get();
        return response()->view('accounting.options.subgroups', ['groups' => $groups]);
    }

    public function changeAccountNo()
    {
        return view('accounting.change_account', [
            'accounts' => AcctTblBalsheetAccount::orderBy('title')->get(['AccountNo', 'title']),
        ]);
    }

    public function changeAccountNoAction(Request $request)
    {
        $data = $request->validate([
            'OldAccountNo' => ['required', 'exists:acct_tbl_balsheet_account,AccountNo'],
            
            'NewAccount'   => [
                'required', 'string', 'max:20', 'different:OldAccountNo',
                'unique:acct_tbl_balsheet_account,AccountNo',
            ],
            'NewGlCode'    => ['required', 'integer', 'exists:acct_tbl_balsheet_group,gl_group'],
            'NewSubGlCode' => ['required', 'integer', 'exists:acct_tbl_balsheet_sub_group,gl_sub_group'],
        ], [
            'NewAccount.unique'    => 'That account number is already in use.',
            'OldAccountNo.exists'  => 'The account being renamed does not exist.',
            'NewGlCode.exists'     => 'The selected GL group does not exist.',
            'NewSubGlCode.exists'  => 'The selected GL sub group does not exist.',
        ]);

        DB::transaction(function () use ($data) {
            $old = $data['OldAccountNo'];
            AcctTblBalsheetAccount::where('AccountNo', $old)->update([
                'AccountNo' => $data['NewAccount'], 'gl_group' => $data['NewGlCode'], 'gl_sub_group' => $data['NewSubGlCode'],
            ]);
         
            foreach ([
                'acct_tbl_balance',
                'acct_tbl_balance_tmp',
                'acct_tbl_transaction',
                'acct_tbl_transaction_tmp',
                'acct_tbl_openning_balance',
            ] as $table) {
                if (DB::getSchemaBuilder()->hasTable($table)) {
                    DB::table($table)->where('AccountNo', $old)->update(['AccountNo' => $data['NewAccount']]);
                }
            }
            if (DB::getSchemaBuilder()->hasTable('acct_tbl_unauth_transaction')) {
                DB::table('acct_tbl_unauth_transaction')->where('DrAccount', $old)->update(['DrAccount' => $data['NewAccount']]);
                DB::table('acct_tbl_unauth_transaction')->where('CrAccount', $old)->update(['CrAccount' => $data['NewAccount']]);
            }
        });
        return back()->with('success', 'Account number changed successfully.');
    }
}