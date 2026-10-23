# ADR-0001: Investigation-Only Accounting Integration

**Date**: 2026-08-26
**Status**: accepted
**Deciders**: Project owner

## Context

The clinic's double-entry ledger (`acct_*` tables) is a port of a CodeIgniter 3-era
accounting system. Billing produces many revenue streams — investigation, consultation,
admission/bed charge, operation, pharmacy, nursing investigation. Only the
**investigation** stream currently posts automatically to the ledger.

`AccountingIntegrationService::postInvestigationInvoice()` and
`postInvestigationDuePayment()` are the only automatic accounting entries, called from
`InvoiceService::createInvoice()` and `PaynowController`. Every other revenue type is
entered manually by an operator through the Cash Receive screen, which offers those
purposes (`CONSULT_FEE`, `ADMISSION_FEE`, `BED_CHARGE`, `OPERATION_FEE`,
`PHARMACY_SALE`, `OTHER_INCOME`, …) as manual voucher types.

## Decision

For the current phase, **only investigation invoices are posted to the ledger
automatically**. Other revenue streams are captured manually via the Cash Receive /
voucher screens until a dedicated integration exists. The accounting module is not yet a
whole-hospital revenue ledger.

## Alternatives Considered

### Alternative 1: Post every revenue stream automatically now
- **Pros**: complete, consistent ledger; no manual double-entry
- **Cons**: touches admission, discharge, operation, pharmacy and nursing payment
  controllers at once — a large, high-risk change; each needs its own GL mapping and
  money-source reconciliation; the ledger posting engine is still stabilising
- **Why not**: too broad for the current phase; risk of corrupting the ledger outweighs
  the convenience

### Alternative 2: Keep everything manual
- **Pros**: no code change; operators already use the screens
- **Cons**: investigation would no longer be automatic either, losing the work already
  done; manual posting is error-prone and easy to skip
- **Why not**: investigation integration is proven and useful; removing it would be a
  regression

## Consequences

### Positive
- Investigation revenue flows into the ledger automatically and idempotently.
- Other streams remain visible and operable through existing manual screens.
- The integration surface is small and easy to reason about while the engine matures.

### Negative
- Consultation, admission, bed charge, operation, pharmacy and nursing revenue are not
  represented in the ledger unless an operator posts them manually.
- Ledger reports are incomplete as a statement of total hospital income for this phase.

### Risks
- Staff may not know they must manually post non-investigation revenue — mitigate with
  on-screen guidance and a checklist.
- When a later ADR extends scope to other streams, historical non-investigation revenue
  may be missing from the ledger; decide then whether to backfill.
