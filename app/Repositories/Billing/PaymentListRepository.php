<?php

namespace App\Repositories\Billing;

use App\Models\InvestigationPayment;
use App\Models\InvestigationPaymentItem;
use App\Repositories\Billing\Contracts\PaymentListRepositoryInterface;

class PaymentListRepository implements PaymentListRepositoryInterface
{
    public function getFiltered(array $filters, int $perPage)
    {
        return InvestigationPayment::query()
            ->filterByPatient($filters['patient_id'] ?? null)
            ->filterByService($filters['service_name'] ?? null)
            ->searchTerm($filters['q'] ?? null)
            ->orderByDesc('PaymentDate')
            ->orderByDesc('ID')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function getSummary(array $filters)
    {
        return InvestigationPayment::query()
            ->filterByPatient($filters['patient_id'] ?? null)
            ->filterByService($filters['service_name'] ?? null)
            ->searchTerm($filters['q'] ?? null)
            ->selectRaw('
                COUNT(*) as total_invoices,
                COUNT(DISTINCT PatientId) as total_patients,
                SUM(TotalBill) as total_bill,
                SUM(Discount) as total_discount,
                SUM(PaidAmount) as total_paid,
                SUM(DueAmount) as total_due
            ')
            ->first();
    }

    public function getDistinctServices()
    {
        return InvestigationPaymentItem::whereNotNull('ServiceName')
            ->distinct()
            ->orderBy('ServiceName')
            ->pluck('ServiceName');
    }
}