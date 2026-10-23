<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Key columns and indexes on the accounting tables.
 *
 * Three distinct problems, all structural:
 *
 * 1. acct_tbl_balance.AccountNo is double(25,0) while every other table stores
 *    AccountNo as varchar(20). Each join therefore compares a float to a string,
 *    which forces MySQL to coerce the varchar side, discards the index, and
 *    risks matching the wrong row once account numbers get long enough to lose
 *    precision in a double.
 *
 * 2. acct_tbl_balance has no UNIQUE constraint on AccountNo. The posting code
 *    relies on "one balance row per account" but nothing enforced it, so two
 *    concurrent authorizations could each insert a row and one balance would be
 *    invisible from then on.
 *
 * 3. RefNumber is varchar(10) in the temp and unauthorized tables but int(10)
 *    in the permanent one, and some reference numbers are not numeric at all
 *    ('INV-000123'), so authorizing one of those vouchers coerces its reference
 *    to 0. nextReferenceNumber() also has to CAST every value to UNSIGNED to
 *    compare them, and those lookups run without an index.
 *
 * Structure only. No row is read for its value, written or deleted. The one
 * pre-check below only counts rows, and it aborts rather than modify anything
 * if the data cannot satisfy the new constraint.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->convertBalanceAccountNo();
        $this->addBalanceColumnDefaults();
        $this->convertRefNumbers();
        $this->addIndexes();
    }

    /**
     * Deliberately empty.
     *
     * Reverting would drop the UNIQUE constraint that protects balance
     * integrity and put back a column type that mis-joins.
     */
    public function down(): void
    {
        // No-op by design. See the note above.
    }

    private function convertBalanceAccountNo(): void
    {
        if (! Schema::hasTable('acct_tbl_balance')) {
            return;
        }

        // Refuse rather than let ALTER TABLE fail halfway or silently merge rows.
        $duplicates = DB::table('acct_tbl_balance')
            ->select('AccountNo')
            ->groupBy('AccountNo')
            ->havingRaw('COUNT(*) > 1')
            ->count();

        if ($duplicates > 0) {
            throw new RuntimeException(
                "acct_tbl_balance has {$duplicates} AccountNo value(s) on more than one row. "
                . 'A UNIQUE index cannot be added until that is resolved. Resolving it means '
                . 'changing accounting data, which is out of scope for this migration - decide '
                . 'which row is authoritative first, then re-run.'
            );
        }

        DB::statement('ALTER TABLE `acct_tbl_balance` MODIFY `AccountNo` VARCHAR(20) NOT NULL');

        if (! $this->indexExists('acct_tbl_balance', 'uk_balance_account')) {
            DB::statement('ALTER TABLE `acct_tbl_balance` ADD UNIQUE INDEX `uk_balance_account` (`AccountNo`)');
        }
    }

    /**
     * VerifiedBalance and EventNo are NOT NULL with no default, so any insert
     * that supplies only AccountNo and Balance fails under strict sql_mode.
     * Giving them a default lets the posting code insert a balance row without
     * pretending to know values that belong to the legacy verification screen.
     */
    private function addBalanceColumnDefaults(): void
    {
        if (! Schema::hasTable('acct_tbl_balance')) {
            return;
        }

        foreach (['VerifiedBalance', 'EventNo'] as $column) {
            if (Schema::hasColumn('acct_tbl_balance', $column)) {
                DB::statement("ALTER TABLE `acct_tbl_balance` MODIFY `{$column}` INT(11) NOT NULL DEFAULT 0");
            }
        }
    }

    /**
     * RefNumber: one type across all three transaction tables.
     *
     * varchar wins over int, which is the opposite of what a "reference number"
     * suggests, because the reference numbers are not all numbers.
     * AccountingIntegrationService posts vouchers with RefNumber values like
     * 'INV-000123' and 'DUE-45' (see its postInvestigationInvoice() and
     * postInvestigationDuePayment()). The temp and unauthorized tables are
     * varchar(10) and hold them fine; the permanent table is int(11), so
     * authorizing one of those vouchers coerces the reference to 0 and the
     * posted row loses all trace of which invoice it came from.
     *
     * Widening the permanent column to varchar is lossless - every existing
     * integer keeps its digits - and it also repairs
     * AccountingIntegrationService::checkDuplicate(), whose
     * where('RefNumber', 'INV-000123') against an int column currently compares
     * against 0 and matches unrelated rows.
     *
     * 20 characters rather than 10: substr(...,0,10) in the integration service
     * is there only because the column is varchar(10), and 'INV-' plus a long
     * bill number already reaches the limit.
     */
    private function convertRefNumbers(): void
    {
        $tables = ['acct_tbl_transaction', 'acct_tbl_transaction_tmp', 'acct_tbl_unauth_transaction'];

        foreach ($tables as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'RefNumber')) {
                continue;
            }

            DB::statement("ALTER TABLE `{$table}` MODIFY `RefNumber` VARCHAR(20) NOT NULL");
        }
    }

    private function addIndexes(): void
    {
        $indexes = [
            // Voucher lookup during authorize / reject.
            'acct_tbl_transaction_tmp'    => ['idx_tmp_refnumber'     => '(`RefNumber`)'],
            'acct_tbl_unauth_transaction' => ['idx_unauth_refnumber'  => '(`RefNumber`)'],
            // Ledger walk and running-balance recalculation both read an account's
            // rows in (PostingDate, ID) order; this covers that exactly.
            'acct_tbl_transaction'        => ['idx_txn_account_date_id' => '(`AccountNo`, `PostingDate`, `ID`)'],
        ];

        foreach ($indexes as $table => $definitions) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            foreach ($definitions as $name => $columns) {
                if (! $this->indexExists($table, $name)) {
                    DB::statement("ALTER TABLE `{$table}` ADD INDEX `{$name}` {$columns}");
                }
            }
        }
    }

    private function indexExists(string $table, string $index): bool
    {
        return DB::table('information_schema.STATISTICS')
            ->where('TABLE_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', $table)
            ->where('INDEX_NAME', $index)
            ->exists();
    }
};
