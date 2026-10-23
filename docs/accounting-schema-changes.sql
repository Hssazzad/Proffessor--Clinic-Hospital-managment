-- =====================================================================
-- Accounting schema changes  (database: profclinic)
--
-- STRUCTURE ONLY. এই script কোনো row-এর value পড়ে না, লেখে না, মোছে না।
-- কোনো balance recalculate করা হয় না, কোনো transaction তৈরি বা বদল হয় না।
--
-- চালানোর আগে অবশ্যই backup নিন:
--   mysqldump -u root -p profclinic > profclinic_backup.sql
--
-- STEP 0 আগে চালান এবং output দেখুন। STEP 0 ঠিক থাকলে STEP 1-5 চালান।
--
-- যে table আপনার DB-তে নেই, তার line-এ "Unknown table" error আসবে —
-- ওটা ক্ষতিকর নয়, ওই line skip করে পরেরটায় যান।
-- =====================================================================


-- =====================================================================
-- STEP 0 — PRE-CHECK  (শুধু পড়ে, কিছু বদলায় না)
-- =====================================================================

-- 0.1  gl_type 3 আসলে কী? Fix 6 (opening balance double-entry) এটার উপর আটকে আছে।
SELECT * FROM acct_gltype_info;

-- 0.2  acct_tbl_balance-এ একই AccountNo দুইবার আছে কিনা।
--      এখানে কোনো row ফেরত এলে STEP 2-এর UNIQUE INDEX চালাবেন না —
--      আগে ঠিক করতে হবে কোন row সঠিক (সেটা data change, আলাদা সিদ্ধান্ত)।
SELECT AccountNo, COUNT(*) AS times, GROUP_CONCAT(Balance) AS balances
FROM acct_tbl_balance
GROUP BY AccountNo
HAVING COUNT(*) > 1;

-- 0.3  AccountNo double(25,0) → VARCHAR(20) করার আগে দেখে নিন কোনো মান
--      20 character-এর বেশি নয়।
SELECT MAX(LENGTH(TRIM(AccountNo) + 0)) AS longest_account_no FROM acct_tbl_balance;

-- 0.4  RefNumber-এ non-numeric মান কত আছে (যেমন 'INV-000123')।
--      এগুলোই কারণ যে RefNumber কে INT না করে VARCHAR করা হচ্ছে।
SELECT 'tmp' AS src, COUNT(*) AS non_numeric FROM acct_tbl_transaction_tmp
  WHERE RefNumber NOT REGEXP '^[0-9]+$'
UNION ALL
SELECT 'unauth', COUNT(*) FROM acct_tbl_unauth_transaction
  WHERE RefNumber NOT REGEXP '^[0-9]+$';


-- =====================================================================
-- STEP 1 — টাকার column: double(50,2) → DECIMAL(18,2)
--
-- কারণ: double হলো binary floating point, তাই 0.01 হুবহু রাখা যায় না।
-- Running balance বারবার যোগ হতে হতে error জমে — আপনার ledger-এ এখনই
-- 0.22 drift মাপা গেছে। DECIMAL হুবহু রাখে, MySQL base-10 তে হিসাব করে।
--
-- DECIMAL(18,2) সর্বোচ্চ 9,999,999,999,999,999.99 ধরে — আপনার সবচেয়ে
-- বড় সংখ্যার চেয়ে ১০,০০০ গুণ বেশি জায়গা।
-- =====================================================================

ALTER TABLE `acct_tbl_transaction`        MODIFY `DrAmount`   DECIMAL(18,2) NOT NULL DEFAULT 0.00;
ALTER TABLE `acct_tbl_transaction`        MODIFY `CrAmount`   DECIMAL(18,2) NOT NULL DEFAULT 0.00;
ALTER TABLE `acct_tbl_transaction`        MODIFY `Balance`    DECIMAL(18,2) NOT NULL DEFAULT 0.00;

ALTER TABLE `acct_tbl_transaction_tmp`    MODIFY `DrAmount`   DECIMAL(18,2) NOT NULL DEFAULT 0.00;
ALTER TABLE `acct_tbl_transaction_tmp`    MODIFY `CrAmount`   DECIMAL(18,2) NOT NULL DEFAULT 0.00;
ALTER TABLE `acct_tbl_transaction_tmp`    MODIFY `Balance`    DECIMAL(18,2) NOT NULL DEFAULT 0.00;

ALTER TABLE `acct_tbl_unauth_transaction` MODIFY `amount`     DECIMAL(18,2) NOT NULL DEFAULT 0.00;

ALTER TABLE `acct_tbl_balance`            MODIFY `Balance`    DECIMAL(18,2) NOT NULL DEFAULT 0.00;
ALTER TABLE `acct_tbl_balance`            MODIFY `Difference` DECIMAL(18,2) NOT NULL DEFAULT 0.00;

ALTER TABLE `acct_tbl_balance_tmp`        MODIFY `Balance`    DECIMAL(18,2) NOT NULL DEFAULT 0.00;
ALTER TABLE `acct_tbl_openning_balance`   MODIFY `Balance`    DECIMAL(18,2) NOT NULL DEFAULT 0.00;
ALTER TABLE `acct_tbl_balsheet_account`   MODIFY `Balance`    DECIMAL(18,2) NOT NULL DEFAULT 0.00;
ALTER TABLE `acct_tbl_balsheet_sub_group` MODIFY `Balance`    DECIMAL(18,2) NOT NULL DEFAULT 0.00;
ALTER TABLE `acct_tbl_balsheet_group`     MODIFY `Balance`    DECIMAL(18,2) NOT NULL DEFAULT 0.00;


-- =====================================================================
-- STEP 2 — acct_tbl_balance এর key column
--
-- সমস্যা ১: AccountNo এখানে double(25,0), কিন্তু অন্য সব table-এ
--   varchar(20)। তাই প্রতিটা JOIN এ float-কে string-এর সাথে মেলানো হয় —
--   index কাজ করে না, আর account number লম্বা হলে double-এ precision
--   হারিয়ে ভুল row-ও মিলতে পারে।
--
-- সমস্যা ২: AccountNo-তে কোনো UNIQUE নেই। কোড ধরে নেয় "এক account = এক
--   balance row", কিন্তু কিছুই তা enforce করত না। দুইটা authorize একসাথে
--   হলে দুইটা row ঢুকে যেত, একটা balance চিরতরে অদৃশ্য হয়ে যেত।
--
-- সমস্যা ৩: VerifiedBalance আর EventNo হলো NOT NULL কিন্তু কোনো DEFAULT
--   নেই। তাই শুধু AccountNo + Balance দিয়ে insert করলে strict mode-এ fail
--   করে। DEFAULT 0 দিলে posting code balance row বসাতে পারে, অথচ legacy
--   verification screen-এর মান নিয়ে কিছু ধরে নিতে হয় না।
-- =====================================================================

-- ⚠ STEP 0.2 তে কোনো duplicate পাওয়া গেলে নিচের দুই line চালাবেন না।
ALTER TABLE `acct_tbl_balance` MODIFY `AccountNo` VARCHAR(20) NOT NULL;
ALTER TABLE `acct_tbl_balance` ADD UNIQUE INDEX `uk_balance_account` (`AccountNo`);

ALTER TABLE `acct_tbl_balance` MODIFY `VerifiedBalance` INT(11) NOT NULL DEFAULT 0;
ALTER TABLE `acct_tbl_balance` MODIFY `EventNo`         INT(11) NOT NULL DEFAULT 0;


-- =====================================================================
-- STEP 3 — RefNumber: তিন table-এ একই type
--
-- VARCHAR, INT নয় — এটা উল্টো মনে হতে পারে, কিন্তু reference number সব
-- সংখ্যা নয়। AccountingIntegrationService invoice voucher-এ RefNumber
-- হিসেবে 'INV-000123' আর 'DUE-45' লেখে।
--
-- tmp আর unauth table varchar(10), ওরা এগুলো ঠিকই রাখে। কিন্তু permanent
-- table int(11) — তাই ওই voucher authorize করলে reference 0 হয়ে যায় এবং
-- posted row থেকে বোঝার উপায় থাকে না কোন invoice থেকে এসেছে।
--
-- int → varchar করা lossless: প্রতিটা পুরনো সংখ্যা তার digit নিয়েই থাকে।
-- এটা AccountingIntegrationService::checkDuplicate() ও ঠিক করে, যেটা
-- এখন 'INV-000123' কে int column-এর সাথে মেলাতে গিয়ে 0-এর সাথে মেলায়
-- এবং সম্পর্কহীন row-কে duplicate ভেবে বসে।
--
-- 10 নয় 20 character: integration service-এ substr(...,0,10) আছে শুধু
-- কারণ column varchar(10); 'INV-' + লম্বা bill number এতেই সীমা ছুঁয়ে ফেলে।
-- =====================================================================

ALTER TABLE `acct_tbl_transaction`        MODIFY `RefNumber` VARCHAR(20) NOT NULL;
ALTER TABLE `acct_tbl_transaction_tmp`    MODIFY `RefNumber` VARCHAR(20) NOT NULL;
ALTER TABLE `acct_tbl_unauth_transaction` MODIFY `RefNumber` VARCHAR(20) NOT NULL;


-- =====================================================================
-- STEP 4 — Index
--
-- voucher authorize / reject এর সময় RefNumber দিয়ে খোঁজা হয়, index ছিল না।
-- আর ledger walk এবং running-balance recalculation দুইটাই একটা account-এর
-- row গুলো (PostingDate, ID) order-এ পড়ে — শেষ index-টা ঠিক সেটাই cover করে।
--
-- একই নামের index আগে থেকে থাকলে "Duplicate key name" error আসবে,
-- ওটা ক্ষতিকর নয় — মানে কাজটা আগেই হয়ে গেছে।
-- =====================================================================

ALTER TABLE `acct_tbl_transaction_tmp`    ADD INDEX `idx_tmp_refnumber`       (`RefNumber`);
ALTER TABLE `acct_tbl_unauth_transaction` ADD INDEX `idx_unauth_refnumber`    (`RefNumber`);
ALTER TABLE `acct_tbl_transaction`        ADD INDEX `idx_txn_account_date_id` (`AccountNo`, `PostingDate`, `ID`);


-- =====================================================================
-- STEP 5 — Charset: latin1_swedish_ci → utf8mb4_unicode_ci
--
-- Particular হলো free text আর ব্যবহারকারী ওখানে বাংলা লেখেন। latin1 বাংলা
-- রাখতে পারে না, তাই ঢোকার সময়ই অক্ষর নষ্ট হচ্ছে। বাকি পুরো application
-- utf8mb4 — মানে acct_* table আর non-accounting table-এর প্রতিটা JOIN এখন
-- দুইটা আলাদা collation মেলাচ্ছে।
--
-- CONVERT TO CHARACTER SET শুধু existing text-কে re-encode করে; কোন row
-- আছে বা কোনো সংখ্যার মান বদলায় না। আগেই latin1 round-trip এ যে text নষ্ট
-- হয়েছে সেটা নষ্টই থাকবে — ওটা সারাতে গেলে data rewrite করতে হবে, যা
-- এই কাজের বাইরে।
--
-- sql_mode নিয়ে: config/database.php তে 'strict' => true আগেই আছে, তাই
-- STRICT_TRANS_TABLES / NO_ZERO_DATE / NO_ZERO_IN_DATE ইতিমধ্যেই চালু।
-- '0000-00-00' PostingDate এর bug connection level-এ আগেই বন্ধ, এখানে
-- আলাদা কিছু করার দরকার নেই।
-- =====================================================================

ALTER TABLE `acct_tbl_transaction`        CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `acct_tbl_transaction_tmp`    CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `acct_tbl_unauth_transaction` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `acct_tbl_balance`            CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `acct_tbl_balance_tmp`        CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `acct_tbl_openning_balance`   CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `acct_tbl_balsheet_account`   CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `acct_tbl_balsheet_sub_group` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `acct_tbl_balsheet_group`     CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `acct_gltype_info`            CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;


-- =====================================================================
-- STEP 6 — যা হলো তা যাচাই  (শুধু পড়ে)
-- =====================================================================

-- সব টাকার column DECIMAL(18,2) হয়েছে কিনা, RefNumber varchar(20) হয়েছে কিনা।
SELECT TABLE_NAME, COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = 'profclinic'
  AND TABLE_NAME LIKE 'acct%'
  AND COLUMN_NAME IN ('DrAmount','CrAmount','Balance','amount','Difference',
                      'AccountNo','RefNumber','VerifiedBalance','EventNo')
ORDER BY TABLE_NAME, COLUMN_NAME;

-- নতুন index বসেছে কিনা।
SELECT TABLE_NAME, INDEX_NAME, NON_UNIQUE, GROUP_CONCAT(COLUMN_NAME ORDER BY SEQ_IN_INDEX) AS cols
FROM information_schema.STATISTICS
WHERE TABLE_SCHEMA = 'profclinic'
  AND INDEX_NAME IN ('uk_balance_account','idx_tmp_refnumber',
                     'idx_unauth_refnumber','idx_txn_account_date_id')
GROUP BY TABLE_NAME, INDEX_NAME, NON_UNIQUE;

-- সব acct_* table utf8mb4 হয়েছে কিনা।
SELECT TABLE_NAME, TABLE_COLLATION
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = 'profclinic' AND TABLE_NAME LIKE 'acct%'
ORDER BY TABLE_NAME;

-- Double-entry এখনো মেলে কিনা — এই script চালানোর আগে-পরে একই আসা উচিত।
SELECT ROUND(SUM(DrAmount), 2) AS total_dr,
       ROUND(SUM(CrAmount), 2) AS total_cr,
       ROUND(SUM(DrAmount) - SUM(CrAmount), 2) AS difference
FROM acct_tbl_transaction;
