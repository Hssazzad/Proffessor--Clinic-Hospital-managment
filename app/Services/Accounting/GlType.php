<?php

declare(strict_types=1);

namespace App\Services\Accounting;

/**
 * The single definition of the debit/credit sign convention.
 *
 * Every balance calculation in the accounting module must go through
 * signedAmount() (or its SQL twin sumExpression()). Previously the same
 * convention was written out by hand in four places and two of them had it
 * backwards, which is what made ledger opening balances and the general
 * ledger report disagree with the balance sheet.
 */
final class GlType
{
    public const LIABILITY   = 1;
    public const ASSET       = 2;
    public const EQUITY      = 3;
    public const INCOME      = 4;
    public const EXPENDITURE = 5;

    /**
     * Does this account type's balance increase on a debit?
     *
     * Assets and Expenditure do. Liability, Equity and Income increase on a
     * credit.
     */
    public static function isDebitNatured(int $glType): bool
    {
        return in_array($glType, (array) config('accounting.debit_natured_types', [2, 5]), true);
    }

    /**
     * The signed effect of one transaction line on its account's balance.
     *
     * Debit-natured : Dr - Cr
     * Credit-natured: Cr - Dr
     *
     * Both legs are always subtracted, so a line that somehow carries a value
     * in both DrAmount and CrAmount nets out correctly instead of silently
     * dropping one of them.
     */
    public static function signedAmount(int $glType, float $drAmount, float $crAmount): float
    {
        $delta = self::isDebitNatured($glType)
            ? $drAmount - $crAmount
            : $crAmount - $drAmount;

        return round($delta, 2);
    }

    /**
     * The SQL equivalent of signedAmount() for aggregating many rows at once.
     *
     * Returns a raw expression such as
     *   COALESCE(SUM(DrAmount) - SUM(CrAmount), 0)
     * suitable for selectRaw().
     */
    public static function sumExpression(int $glType): string
    {
        return self::isDebitNatured($glType)
            ? 'COALESCE(SUM(DrAmount) - SUM(CrAmount), 0)'
            : 'COALESCE(SUM(CrAmount) - SUM(DrAmount), 0)';
    }

    /**
     * Human label for a gl_type code, e.g. 2 => 'Asset'.
     */
    public static function label(int $glType): string
    {
        return (string) (config('accounting.gl_types')[$glType] ?? 'Unknown');
    }

    /**
     * All gl_type codes mapped to their labels, for dropdowns and report headers.
     *
     * @return array<int,string>
     */
    public static function labels(): array
    {
        return (array) config('accounting.gl_types', []);
    }
}
