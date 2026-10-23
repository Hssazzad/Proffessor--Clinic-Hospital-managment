<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Accounting tables: latin1_swedish_ci -> utf8mb4_unicode_ci.
 *
 * Particular is free text and users type Bengali into it. latin1 cannot store
 * Bengali, so those characters are already being mangled on the way in. The
 * rest of the application is utf8mb4, which also means every join between an
 * acct_* table and a non-accounting table currently compares across two
 * collations.
 *
 * Structure only. CONVERT TO CHARACTER SET re-encodes existing text in place;
 * it does not change which rows exist or any numeric value. Text already
 * corrupted by the latin1 round-trip stays corrupted - repairing it would mean
 * rewriting data, which is out of scope.
 *
 * Note on sql_mode: config/database.php already sets 'strict' => true on the
 * mysql connection, which enables STRICT_TRANS_TABLES, NO_ZERO_DATE and
 * NO_ZERO_IN_DATE. The '0000-00-00' PostingDate class of bug is therefore
 * already blocked at the connection level and needs no DDL here.
 */
return new class extends Migration
{
    private const TABLES = [
        'acct_tbl_transaction',
        'acct_tbl_transaction_tmp',
        'acct_tbl_unauth_transaction',
        'acct_tbl_balance',
        'acct_tbl_balance_tmp',
        'acct_tbl_openning_balance',
        'acct_tbl_balsheet_account',
        'acct_tbl_balsheet_sub_group',
        'acct_tbl_balsheet_group',
        'acct_gltype_info',
    ];

    public function up(): void
    {
        foreach (self::TABLES as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            DB::statement("ALTER TABLE `{$table}` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        }
    }

    /**
     * Deliberately empty.
     *
     * Converting back to latin1 would destroy any multi-byte character stored
     * after this migration ran.
     */
    public function down(): void
    {
        // No-op by design. See the note above.
    }
};
