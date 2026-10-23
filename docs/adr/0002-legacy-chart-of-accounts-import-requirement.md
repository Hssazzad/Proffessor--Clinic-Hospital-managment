# ADR-0002: Legacy Chart-of-Accounts Import Requirement

**Date**: 2026-08-26
**Status**: accepted
**Deciders**: Project owner

## Context

The accounting tables were originally created by hand during the CodeIgniter 3 era
and never existed in Laravel migrations. The baseline migration
(`2026_08_24_000001_baseline_accounting_tables`) captures only the four core
transaction/balance tables (`acct_tbl_transaction`, `acct_tbl_transaction_tmp`,
`acct_tbl_unauth_transaction`, `acct_tbl_balance`).

The chart-of-accounts tables (`acct_tbl_balsheet_group`, `acct_tbl_balsheet_sub_group`,
`acct_tbl_balsheet_account`) and the opening-balance table (`acct_tbl_openning_balance`)
were **not present in the available backup**, so their DDL has not been guessed and they
are not under migration control.

`AccountingIntegrationService::ensureGlAccount()` auto-creates the four hardcoded GL
accounts (Cash, Patient Accounts Receivable, Investigation Income, Investigation Discount)
on first use, but the Cash Payment / Cash Receive / Transfer Posting screens, the
ledger statement, and the account balance report all query `acct_tbl_balsheet_account`
for the full chart of accounts.

## Decision

On a fresh database, the chart-of-accounts tables must be imported from a dump of the
live production database. The accounting module is **not self-bootstrapping** on a clean
environment.

## Alternatives Considered

### Alternative 1: Write migrations for the full chart-of-accounts schema
- **Pros**: version-controlled; fresh DB bootstrap works automatically
- **Cons**: the exact DDL, column defaults, and data are unknown — guessing would
  produce a schema that may not match production, and every migration would need to
  reconcile with the unknown shape; the comment in the baseline migration explicitly
  says "their DDL has NOT been guessed"
- **Why not**: the risk of producing a mismatched schema that breaks the live system
  on deploy is higher than the inconvenience of importing a dump

### Alternative 2: Seed the four hardcoded accounts in a seeder
- **Pros**: the four accounts used by the integration service would be available on a
  fresh DB
- **Cons**: `ensureGlAccount()` already does this at runtime, and the manual voucher
  screens (Cash Payment / Receive / Transfer) would still lack the full account list
  (employee, supplier, expense, other income accounts, etc.) — the system would be
  partially usable but misleadingly incomplete
- **Why not**: a partial chart of accounts is worse than requiring a full import; partial
  bootstrap creates a false sense of completeness

## Consequences

### Positive
- Live dumps are the single source of truth for the chart of accounts — no drift.
- The auto-create mechanism in `ensureGlAccount()` at least covers the four essential
  accounts, so the integration service never fails on a fresh DB even if the dump
  hasn't been imported yet.

### Negative
- Setting up a new environment requires a production dump, which may contain
  patient-identifying data — must be scrubbed before use in non-production.
- The accounting module is not fully usable on a fresh database until the dump is
  imported.

### Risks
- Developer onboarding friction: every new dev environment needs a manual dump import
  step. Mitigate with a documented setup script and a sanitised sample dump.
- Schema drift: if the live chart-of-accounts schema changes, the migration guard
  (`hasTable()`) will silently skip non-existent tables — the change only applies
  when the dump is next imported. Schedule a follow-up ADR to migrate the chart of
  accounts into migration control once the exact DDL is known.