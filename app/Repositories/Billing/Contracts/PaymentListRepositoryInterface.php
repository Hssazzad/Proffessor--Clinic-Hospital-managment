<?php

namespace App\Repositories\Billing\Contracts;

interface PaymentListRepositoryInterface
{
    public function getFiltered(array $filters, int $perPage);

    public function getSummary(array $filters);

    public function getDistinctServices();
}