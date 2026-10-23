<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Repositories\Billing\Contracts\PaymentListRepositoryInterface;
use Illuminate\Http\Request;

class PaylistV2Controller extends Controller
{
    public function __construct(
        protected PaymentListRepositoryInterface $repo
    ) {
    }

    public function index(Request $request)
    {
        $filters = $request->only(['patient_id', 'service_name', 'q', 'payment_source', 'date_mode', 'date_column', 'single_date', 'date_from', 'date_to', 'per_page']);
        $filters['payment_source'] = $request->input('payment_source', 'all');
        $perPage = (int) $request->input('per_page', 20);

        $payments = $this->repo->getFiltered($filters, $perPage);
        $summary  = $this->repo->getSummary($filters);
        $services = $this->repo->getDistinctServices();

        $grouped = $payments->getCollection()->groupBy(function ($item) {
            return $item->PaymentDate
                ? \Carbon\Carbon::parse($item->PaymentDate)->toDateString()
                : ($item->created_at ? \Carbon\Carbon::parse($item->created_at)->toDateString() : 'Unknown');
        });

        return view('billing.paylist.v2', compact('payments', 'summary', 'services', 'filters', 'grouped'));
    }
}