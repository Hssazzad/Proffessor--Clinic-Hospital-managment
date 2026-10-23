<?php

namespace App\Services\Billing;

use Illuminate\Support\Facades\DB;

/**
 * Shared form data for the Create Payment ("Create Invoice") screens.
 *
 * Both CreateInvoiceController@index (reception) and
 * ManagerCollectionController@index (manager) render the SAME Blade view
 * (billing.create_invoice_index), so the dropdown data must come from one
 * place instead of being copy-pasted between controllers.
 */
class CreateInvoiceFormDataService
{
    public function formData(): array
    {
        $invTypes = DB::table('configMain')
            ->select('Code', 'Name')
            ->orderBy('Name')
            ->get();

        $references = DB::table('patient_ref')
            ->where('active', 1)
            ->orderBy('Name')
            ->get(['ID', 'Name', 'ref_type']);

        return compact('invTypes', 'references');
    }
}
