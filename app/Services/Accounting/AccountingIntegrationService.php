<?php

namespace App\Services\Accounting;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Exception;

class AccountingIntegrationService
{
    // GL Accounts used by this clinic's chart of accounts.
    // They are posted as debit/credit lines for every invoice.
    public const GL_CASH = '205101101';
    public const GL_RECEIVABLE = '20701101';
    public const GL_INCOME = '40601101';
    public const GL_DISCOUNT = '51501101';

    /**
     * Chart-of-account metadata for each hardcoded GL account.
     * Used to AUTO-CREATE the account (plus its group/sub-group)
     * the first time it is needed, so nothing has to be seeded
     * manually on a fresh/live database.
     */
    private const GL_DEFINITIONS = [
        '205101101' => [
            'title' => 'Cash',
            'gl_type' => 2,
            'gl_group' => 205,
            'gl_group_name' => 'Cash',
            'gl_sub_group' => 205101,
            'gl_sub_group_name' => 'Cash Account',
        ],
        '20701101' => [
            'title' => 'Patient Accounts Receivable',
            'gl_type' => 2,
            'gl_group' => 207,
            'gl_group_name' => 'Receivable',
            'gl_sub_group' => 20701,
            'gl_sub_group_name' => 'Patient Accounts Receivable',
        ],
        '40601101' => [
            'title' => 'Investigation Income',
            'gl_type' => 4,
            'gl_group' => 406,
            'gl_group_name' => 'Laboratory Income',
            'gl_sub_group' => 40601,
            'gl_sub_group_name' => 'Lab Services',
        ],
        '51501101' => [
            'title' => 'Investigation Discount',
            'gl_type' => 5,
            'gl_group' => 515,
            'gl_group_name' => 'Discount Group',
            'gl_sub_group' => 51501,
            'gl_sub_group_name' => 'Investigation Discount',
        ],
    ];

    public function postInvestigationInvoice(object $invoice, string $paymentDate): void
    {
        // BillNo already contains 'INV-', so we just use it directly (max 10 chars)
        $refNumber = substr($invoice->BillNo, 0, 10);
        if ($this->checkDuplicate($refNumber)) return;

        $total = (float) ($invoice->TotalBill ?? 0);
        $discount = (float) ($invoice->Discount ?? 0);
        $paid = (float) ($invoice->PaidAmount ?? 0);
        $due = (float) ($invoice->DueAmount ?? 0);

        $debits = [];
        $credits = [];

        if ($paid > 0) $debits[] = ['account' => self::GL_CASH, 'amount' => $paid];
        if ($discount > 0) $debits[] = ['account' => self::GL_DISCOUNT, 'amount' => $discount];
        if ($due > 0) $debits[] = ['account' => self::GL_RECEIVABLE, 'amount' => $due];
        if ($total > 0) $credits[] = ['account' => self::GL_INCOME, 'amount' => $total];

        $this->createVoucher($debits, $credits, $refNumber, $paymentDate, "Investigation Invoice " . $invoice->BillNo, 'INV');
    }

    public function postInvestigationDuePayment(string $billNo, int $transactionId, float $payingAmount, string $paymentDate): void
    {
        if ($payingAmount <= 0) return;

        // acct_tbl_transaction_tmp.RefNumber is varchar(10)
        // We use DUE- followed by the transaction ID to fit within 10 characters.
        $refNumber = substr('DUE-' . $transactionId, 0, 10);
        if ($this->checkDuplicate($refNumber)) return;
       //code will get from database table.
        $debits = [['account' => self::GL_CASH, 'amount' => $payingAmount]];
        $credits = [['account' => self::GL_RECEIVABLE, 'amount' => $payingAmount]];

        $this->createVoucher($debits, $credits, $refNumber, $paymentDate, "Due Payment for Invoice " . $billNo, 'PAY');
    }

    private function createVoucher(array $debits, array $credits, string $refNumber, string $postingDate, string $particular, string $screenType): void
    {
        $totalDebit = 0;
        $totalCredit = 0;

        foreach ($debits as $dr) { $totalDebit += round($dr['amount'], 2); $this->ensureGlAccount($dr['account']); }
        foreach ($credits as $cr) { $totalCredit += round($cr['amount'], 2); $this->ensureGlAccount($cr['account']); }

        if ($totalDebit == 0 && $totalCredit == 0) throw new Exception("Accounting Error: Zero amounts for Ref: $refNumber");
        if (round($totalDebit, 2) !== round($totalCredit, 2)) throw new Exception("Accounting Error: Debit ($totalDebit) and Credit ($totalCredit) do not balance for Ref: $refNumber");

        $inputerId = (string) (Auth::id() ?? '');
        $now = now();

        foreach ($debits as $dr) {
            if (round($dr['amount'], 2) > 0) {
                DB::table('acct_tbl_transaction_tmp')->insert([
                    'AccountNo' => $dr['account'], 'TrType' => 'Debit', 'DrAmount' => round($dr['amount'], 2), 'CrAmount' => 0,
                    'Particular' => $particular, 'Balance' => $this->provisionalBalance($dr['account'], round($dr['amount'], 2), 'Debit'), 'PostingDate' => $postingDate, 'RefNumber' => $refNumber,
                    'ScreenType' => $screenType, 'FactoryCode' => null, 'EmployeeID' => '', 'InputerID' => $inputerId,
                    'AuthorizerID' => '', 'Timestamp' => $now
                ]);
            }
        }

        foreach ($credits as $cr) {
            if (round($cr['amount'], 2) > 0) {
                DB::table('acct_tbl_transaction_tmp')->insert([
                    'AccountNo' => $cr['account'], 'TrType' => 'Credit', 'DrAmount' => 0, 'CrAmount' => round($cr['amount'], 2),
                    'Particular' => $particular, 'Balance' => $this->provisionalBalance($cr['account'], round($cr['amount'], 2), 'Credit'), 'PostingDate' => $postingDate, 'RefNumber' => $refNumber,
                    'ScreenType' => $screenType, 'FactoryCode' => null, 'EmployeeID' => '', 'InputerID' => $inputerId,
                    'AuthorizerID' => '', 'Timestamp' => $now
                ]);
            }
        }

        DB::table('acct_tbl_unauth_transaction')->insert([
            'DrAccount' => $debits[0]['account'] ?? '', 'CrAccount' => $credits[0]['account'] ?? '',
            'Particular' => $particular, 'amount' => round($totalDebit, 2), 'FactoryCode' => null, 'EmployeeID' => '',
            'PostingDate' => $postingDate, 'RefNumber' => $refNumber, 'InputerID' => $inputerId, 'AuthorizerID' => '',
            'ScreenType' => $screenType, 'AutoPosting' => $now
        ]);
    }

    private function checkDuplicate(string $refNumber): bool
    {
        return DB::table('acct_tbl_transaction_tmp')->where('RefNumber', $refNumber)->exists() || DB::table('acct_tbl_transaction')->where('RefNumber', $refNumber)->exists();
    }

    /**
     * Provisional running balance for a temporary ledger line.
     *
     * DISPLAY ONLY. The row lands in acct_tbl_transaction_tmp, and
     * TransactionController::authorizeTransaction() recomputes the balance of
     * every affected row before anything reaches acct_tbl_transaction - so a
     * stale figure here cannot end up in the ledger.
     *
     * The sign convention used to be written out by hand here, in
     * TransactionController twice more, and once again in the report layer, with
     * two of the copies reversed. GlType::signedAmount() is now the only
     * definition. ensureGlAccount() has already run for this account by the time
     * createVoucher() calls us, so gl_type is guaranteed present.
     */
    private function provisionalBalance(string $accountNo, float $amount, string $trType): float
    {
        $glType = (int) (DB::table('acct_tbl_balsheet_account')->where('AccountNo', $accountNo)->value('gl_type') ?? 0);

        $isDebit = $trType === 'Debit';
        $delta = GlType::signedAmount($glType, $isDebit ? $amount : 0.0, $isDebit ? 0.0 : $amount);

        $currentBalance = (float) (DB::table('acct_tbl_balance')->where('AccountNo', $accountNo)->value('Balance') ?? 0);

        return round($currentBalance + $delta, 2);
    }

    /**
     * Makes sure the GL account exists in the chart of accounts.
     * If it is missing, it is created automatically together with
     * its group and sub-group — so no manual seeding is needed.
     */
    private function ensureGlAccount(string $accountNo): void
    {
        if (DB::table('acct_tbl_balsheet_account')->where('AccountNo', $accountNo)->exists()) {
            return;
        }

        $def = self::GL_DEFINITIONS[$accountNo] ?? null;
        if (!$def) {
            throw new Exception("Accounting Error: GL Account {$accountNo} does not exist and has no auto-create definition.");
        }

        DB::transaction(function () use ($accountNo, $def) {
            if (!DB::table('acct_tbl_balsheet_group')->where('gl_group', $def['gl_group'])->exists()) {
                DB::table('acct_tbl_balsheet_group')->insert([
                    'gl_type' => $def['gl_type'],
                    'gl_group' => $def['gl_group'],
                    'gl_group_name' => $def['gl_group_name'],
                    'tradingaccount' => 0,
                    'Balance' => 0,
                ]);
            }

            if (!DB::table('acct_tbl_balsheet_sub_group')->where('gl_sub_group', $def['gl_sub_group'])->exists()) {
                DB::table('acct_tbl_balsheet_sub_group')->insert([
                    'gl_type' => $def['gl_type'],
                    'gl_group' => $def['gl_group'],
                    'gl_sub_group' => $def['gl_sub_group'],
                    'gl_sub_group_name' => $def['gl_sub_group_name'],
                    'tradingaccount' => 0,
                    'Balance' => 0,
                ]);
            }

            if (!DB::table('acct_tbl_balsheet_account')->where('AccountNo', $accountNo)->exists()) {
                DB::table('acct_tbl_balsheet_account')->insert([
                    'AccountNo' => $accountNo,
                    'title' => $def['title'],
                    'gl_type' => $def['gl_type'],
                    'gl_group' => $def['gl_group'],
                    'gl_sub_group' => $def['gl_sub_group'],
                    'Balance' => 0,
                    'AccountType' => 0,
                    'Position' => 0,
                ]);
            }
        });

        \Log::info('Accounting: GL account auto-created', ['AccountNo' => $accountNo, 'title' => $def['title']]);
    }
}
?>