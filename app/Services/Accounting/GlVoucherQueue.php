<?php

declare(strict_types=1);

namespace App\Services\Accounting;

use App\Models\Accounting\AcctTblBalsheetAccount;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Shared double-entry posting primitives for the manual voucher screens
 * (Cash Payment / Receive, Bank Payment / Receive, Transfer Posting, OT Collection).
 *
 * WHY THIS EXISTS
 * ---------------
 * Reference numbers must be unique across EVERY screen that posts to
 * acct_tbl_transaction, because that column is what links a voucher to its
 * business record. Two screens each rolling their own "max + 1" is how a
 * RefNumber ends up on two different vouchers. So the allocator and the
 * provisional-balance maths live here once, and every screen calls in.
 *
 * The flow is unchanged from the original screens: a voucher is written to
 * acct_tbl_transaction_tmp (two legs) plus one acct_tbl_unauth_transaction
 * header, and TransactionController::authorizeTransaction() later moves it to
 * the authorized ledger with a full balance recalculation.
 *
 * Every method expects to be called from inside a DB::transaction().
 */
class GlVoucherQueue
{
    private const TBL_ACCOUNT     = 'acct_tbl_balsheet_account';
    private const TBL_BALANCE     = 'acct_tbl_balance';
    private const TBL_TRAN_TMP    = 'acct_tbl_transaction_tmp';
    private const TBL_UNAUTH_TRAN = 'acct_tbl_unauth_transaction';
    private const TBL_TRANSACTION = 'acct_tbl_transaction';

    /**
     * Next free RefNumber across every posting table, so no two vouchers —
     * on any screen — can ever share one.
     *
     * Non-numeric RefNumbers (e.g. the "DUE-<id>" / "INV-<id>" strings the
     * billing module writes) are skipped by the REGEXP guard.
     */
    public function nextReferenceNumber(): int
    {
        $permanentMax = (int) (DB::table(self::TBL_TRANSACTION)
            ->whereRaw('RefNumber REGEXP ?', ['^[0-9]+$'])
            ->orderByRaw('CAST(RefNumber AS UNSIGNED) DESC')
            ->lockForUpdate()
            ->value('RefNumber') ?? 0);

        $temporaryMax = (int) (DB::table(self::TBL_TRAN_TMP)
            ->whereRaw('RefNumber REGEXP ?', ['^[0-9]+$'])
            ->selectRaw('MAX(CAST(RefNumber AS UNSIGNED)) as max_ref')
            ->value('max_ref') ?? 0);

        $unauthorizedMax = (int) (DB::table(self::TBL_UNAUTH_TRAN)
            ->whereRaw('RefNumber REGEXP ?', ['^[0-9]+$'])
            ->selectRaw('MAX(CAST(RefNumber AS UNSIGNED)) as max_ref')
            ->value('max_ref') ?? 0);

        return max($permanentMax, $temporaryMax, $unauthorizedMax) + 1;
    }

    /**
     * Provisional balance shown on the unauthorized voucher.
     *
     * DISPLAY ONLY. This value is written to acct_tbl_transaction_tmp.Balance so
     * the review screen can show the operator what the account would look like,
     * and authorizeTransaction() recomputes it from scratch before anything
     * reaches acct_tbl_transaction.
     */
    public function provisionalBalance(string $accountNo, float $drAmount, float $crAmount): float
    {
        $glType = (int) AcctTblBalsheetAccount::where('AccountNo', $accountNo)->value('gl_type');

        $currentBalance = (float) (DB::table(self::TBL_BALANCE)
            ->where('AccountNo', $accountNo)
            ->value('Balance') ?? 0);

        return round($currentBalance + GlType::signedAmount($glType, $drAmount, $crAmount), 2);
    }

    /**
     * Queue one balanced voucher: one Debit leg + one Credit leg in
     * acct_tbl_transaction_tmp, plus the acct_tbl_unauth_transaction header
     * that the Authorize screen reads.
     *
     * @param array  $extra Optional EmployeeID / FactoryCode / Serial for the
     *                      operator and voucher reference fields.
     * @return int   The allocated RefNumber — the caller stores this on its own
     *               business record to link the voucher back to it.
     */
    public function queueVoucher(
        string $debitAccount,
        string $creditAccount,
        float $amount,
        string $particular,
        string $postingDate,
        string $screenType,
        array $extra = []
    ): int {
        $refNumber  = $this->nextReferenceNumber();
        $inputerId  = (string) (Auth::id() ?? '');
        $employeeId = (string) ($extra['EmployeeID'] ?? '');
        $factoryCode = (string) ($extra['FactoryCode'] ?? '');
        $serial     = (string) ($extra['Serial'] ?? '');

        $shared = [
            'Particular'   => $particular,
            'PostingDate'  => $postingDate,
            'RefNumber'    => (string) $refNumber,
            'ScreenType'   => $screenType,
            'FactoryCode'  => $factoryCode,
            'EmployeeID'   => $employeeId,
            'MoNumber'     => $serial,
            'InputerID'    => $inputerId,
            'AuthorizerID' => '',
        ];

        $debitLeg = array_merge($shared, [
            'AccountNo' => (string) $debitAccount,
            'TrType'    => 'Debit',
            'DrAmount'  => $amount,
            'CrAmount'  => 0,
            'Balance'   => $this->provisionalBalance((string) $debitAccount, $amount, 0.0),
        ]);

        $creditLeg = array_merge($shared, [
            'AccountNo' => (string) $creditAccount,
            'TrType'    => 'Credit',
            'DrAmount'  => 0,
            'CrAmount'  => $amount,
            'Balance'   => $this->provisionalBalance((string) $creditAccount, 0.0, $amount),
        ]);

        $unauthHeader = [
            'DrAccount'    => (string) $debitAccount,
            'CrAccount'    => (string) $creditAccount,
            'Particular'   => $particular,
            'amount'       => $amount,
            'FactoryCode'  => $factoryCode,
            'EmployeeID'   => $employeeId,
            'PostingDate'  => $postingDate,
            'RefNumber'    => (string) $refNumber,
            'InputerID'    => $inputerId,
            'AuthorizerID' => '',
            'Serial'       => $serial,
            'MoNumber'     => $serial,
            'ScreenType'   => $screenType,
        ];

        DB::table(self::TBL_TRAN_TMP)->insert($debitLeg);
        DB::table(self::TBL_TRAN_TMP)->insert($creditLeg);
        DB::table(self::TBL_UNAUTH_TRAN)->insert($unauthHeader);

        return $refNumber;
    }
}