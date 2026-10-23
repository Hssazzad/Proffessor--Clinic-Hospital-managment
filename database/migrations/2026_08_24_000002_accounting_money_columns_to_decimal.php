<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Money columns: double(50,2) -> DECIMAL(18,2).
 *
 * double is binary floating point, so 0.01 has no exact representation and
 * repeated addition of a running balance accumulates error. A measured drift of
 * 0.22 already existed across the ledger. DECIMAL stores the value exactly and
 * MySQL does the arithmetic in base 10, which removes the drift at the source.
 *
 * DECIMAL(18,2) holds up to 9,999,999,999,999,999.99 — four orders of magnitude
 * above the largest figure this system has ever recorded.
 *
 * Structure only. No row is read, written or deleted.
 */
return new class extends Migration
{
    /**
     * table => [column => nullability/default clause]
     */
    private const MONEY_COLUMNS = [
        'acct_tbl_transaction' => [
            'DrAmount' => 'NOT NULL DEFAULT 0.00',
            'CrAmount' => 'NOT NULL DEFAULT 0.00',
            'Balance'  => 'NOT NULL DEFAULT 0.00',
        ],
        'acct_tbl_transaction_tmp' => [
            'DrAmount' => 'NOT NULL DEFAULT 0.00',
            'CrAmount' => 'NOT NULL DEFAULT 0.00',
            'Balance'  => 'NOT NULL DEFAULT 0.00',
        ],
        'acct_tbl_unauth_transaction' => [
            'amount' => 'NOT NULL DEFAULT 0.00',
        ],
        'acct_tbl_balance' => [
            'Balance'    => 'NOT NULL DEFAULT 0.00',
            'Difference' => 'NOT NULL DEFAULT 0.00',
        ],
        'acct_tbl_balance_tmp' => [
            'Balance' => 'NOT NULL DEFAULT 0.00',
        ],
        'acct_tbl_openning_balance' => [
            'Balance' => 'NOT NULL DEFAULT 0.00',
        ],
        'acct_tbl_balsheet_account' => [
            'Balance' => 'NOT NULL DEFAULT 0.00',
        ],
        'acct_tbl_balsheet_sub_group' => [
            'Balance' => 'NOT NULL DEFAULT 0.00',
        ],
        'acct_tbl_balsheet_group' => [
            'Balance' => 'NOT NULL DEFAULT 0.00',
        ],
    ];

    public function up(): void
    {
        foreach (self::MONEY_COLUMNS as $table => $columns) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            foreach ($columns as $column => $clause) {
                if (! Schema::hasColumn($table, $column)) {
                    continue;
                }

                DB::statement("ALTER TABLE `{$table}` MODIFY `{$column}` DECIMAL(18,2) {$clause}");
            }
        }
    }

    /**
     * Deliberately empty.
     *
     * Reverting DECIMAL back to double would reintroduce the rounding error the
     * change exists to remove, and would silently alter stored values.
     */
    public function down(): void
    {
        // No-op by design. See the note above.
    }
};
