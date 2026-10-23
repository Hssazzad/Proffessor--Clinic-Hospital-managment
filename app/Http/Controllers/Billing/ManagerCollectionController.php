<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Http\Requests\Billing\CreateInvoiceRequest;
use App\Services\Billing\CreateInvoiceFormDataService;
use App\Services\Billing\InvoiceService;

/**
 * Manager Collection — same Create Payment flow, different source tag.
 *
 * Renders the SAME view (billing.create_invoice_index) and calls the SAME
 * InvoiceService::createInvoice() as CreateInvoiceController. The only
 * difference: the source is forced server-side to 'manager' and is NEVER
 * read from user input, so reception/manager invoices cannot be mixed up
 * by tampering with the request body.
 */
class ManagerCollectionController extends Controller
{
    public function __construct(
        protected InvoiceService $invoiceService,
        protected CreateInvoiceFormDataService $formData
    ) {
    }

    // ============================================================
    // GET /Billing/ManagerCollection
    // ============================================================
    public function index()
    {
        return view('billing.create_invoice_index', $this->formData->formData() + [
            'paymentSource' => 'manager',
            'storeRoute'    => route('billing.manager.store'),
            'pageTitle'     => 'Manager Collection',
        ]);
    }

    // ============================================================
    // POST /Billing/ManagerCollection/store
    // Same JSON shape as CreateInvoiceController@store so the
    // existing view JS works unchanged.
    // ============================================================
    public function store(CreateInvoiceRequest $request)
    {
        try {
            $result = $this->invoiceService->createInvoice($request, 'manager');
            return response()->json($result);

        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);

        } catch (\Throwable $e) {
            \Log::error('ManagerCollectionController@store: ' . $e->getMessage(), [
                'patient_id' => $request->patient_id ?? null,
                'trace'      => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Invoice could not be saved. Error: ' . $e->getMessage()
            ], 500);
        }
    }
}
