# Accounting System — Code Fix Specification

**Status:** Ready for implementation
**Scope:** Code fixes + DDL on the empty live database. No data migration.
**All file paths and line numbers in this document were verified against the repository.**

---

## 0. PRE-FLIGHT CHECK (do this before writing any code)

Two facts must be confirmed. Both change the scope of the work.

### 0.1 Confirm the live database is empty

```sql
SELECT COUNT(*) AS txn_count, COALESCE(SUM(DrAmount), 0) AS total_dr
FROM acct_tbl_transaction;
```

- `txn_count = 0` → this specification is valid as written.
- `txn_count > 0` → **STOP.** This is not a fresh database. Report back before proceeding.
  Code-only fixes cannot repair balances already written. An audit of a prior database found
  1,258 broken balance links across 55 accounts — those do not self-heal.

### 0.2 Confirm the meaning of `gl_type = 3`

```sql
SELECT * FROM acct_gltype_info;
SELECT gl_type, COUNT(*) FROM acct_tbl_balsheet_group GROUP BY gl_type;
```

The codebase contains exactly one reference to `gl_type 3`, a comment at
`app/Http/Controllers/AccountingController.php:30` reading `// 3 Off Balance`.
There is no Equity handling anywhere in the code.

This matters because `balanceSheetData()` builds
Assets from `gl_type IN (2,5)` and Liabilities from `gl_type IN (1,4)` —
**`gl_type 3` is excluded from the balance sheet entirely.**

Report which of these is true before implementing Fix #6:

| Case | Action |
|---|---|
| `gl_type 3` is Equity | Fix #6 proceeds; `balanceSheetData()` must be extended to include `gl_type 3` in the Equity section |
| `gl_type 3` is Off-Balance / unused | Fix #6 needs a different contra strategy — do not post opening balances to a type-3 account. Escalate for a decision. |

Note: `399999999` is **not** a valid account number in this system.
`nextAccountNo()` generates `<gl_sub_group> + '101'` (e.g. `205101101`).
A well-formed type-3 account looks like `3xxxxx101`.

---

## 1. Context

- Live database is separate and empty — no transactions yet (verify per §0.1).
- The prior/test database is not production.
- **Task: code fixes + one-time DDL on the empty schema. No data migration, no recalculation
  scripts, no manual balance updates.**

## 2. Objective

When the live database begins receiving real transactions, the following must calculate
correctly from transaction #1:

Income, Expense, Asset, Liability, Equity, Ledger, Trial Balance, Balance Sheet,
All Account Balance.

---

## 3. HARD RULES (non-negotiable)

1. Do NOT modify existing accounting data in any database.
2. Do NOT write balance-recalculation or data-cleanup scripts.
3. Do NOT update, delete, or backfill existing transaction rows.
4. Do NOT create correcting or adjusting transactions.
5. Do NOT change existing balances manually.
6. Do NOT rewrite the double-entry posting engine. It is verified correct
   (audit result: `SUM(DrAmount) = SUM(CrAmount) = 509,570,293.30`, difference `0.00`,
   0 unbalanced vouchers out of 8,941).
7. **DDL on the empty live schema IS allowed and required** (see Phase 0).
   Schema changes are free while the database is empty and expensive afterwards.
   This is the only exception to "no database changes" — it alters structure, never data.
8. Make the smallest safe code change that fixes the stated bug.
9. Do not touch unrelated modules.
10. These `acct_*` tables have no existing Laravel migrations — they were carried over from a
    legacy CodeIgniter 3 system. Capture the current schema in a baseline migration before
    altering it.

---

## 4. ACCOUNTING RULES (apply everywhere)

| GL Type | Meaning | Debit | Credit | Balance Formula |
|---|---|---:|---:|---|
| 1 | Liability | − | + | OB + Cr − Dr |
| 2 | Asset | + | − | OB + Dr − Cr |
| 3 | *see §0.2* | − | + | OB + Cr − Dr |
| 4 | Income | − | + | OB + Cr − Dr |
| 5 | Expense | + | − | OB + Dr − Cr |

The leading digit of `AccountNo` **is** the `gl_type`, by construction:
`nextGlGroup()` → `($glType * 100) + 1`; `nextGlSubGroup()` → `($gl_group * 100) + 1`;
`nextAccountNo()` → `$gl_sub_group . '101'`.
Example: `205101101` → `gl_type 2` → Asset (this is the hardcoded Cash account,
`TransactionController::CASH_ACCOUNT_NO`).

`calculateRunningBalance()` (`TransactionController.php:1007`) already encodes this correctly:
it inverts the sign for `gl_type` 2 and 5 only. **This function is the reference implementation
for the sign convention — match it everywhere else.**

**Architecture rule:** Transaction amounts → accounting calculation → running balance →
account balance → reports.
**Never read a stored `Balance` value as the input to the next balance calculation.**

---

## 5. IMPLEMENTATION ORDER (mandatory)

Later phases depend on earlier ones. Do not reorder.

### Phase 0 — Schema (do first; free while the database is empty)

| # | Fix | Why now |
|---|---|---|
| 0.1 | Money columns → `DECIMAL(18,2)` | Only chance to do it without a data migration |
| 0.2 | `acct_tbl_balance.AccountNo` → `VARCHAR(20)` + `UNIQUE` | Type alignment; prevents double-counting |
| 0.3 | `RefNumber` type alignment + indexes | Correctness at the temp→permanent boundary; performance |
| 0.4 | `latin1` → `utf8mb4` | Bengali text support |

### Phase 1 — Foundation

| # | Fix | Why in this position |
|---|---|---|
| 4 | GL type mapping (Asset/Liability swap) | Every other calculation depends on reading `gl_type` correctly |
| 5 | Opening balance sign | Depends on #4 |
| 6 | Initial balance posting | Depends on #5; blocked on §0.2 decision |
| 1 | Running balance calculation | Core engine |
| 2 | Backdated transaction handling | Depends on #1 |
| 3 | `acct_tbl_balance` sync | Depends on #1 and #2 |

### Phase 2 — Integrity and reports

| # | Fix | Impact |
|---|---|---|
| 7 | Expenditure statement | Currently renders the Income Statement |
| 8 | Amount validation | Blocks 0 and negative amounts |
| 8b | Sign field whitelist | Blocks arbitrary client-supplied sign values |
| 8c | PostingDate validation | Blocks zero-dates and absurd dates |
| 9 | Dr ≠ Cr check | Prevents same-account debit and credit |
| 10 | AccountNo change safety | Prevents duplicates and orphaned references |
| 11 | Account creation validation | Unique AccountNo on create |
| 12 | Account deletion safety | Blocks delete when references exist |
| 13 | Sub-group code change cascade | Prevents orphaned accounts |

---

## 6. PHASE 0 — SCHEMA FIXES

Write these as Laravel migrations. Run against the **empty** live database only.

### 0.1 Money columns → `DECIMAL(18,2)`

All money columns are currently `double(50,2)`. `double` is a binary float —
`0.1 + 0.2 != 0.3`. An audit of the prior database measured real drift of `0.22`
on reconciled accounts. No amount of application code can prevent this.

```sql
ALTER TABLE acct_tbl_transaction
  MODIFY COLUMN DrAmount DECIMAL(18,2) NOT NULL DEFAULT 0.00,
  MODIFY COLUMN CrAmount DECIMAL(18,2) NOT NULL DEFAULT 0.00,
  MODIFY COLUMN Balance  DECIMAL(18,2) NOT NULL DEFAULT 0.00;

ALTER TABLE acct_tbl_transaction_tmp
  MODIFY COLUMN DrAmount DECIMAL(18,2) NOT NULL DEFAULT 0.00,
  MODIFY COLUMN CrAmount DECIMAL(18,2) NOT NULL DEFAULT 0.00,
  MODIFY COLUMN Balance  DECIMAL(18,2) NOT NULL DEFAULT 0.00;

ALTER TABLE acct_tbl_unauth_transaction
  MODIFY COLUMN amount DECIMAL(18,2) NOT NULL DEFAULT 0.00;

ALTER TABLE acct_tbl_balance
  MODIFY COLUMN Balance    DECIMAL(18,2) NOT NULL DEFAULT 0.00,
  MODIFY COLUMN Difference DECIMAL(18,2) NOT NULL DEFAULT 0.00;
```

Also check `acct_tbl_openning_balance` and the `Balance` columns on
`acct_tbl_balsheet_group` / `_sub_group` / `_account` and apply the same change.
(These tables were not available for inspection — confirm their DDL first.)

In PHP, use string/`bcmath` or integer paisa for arithmetic on these values, or at minimum
`round(..., 2)` at every boundary. Do not let PHP floats accumulate.

### 0.2 `acct_tbl_balance` — type alignment and uniqueness

Current state: `AccountNo double(25,0)`, and `PRIMARY KEY (ID)` is the **only** key —
there is no index on `AccountNo` at all. Every other table uses `varchar(20)`.

A `varchar`↔`double` join forces numeric conversion and prevents index use.
No duplicates exist today (verified: 258 rows, 258 distinct), but nothing prevents them, and
both `initialBalanceAction()` and `changeAccountNoAction()` write without a uniqueness check.
One duplicate row makes every `SUM(b.Balance)` report double-count.

```sql
ALTER TABLE acct_tbl_balance
  MODIFY COLUMN AccountNo VARCHAR(20) NOT NULL;
ALTER TABLE acct_tbl_balance
  ADD UNIQUE INDEX uk_balance_account (AccountNo);
```

Then change all balance writes to `upsert` / `updateOrInsert` keyed on `AccountNo`.

### 0.3 `RefNumber` type alignment and missing indexes

```
acct_tbl_transaction.RefNumber         int(10)      <- permanent
acct_tbl_transaction_tmp.RefNumber     varchar(10)  <- mismatch
acct_tbl_unauth_transaction.RefNumber  varchar(10)  <- mismatch
```

This mismatch is why `nextReferenceNumber()` (`TransactionController.php:1055`) needs
`CAST(RefNumber AS UNSIGNED)`.

`acct_tbl_transaction_tmp` and `acct_tbl_unauth_transaction` both have only `PRIMARY KEY (ID)`,
yet `authorizeTransaction()` and `deleteUnauthTransaction()` both run
`whereIn('RefNumber', ...)` against them — a full table scan on every authorization.

```sql
ALTER TABLE acct_tbl_transaction_tmp
  MODIFY COLUMN RefNumber INT UNSIGNED NOT NULL,
  ADD INDEX idx_tmp_refnumber (RefNumber);

ALTER TABLE acct_tbl_unauth_transaction
  MODIFY COLUMN RefNumber INT UNSIGNED NOT NULL,
  ADD INDEX idx_unauth_refnumber (RefNumber);

-- Every ledger query sorts by exactly this tuple. The existing separate
-- KEY(AccountNo) and KEY(PostingDate) cannot serve it.
ALTER TABLE acct_tbl_transaction
  ADD INDEX idx_txn_account_date_id (AccountNo, PostingDate, ID);
ALTER TABLE acct_tbl_transaction_tmp
  ADD INDEX idx_tmp_account_date_id (AccountNo, PostingDate, ID);
```

Minor consistency cleanup in `acct_tbl_unauth_transaction`:
`CrAccount varchar(11)` should be `varchar(20)` to match `DrAccount`;
`ScreenType varchar(5)` should be `varchar(11)` to match the other tables.

### 0.4 Charset

All four tables are `CHARSET=latin1 COLLATE=latin1_swedish_ci`, including
`Particular text`. Current data is ASCII so nothing is corrupted yet, but any Bengali text
typed into a voucher narration will be stored as mojibake.

```sql
ALTER TABLE acct_tbl_transaction         CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE acct_tbl_transaction_tmp     CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE acct_tbl_unauth_transaction  CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE acct_tbl_balance             CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Also enable strict dates so `'0000-00-00'` can never be inserted
(the prior database has 8 such rows):

```sql
-- verify current setting, then set in my.cnf / RDS parameter group
SELECT @@sql_mode;
-- required: STRICT_TRANS_TABLES, NO_ZERO_DATE, NO_ZERO_IN_DATE
```

---

## 7. PHASE 1 — FOUNDATION FIXES

### Fix 4 — GL type mapping (Asset / Liability reversed)

`gl_type 1` is labelled Asset and `2` Liability in three places. This is backwards.
`balanceSheetData()` has it right; everything else contradicts it.

| Location | Current | Required |
|---|---|---|
| `app/Http/Controllers/AccountingController.php:29-34` | `$assets = gl_type 1`, `$liabilities = gl_type 2` | swap; also fix the comment on lines 29-30 |
| `app/Http/Controllers/TransactionController.php:417` | `['1'=>'Asset','2'=>'Liability', ...]` | `['1'=>'Liability','2'=>'Asset', ...]` |
| `resources/views/transaction/allaccountbalance.blade.php:21-22` | `<option value="1">Asset</option>` | swap the two option labels |

Reference implementation to match: `AccountingController::balanceSheetData()` at line 88
(`$liabilities = $groups(1); $assets = $groups(2);` and
`whereIn('g.gl_type', [1,4])` for liability, `[2,5]` for asset).

Grep for any other `gl_type` label mapping before declaring this done.

### Fix 5 — Opening balance sign

**File:** `app/Http/Controllers/TransactionController.php`
**Function:** `getOpeningBalance()` — **line 680**

Current code applies one formula to every account type:

```php
->selectRaw('COALESCE(SUM(CrAmount) - SUM(DrAmount), 0) as bal')
```

Required:

- `gl_type` 2 (Asset) and 5 (Expense): `SUM(Dr) - SUM(Cr)`
- `gl_type` 1 (Liability), 3, 4 (Income): `SUM(Cr) - SUM(Dr)`

Reuse the sign logic from `calculateRunningBalance()` (line 1007) rather than duplicating it —
extract it into a shared private helper so there is one definition of the sign convention.

This function also never consults `acct_tbl_openning_balance`. It must.
Note that `allAccountBalanceRows()` (line 443) **does** fall back to that table — the two
functions currently disagree, which is why the ledger opening balance and the
All Account Balance report show different numbers for the same account.
Both must call the same helper.

The raw value is printed unguarded at
`resources/views/transaction/showledgerstatement.blade.php:6`, so the wrong sign is visible
directly to the user.

### Fix 6 — Initial balance posting

**File:** `app/Http/Controllers/AccountingController.php`
**Function:** `initialBalanceAction()` — **line 548**

**Blocked on the §0.2 decision.** Do not implement until `gl_type 3` is confirmed.

Current bugs:
- One-sided entry — no contra leg, so the trial balance is unbalanced from the first entry.
- Inserts into `acct_tbl_balance` only when no row exists; never updates an existing row.
- No `gl_type` sign handling.
- `authorizeTransaction()` (line 917) reads the opening balance as `0.0` for any account with
  no prior transaction row, so the opening balance is silently discarded on the first posting.

Required:
- Post a proper two-legged authorized voucher.
- Asset/Expense opening balance: Dr the account, Cr the contra account.
- Liability/Income opening balance: Cr the account, Dr the contra account.
- `updateOrInsert` on `acct_tbl_balance` keyed by `AccountNo` (depends on Fix 0.2).
- Write the matching row to `acct_tbl_openning_balance`.
- `authorizeTransaction()` must seed the running balance from
  `acct_tbl_openning_balance` when no prior transaction row exists.

If `gl_type 3` turns out to be Equity, `balanceSheetData()` must also be extended to render an
Equity section — it currently drops `gl_type 3` entirely.

### Fix 1 — Running balance calculation

**File:** `app/Http/Controllers/TransactionController.php`
**Functions:** `authorizeTransaction()` (line 857), `calculateRunningBalance()` (line 1007)

Bug: line 911-915 seeds the running balance from
`orderBy('PostingDate','desc')->orderBy('ID','desc')->first()` — the **last** row by date.
Reports read in `ASC` order. When a backdated row exists, the seed is taken from the wrong row.
Line 917 then defaults to `0.0`, ignoring any opening balance.

Required:
- Compute strictly in `PostingDate ASC, ID ASC` order.
- Seed from the opening balance (Fix 5), not from `0.0`.
- Apply the `gl_type` sign convention via the shared helper.
- Store the computed result. Never feed a stored `Balance` back in as the previous value.

Note that `calculateBalance()` (line 715) computes the temp-row `Balance` from a
**client-supplied** `$sign`, and `authorizeTransaction()` line 933 recomputes and overwrites it
anyway. The temp `Balance` is therefore display-only and untrusted — make that explicit in a
comment, or stop writing it.

### Fix 2 — Backdated transaction handling

**File:** `app/Http/Controllers/TransactionController.php`
**Function:** `authorizeTransaction()` (line 857)

Bug: a row inserted with an earlier `PostingDate` invalidates the stored `Balance` of every
later row for that account. The prior database had 9,420 back-dated rows and
1,258 broken balance links as a result.

Required, inside the existing `DB::transaction()`:
1. Determine the earliest affected `PostingDate` per account in the voucher.
2. Re-walk that account's rows from that date forward in `PostingDate ASC, ID ASC` order,
   rewriting each `Balance`.
3. Write the final value to `acct_tbl_balance`.
4. Only touch the accounts in the voucher — never all accounts.

Use `lockForUpdate()` on the affected account rows to keep concurrent authorizations correct.

### Fix 3 — `acct_tbl_balance` sync

**File:** `app/Http/Controllers/TransactionController.php`
**Table:** `acct_tbl_balance`

Bug: line 955 does `->where('AccountNo', $acctNo)->update([...])`. If no row exists the update
silently affects 0 rows and the balance is lost. In the prior database, 23 accounts disagreed
with their ledger — the worst by `-6,168,168.00`.

Required:
- `updateOrInsert(['AccountNo' => $acctNo], ['Balance' => $final])` — depends on Fix 0.2.
- Invariant to hold after every authorization:
  `acct_tbl_balance.Balance == ` the `Balance` of that account's last row in
  `PostingDate ASC, ID ASC` order.

Separately: `AccountingController::updateBalances()` (line 168) runs three raw `UPDATE`
statements rolling account → sub-group → group `Balance` columns. **No report reads those
columns.** Either remove the endpoint or document it as unused — right now it looks
authoritative and is not.

---

## 8. PHASE 2 — INTEGRITY AND REPORTS

### Fix 7 — Expenditure statement

**File:** `app/Http/Controllers/AccountingController.php`
**Function:** `expenditureStatement()` — **line 53**

```php
public function expenditureStatement()
{
    return view('accounting.income_statement', $this->incomeStatementData());
}
```

It returns the Income Statement. Required: a dedicated view and data method querying
`gl_type = 5` only, displaying absolute values.

### Fix 8 — Amount validation

**File:** `app/Http/Controllers/TransactionController.php`
**Lines:** 67-75, 196-202, 306-313

`'Amount' => 'required|numeric'` permits `0` and negatives.

```php
'Amount' => 'required|numeric|gt:0',
```

### Fix 8b — Sign field whitelist

Same three validation blocks. `debitsign` and `creditsign` are `'required'` with no
constraint, so a client can post any value; `calculateBalance()` multiplies by it directly.

```php
'debitsign'  => 'required|in:-1',
'creditsign' => 'required|in:1',
```

All three views (`cashpayment`, `cashreceive`, `transferposting` under
`resources/views/transaction/`) submit these as hidden fields with exactly `-1` and `1`.
Confirm before locking the values.

### Fix 8c — PostingDate validation

Same three blocks. `'PostingDate' => 'required|date'` accepts `'0000-00-00'` on a non-strict
MySQL connection (the prior database has 8 such rows).

```php
'PostingDate' => 'required|date|after:1990-01-01|before_or_equal:today',
```

Adjust the bounds to the business rule for backdating.

### Fix 9 — Prevent same debit and credit account

**File:** `app/Http/Controllers/TransactionController.php`

**The field names are `DebitAccount` and `CreditAccount`** — not `DrAccount` / `CrAccount`.
Using the wrong names with `required` will fail every request.

`cashPaymentAction()` (line 67) and `transferPostingAction()` (line 306) — both fields present:

```php
'DebitAccount'  => 'required|different:CreditAccount',
'CreditAccount' => 'required',
```

`cashReceiveAction()` (line 196) has **no** `DebitAccount` field — debit is hardcoded to
`self::CASH_ACCOUNT_NO` (line 207). Validate in the controller instead:

```php
if ((string) $validated['CreditAccount'] === (string) self::CASH_ACCOUNT_NO) {
    // reject: cash-to-cash entry
}
```

Return a clear user-facing message in both cases.

### Fix 10 — AccountNo change safety

**File:** `app/Http/Controllers/AccountingController.php`
**Function:** `changeAccountNoAction()` — **line 585**

Bugs:
- No `unique` rule on `NewAccount` — renaming onto an existing number merges two accounts.
- Updates `acct_tbl_balance`, `acct_tbl_balance_tmp`, `acct_tbl_transaction`,
  `acct_tbl_transaction_tmp`, `acct_tbl_unauth_transaction` — but **not**
  `acct_tbl_openning_balance`, which is left orphaned.

Required:
- `'NewAccount' => 'required|unique:acct_tbl_balsheet_account,AccountNo'`
- Add `acct_tbl_openning_balance` to the cascade.
- Wrap the whole cascade in `DB::transaction()`.
- Enumerate every table holding an `AccountNo` before implementing —
  `grep -rn "AccountNo" app/` — and confirm the list is complete.

### Fix 11 — Account creation validation

**File:** `app/Http/Controllers/AccountingController.php`
**Function:** `addAccountAction()` — **line 351**

Current: `'AccountNo' => 'required|string|max:20'` — no uniqueness check.

**The table is `acct_tbl_balsheet_account`.** There is no table named `acct_tbl_account`;
referencing it produces a SQL error.

```php
'AccountNo' => 'required|string|max:20|unique:acct_tbl_balsheet_account,AccountNo',
```

### Fix 12 — Account deletion safety

**File:** `app/Http/Controllers/AccountingController.php`
**Function:** `deleteAccount()` — **line 489**

Currently checks only `acct_tbl_transaction` and `acct_tbl_transaction_tmp`.

Block deletion when any of these hold a reference:
- `acct_tbl_transaction`
- `acct_tbl_transaction_tmp`
- `acct_tbl_unauth_transaction`
- `acct_tbl_openning_balance`
- `acct_tbl_balance` with `Balance != 0`

### Fix 13 — Sub-group code change cascade

**File:** `app/Http/Controllers/AccountingController.php`
**Function:** `updateSubGroup()` — **line 514**

Changes `gl_sub_group` without cascading to child accounts. Since `AccountNo` is derived from
`gl_sub_group` (`nextAccountNo()` → `$gl_sub_group . '101'`), every child account is orphaned
from its sub-group after a rename.

Either cascade to all child accounts, or block the change when children exist.
Apply the same reasoning to `deleteSubGroup()`.

---

## 9. BEFORE WRITING CODE

Deliver an audit report containing:

1. §0.1 query result — the live transaction count.
2. §0.2 query result — the meaning of `gl_type 3`.
3. Current behaviour of each affected feature, one or two sentences each.
4. Exact file paths and line numbers for each bug, re-verified against the current checkout
   (line numbers in this document were correct at the time of writing but will drift).
5. The proposed minimal change per bug.
6. The complete list of files you will modify.
7. Any new files or views you need to create.
8. The DDL migration file contents for Phase 0.

**Wait for approval before coding.**

---

## 10. DEFINITION OF DONE

### 10.1 Functional tests — run on a fresh database, in order

| # | Action | Expected result |
|---|---|---|
| 1 | Opening balance: Cash (Asset, `2xxxxx101`) Dr 10,000 | Cash balance `= +10,000`; contra leg posted; trial balance balanced |
| 2 | Opening balance: Loan (Liability, `1xxxxx101`) Cr 5,000 | Loan balance `= +5,000`; contra leg posted |
| 3 | Income: Dr Cash 2,000 / Cr Income (`4xxxxx101`) 2,000 | Cash `+2,000` → `12,000`; Income `+2,000` |
| 4 | Expense: Dr Expense (`5xxxxx101`) 1,500 / Cr Cash 1,500 | Expense `+1,500`; Cash `−1,500` → `10,500` |
| 5 | Backdate: post a voucher dated between #1 and #3, Dr Cash 1,000 / Cr Income 1,000 | Cash final `= 11,500`; **every** intermediate row's `Balance` recomputed in `PostingDate ASC, ID ASC` order — walk the ledger and verify each row, not just the last |
| 6 | Balance Sheet | `Assets = Liabilities + Equity + Net Income`. With data #1–#4 only: `10,500 = 5,000 + 5,000 + 500` |
| 7 | Trial Balance | `SUM(DrAmount) = SUM(CrAmount)`, difference exactly `0.00` |
| 8 | Ledger vs All Account Balance vs `acct_tbl_balance` | All three report the identical final number for the same account and date |
| 9 | Expenditure report | Shows only `gl_type = 5`; does not equal the Income Statement output |
| 10 | Validation | Each of these is rejected with a clear message: `Amount = 0`; `Amount = -100`; `DebitAccount == CreditAccount`; `PostingDate = '0000-00-00'`; `debitsign = 99` |

Note on #6: the equation includes Net Income. `Assets = Liabilities + Equity` alone will fail
on correct code once any income or expense is posted.

### 10.2 Decimal precision check

After test #5, confirm no float drift was introduced:

```sql
SELECT SUM(DrAmount) - SUM(CrAmount) AS diff FROM acct_tbl_transaction;
-- must be exactly 0.00, not 0.0000001 or -0.22
```

### 10.3 Report consistency

For the same account and date range:

```
Ledger ending balance == All Account Balance value == acct_tbl_balance.Balance
```

Verify for at least one account of each `gl_type` in use.

### 10.4 Authorization flow

- New voucher → temp → unauthorized → authorized path works end to end.
- Backdated authorization recalculates the affected accounts only, leaving others untouched.
- Two concurrent authorizations on the same account do not corrupt the running balance
  (exercise the `lockForUpdate()` path).

### 10.5 Regression guard

`grep -rn "gl_type" app/ resources/` returns no remaining place where
`1` is treated as Asset or `2` as Liability.

---

## 11. FINAL RESTRICTION

This task changes **code** and the **empty schema**.

Do not deliver: data migration scripts, data cleanup tools, balance recalculation scripts, or
`UPDATE`/`DELETE` statements against existing accounting rows.

Phase 0 DDL is `ALTER TABLE` on structure only, executed against the empty live database
before it receives its first transaction. If §0.1 shows the database is not empty,
Phase 0 must be re-scoped and re-approved.
