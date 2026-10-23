<?php

namespace App\Repositories\Billing\Contracts;

use Illuminate\Support\Collection;

interface InvestigationReportRepositoryInterface
{
    /**
     * Revenue + test count + patient count per normalized category.
     *
     * @param  array  $filters
     */
    public function getCategoryTotals(array $filters): Collection;

    /**
     * Service-level breakdown within a category (or all categories when null).
     *
     * @param  array  $filters
     */
    public function getServiceBreakdown(?string $category, array $filters): Collection;

    /**
     * Distinct patient counts grouped by day (daily) or YYYY-MM (monthly).
     *
     * @param  array  $filters
     */
    public function getPatientCounts(array $filters, string $grouping): Collection;

    /**
     * Canonical category list for the dropdown / summary ordering.
     */
    public function getCategories(): array;
}
