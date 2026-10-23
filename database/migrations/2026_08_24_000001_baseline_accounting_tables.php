<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Baseline capture of the legacy accounting tables.
 *
 * The acct_* tables were created by hand during the CodeIgniter 3 era and have
 * never been under Laravel migration control. Nothing can be altered safely
 * until the starting shape is recorded, so this migration reproduces the exact
 * DDL that was in production and creates each table only if it is missing.
 *
 * Structure only. No data is inserted, updated or deleted here or in any of
 * the accounting migrations that follow.
 *
 * SCOPE LIMIT: only the four transaction/balance tables are captured. The
 * chart-of-accounts tables (acct_tbl_balsheet_group, acct_tbl_balsheet_sub_group,
 * acct_tbl_balsheet_account, acct_tbl_openning_balance, acct_gltype_info,
 * acct_tbl_balance_tmp) were not present in the backup that was available, and
 * their DDL has NOT been guessed. On a fresh environment they must be imported
 * from a dump of the live database before these migrations are run. Every later
 * accounting migration guards its changes with hasTable()/hasColumn() so they
 * are all safe to run whether or not those tables exist yet.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Raw legacy DDL below is MySQL-specific (AUTO_INCREMENT/ENGINE=InnoDB).
        // On non-MySQL drivers (e.g. sqlite test DBs) skip the capture; every
        // later accounting migration guards its changes with hasTable()/hasColumn().
        $driver = DB::connection()->getDriverName();
        if (! in_array($driver, ['mysql', 'mariadb'], true)) {
            return;
        }

        if (! Schema::hasTable('acct_tbl_transaction')) {
            DB::statement(<<<'SQL'
                CREATE TABLE `acct_tbl_transaction` (
                  `ID` int(11) NOT NULL AUTO_INCREMENT,
                  `AccountNo` varchar(20) NOT NULL,
                  `TrType` varchar(11) NOT NULL,
                  `Particular` text NOT NULL,
                  `DrAmount` double(50,2) NOT NULL DEFAULT 0.00,
                  `CrAmount` double(50,2) NOT NULL DEFAULT 0.00,
                  `Balance` double(50,2) NOT NULL,
                  `PostingDate` date NOT NULL,
                  `RefNumber` int(10) NOT NULL,
                  `MatGroupNo` int(11) NOT NULL DEFAULT 0,
                  `InputerID` varchar(20) NOT NULL,
                  `AuthorizerID` varchar(20) NOT NULL,
                  `EmployeeID` varchar(50) DEFAULT NULL,
                  `FactoryCode` varchar(20) DEFAULT NULL,
                  `SupplierCode` varchar(20) NOT NULL,
                  `Serial` varchar(20) DEFAULT NULL,
                  `ScreenType` varchar(11) DEFAULT NULL,
                  `Timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
                  PRIMARY KEY (`ID`),
                  KEY `AccountNo` (`AccountNo`),
                  KEY `PostingDate` (`PostingDate`)
                ) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci
            SQL);
        }

        if (! Schema::hasTable('acct_tbl_transaction_tmp')) {
            DB::statement(<<<'SQL'
                CREATE TABLE `acct_tbl_transaction_tmp` (
                  `ID` int(11) NOT NULL AUTO_INCREMENT,
                  `AccountNo` varchar(20) NOT NULL,
                  `TrType` varchar(11) NOT NULL,
                  `Particular` text NOT NULL,
                  `DrAmount` double(50,2) NOT NULL,
                  `CrAmount` double(50,2) NOT NULL,
                  `Balance` double(50,2) NOT NULL,
                  `PostingDate` date NOT NULL,
                  `FactoryCode` varchar(15) DEFAULT NULL,
                  `EmployeeID` varchar(50) NOT NULL,
                  `RefNumber` varchar(10) NOT NULL,
                  `MatGroupNo` int(11) NOT NULL DEFAULT 0,
                  `InputerID` varchar(20) NOT NULL,
                  `AuthorizerID` varchar(20) NOT NULL,
                  `Serial` varchar(20) DEFAULT NULL,
                  `MoNumber` varchar(20) DEFAULT '',
                  `ScreenType` varchar(11) DEFAULT NULL,
                  `Timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
                  PRIMARY KEY (`ID`)
                ) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci
            SQL);
        }

        if (! Schema::hasTable('acct_tbl_unauth_transaction')) {
            DB::statement(<<<'SQL'
                CREATE TABLE `acct_tbl_unauth_transaction` (
                  `ID` int(11) NOT NULL AUTO_INCREMENT,
                  `DrAccount` varchar(20) NOT NULL,
                  `CrAccount` varchar(11) NOT NULL,
                  `Particular` text NOT NULL,
                  `amount` double(50,2) NOT NULL,
                  `FactoryCode` varchar(15) DEFAULT NULL,
                  `EmployeeID` varchar(50) NOT NULL,
                  `PostingDate` date NOT NULL,
                  `RefNumber` varchar(10) NOT NULL,
                  `InputerID` varchar(20) NOT NULL,
                  `AuthorizerID` varchar(20) NOT NULL,
                  `Serial` varchar(20) DEFAULT NULL,
                  `MoNumber` varchar(50) DEFAULT NULL,
                  `ScreenType` varchar(5) NOT NULL,
                  `AutoPosting` timestamp NOT NULL DEFAULT current_timestamp(),
                  PRIMARY KEY (`ID`)
                ) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci
            SQL);
        }

        if (! Schema::hasTable('acct_tbl_balance')) {
            DB::statement(<<<'SQL'
                CREATE TABLE `acct_tbl_balance` (
                  `ID` int(11) NOT NULL AUTO_INCREMENT,
                  `AccountNo` double(25,0) NOT NULL,
                  `Balance` double(50,2) NOT NULL,
                  `VerifiedBalance` int(11) NOT NULL,
                  `Difference` double(50,2) NOT NULL,
                  `EventNo` int(11) NOT NULL,
                  PRIMARY KEY (`ID`)
                ) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci
            SQL);
        }
    }

    /**
     * Deliberately empty.
     *
     * Rolling back would have to DROP live accounting tables, which is exactly
     * the destructive data operation these changes are forbidden from doing.
     */
    public function down(): void
    {
        // No-op by design. See the note above.
    }
};
