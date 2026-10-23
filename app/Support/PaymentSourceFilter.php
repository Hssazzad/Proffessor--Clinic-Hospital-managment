<?php

namespace App\Support;

use Illuminate\Support\Facades\Schema;

class PaymentSourceFilter
{
    public const SOURCE_ALL       = 'all';
    public const SOURCE_RECEPTION = 'reception';
    public const SOURCE_MANAGER   = 'manager';
    public const SOURCE_LEGACY    = 'legacy';

    public const SOURCES = [
        self::SOURCE_ALL,
        self::SOURCE_RECEPTION,
        self::SOURCE_MANAGER,
        self::SOURCE_LEGACY,
    ];

    /**
     * Cache whether investigation_payments.payment_source exists.
     */
    protected static ?bool $hasColumn = null;

    /**
     * Check if payment_source column exists on investigation_payments.
     * Guarded with Schema::hasTable & Schema::hasColumn, cached in-process.
     */
    public static function hasColumn(): bool
    {
        if (static::$hasColumn === null) {
            static::$hasColumn = Schema::hasTable('investigation_payments')
                && Schema::hasColumn('investigation_payments', 'payment_source');
        }

        return static::$hasColumn;
    }

    /**
     * Reset the cached column check (useful in tests).
     */
    public static function resetColumnCache(): void
    {
        static::$hasColumn = null;
    }

    /**
     * Whitelist allowed sources. Anything unrecognized defaults to 'all'.
     */
    public static function normalize(?string $value): string
    {
        $val = strtolower(trim((string) $value));
        return in_array($val, self::SOURCES, true) ? $val : self::SOURCE_ALL;
    }

    /**
     * Human-friendly label for a source.
     */
    public static function label(?string $source): string
    {
        return match (static::normalize($source)) {
            self::SOURCE_RECEPTION => 'Reception',
            self::SOURCE_MANAGER   => 'Manager',
            self::SOURCE_LEGACY    => 'Legacy',
            default                => 'All',
        };
    }

    /**
     * Render an HTML badge for a given payment_source value from DB.
     * $rawSource is the raw DB column value (e.g. 'reception', 'manager', or null).
     */
    public static function badgeHtml(?string $rawSource): string
    {
        $s = strtolower(trim((string) $rawSource));

        if ($s === self::SOURCE_MANAGER) {
            return '<span class="badge badge-primary px-2 py-1" style="font-size:10px;font-weight:600;letter-spacing:0.3px;">Manager</span>';
        }

        if ($s === self::SOURCE_RECEPTION) {
            return '<span class="badge badge-info px-2 py-1" style="font-size:10px;font-weight:600;letter-spacing:0.3px;">Reception</span>';
        }

        return '<span class="badge badge-secondary px-2 py-1" style="font-size:10px;font-weight:500;letter-spacing:0.3px;">Legacy</span>';
    }

    /**
     * Apply payment_source filter to a QueryBuilder or Eloquent query.
     *
     * @param \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder $query
     * @param string|null $source 'all'|'reception'|'manager'|'legacy'
     * @param string $column e.g. 'investigation_payments.payment_source', 'p.payment_source', etc.
     * @return mixed
     */
    public static function applyToQuery($query, ?string $source, string $column = 'investigation_payments.payment_source')
    {
        if (! static::hasColumn()) {
            return $query;
        }

        $normalized = static::normalize($source);

        return match ($normalized) {
            self::SOURCE_RECEPTION => $query->where($column, '=', 'reception'),
            self::SOURCE_MANAGER   => $query->where($column, '=', 'manager'),
            self::SOURCE_LEGACY    => $query->whereNull($column),
            default                => $query,
        };
    }
}
