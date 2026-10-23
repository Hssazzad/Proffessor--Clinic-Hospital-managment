<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB; 
use App\Http\Controllers\PrescriptionWizardController;
use App\Http\Controllers\PrescriptionController;
use App\Http\Controllers\PreConAssessmentController;
use App\Http\Controllers\SurgeryPrescriptionController;
use App\Http\Controllers\AdmissionPrintOptionsController;



// --- Master Prescription Wizard (server-side tabs) ---
Route::get('/prescriptions/wizard', [PrescriptionWizardController::class, 'show'])->name('rx.wizard');

// --- Patient Management ---
Route::post('/prescriptions/patient/save', [PrescriptionWizardController::class, 'savePatient'])->name('rx.patient.save');
Route::get('/patients/search-ajax', [PrescriptionWizardController::class, 'patientsSearchAjax'])->name('patients.search');

// --- Template Medicines (Public if needed) ---
Route::get('/template-medicines/search', [SurgeryPrescriptionController::class, 'getTemplateMedicines'])->name('template-medicines.search');

// --- Investigations (Add/Update/Delete) ---
Route::post('/prescriptions/investigations', [PrescriptionWizardController::class, 'storeInvestigation'])->name('rx.investigation.store');
Route::put('/prescriptions/investigations/{inv}', [PrescriptionWizardController::class, 'updateInvestigation'])->name('rx.investigation.update');
Route::delete('/prescriptions/investigations/{inv}', [PrescriptionWizardController::class, 'destroyInvestigation'])->name('rx.investigation.destroy');

// --- Diagnosis (Add/Update/Delete) ---
Route::post('/prescriptions/diagnoses', [PrescriptionWizardController::class, 'storeDiagnosis'])->name('rx.diagnosis.store');
Route::put('/prescriptions/diagnoses/{diag}', [PrescriptionWizardController::class, 'updateDiagnosis'])->name('rx.diagnosis.update');
Route::delete('/prescriptions/diagnoses/{diag}', [PrescriptionWizardController::class, 'destroyDiagnosis'])->name('rx.diagnosis.destroy');

// --- Medicine (Add/Update/Delete) ---
Route::post('/prescriptions/medicines', [PrescriptionWizardController::class, 'storeMedicine'])->name('rx.medicine.store');
Route::put('/prescriptions/medicines/{med}', [PrescriptionWizardController::class, 'updateMedicine'])->name('rx.medicine.update');
Route::delete('/prescriptions/medicines/{med}', [PrescriptionWizardController::class, 'destroyMedicine'])->name('rx.medicine.destroy');

// --- Doctors ---
Route::post('/prescriptions/doctors', [PrescriptionWizardController::class, 'storeDoctor'])->name('rx.doctor.store');
Route::put('/prescriptions/doctors/{doc}', [PrescriptionWizardController::class, 'updateDoctor'])->name('rx.doctor.update');
Route::delete('/prescriptions/doctors/{doc}', [PrescriptionWizardController::class, 'destroyDoctor'])->name('rx.doctor.destroy');

// --- AJAX Helpers & Lists ---
Route::post('/rx/inv/list', [PrescriptionWizardController::class, 'ajaxListInvestigations'])->name('rx.inv.list');
Route::post('/rx/inv/store', [PrescriptionWizardController::class, 'ajaxStoreInvestigation'])->name('rx.inv.store');
Route::post('/rx/inv/update/{id}', [PrescriptionWizardController::class, 'ajaxUpdateInvestigation'])->name('rx.inv.update');
Route::post('/rx/inv/delete/{id}', [PrescriptionWizardController::class, 'ajaxDeleteInvestigation'])->name('rx.inv.delete');
Route::post('/rx/inv/ajax', [PrescriptionWizardController::class, 'ajaxInvestigation'])->name('rx.inv.ajax');
Route::post('/rx/diag/ajax', [PrescriptionWizardController::class, 'ajaxDiagnosis'])->name('rx.diag.ajax');
Route::post('/rx/med/ajax', [PrescriptionWizardController::class, 'ajaxMedicine'])->name('rx.med.ajax');
Route::post('/rx/complain/save', [PrescriptionWizardController::class, 'saveComplain'])->name('rx.complain.save');   
Route::post('/rx/complain/ajax', [PrescriptionWizardController::class, 'ajaxComplain'])->name('rx.complain.ajax');
Route::post('/rx/finish/new', [PrescriptionWizardController::class, 'finishNew'])->name('rx.finish.new');
Route::post('/rx/oe-ajax', [PrescriptionWizardController::class, 'oeAjax'])->name('rx.oe.ajax');
Route::get('/ajax/filtered-medicines', [PrescriptionWizardController::class, 'filteredMedicines'])->name('ajax.filtered.medicines');

// Sub medicine types for the prescription filter cascade (public, same level as filtered-medicines)
Route::get('/ajax/medicine-sub-types', [PrescriptionWizardController::class, 'subTypes'])->name('ajax.medicine.subtypes');
Route::get('/ajax/commonly-used-medicines', [PrescriptionWizardController::class, 'commonlyUsedMedicines'])->name('ajax.commonly.used.medicines');
Route::post('/ajax/quick-add-medicine', [PrescriptionWizardController::class, 'quickAddMedicine'])->name('ajax.quick.add.medicine');

Route::get('/ajax/load-medicines', function () {
    if (!DB::getSchemaBuilder()->hasTable('common_medicine')) {
        return response()->json([]);
    }

    $columns = DB::getSchemaBuilder()->getColumnListing('common_medicine');
    $select = array_values(array_intersect(['id', 'name', 'strength', 'GroupName'], $columns));

    $query = DB::table('common_medicine')->select($select)->orderBy('name');
    if (in_array('active', $columns, true)) {
        $query->where(function ($q) {
            $q->where('active', 1)->orWhereNull('active');
        });
    }

    return $query->get()->map(fn ($m) => [
        'id' => $m->id ?? null,
        'name' => $m->name ?? '',
        'strength' => $m->strength ?? '',
        'GroupName' => $m->GroupName ?? '',
    ])->values();
})->name('ajax.load.medicines');

// --- Patient History & Main PDFs ---
Route::get('/rx/patient/{patientId}/previous-prescriptions', [PrescriptionWizardController::class, 'previousPrescriptions'])->name('rx.patient.prev');
Route::get('/rx/prescription/{rxId}/details', [PrescriptionWizardController::class, 'previousPrescriptionDetails'])->name('rx.prev.details');
Route::get('/prescriptions/{id}/pdf', [PrescriptionWizardController::class, 'pdf'])->name('rx.pdf.inline');

// --- Authenticated Route Group ---
Route::middleware(['auth'])->group(function () {

    // General Prescriptions (Non-Surgery)
    Route::get('prescriptions/search', [PrescriptionController::class, 'search'])->name('rx.search');

    // Preview AJAX route — canonical name
    Route::get('prescriptions/preview-ajax/{id}', [PrescriptionController::class, 'previewAjax'])
        ->name('prescriptions.preview.ajax');

    // Additional alias for older internal usage (if some code uses rx.preview.ajax)
    Route::get('rx/preview-ajax/{id}', [PrescriptionController::class, 'previewAjax'])
        ->name('rx.preview.ajax');

    Route::get('prescriptions/admission-stage-preview/{admissionId}', [PrescriptionController::class, 'admissionStagePreview'])
         ->name('prescriptions.admission.stage.preview');

    Route::get('prescriptions/{id}/pdf-inline', [PrescriptionController::class, 'pdf'])->name('rx.pdf.inline');

    // NEW: Print view (browser print, not PDF)
    Route::get('prescriptions/print/{id}', [PrescriptionController::class, 'printView'])
         ->name('prescriptions.print');

    // Fix / compatibility: admin route alias used somewhere in theme/menu — add it to avoid RouteNotFound
    Route::get('admin/preconassessment/create', [PreConAssessmentController::class, 'create'])
         ->name('admin.preconassessment.create');

    Route::get('prescriptions/surgery-preview/{id}', [PrescriptionController::class, 'surgeryPreview'])->name('prescriptions.surgery.preview');
    Route::get('prescriptions/print-data/{id}', [PrescriptionController::class, 'printData'])->name('prescriptions.printData');
    Route::get('prescriptions/admission-print-data', [PrescriptionController::class, 'admissionPrintData'])->name('prescriptions.admissionPrintData');
    Route::get('admission/print-options', [AdmissionPrintOptionsController::class, 'options'])->name('admission.print.options');
    Route::post('admission/print-selection', [AdmissionPrintOptionsController::class, 'saveSelection'])->name('admission.print.selection');
    
    // Store general prescription
    Route::post('/prescriptions', [PrescriptionController::class, 'store'])->name('prescriptions.store');
    
    // ═══════════════════════════════════════════════════════════════════════
    // ✅ Pre-Con Assessment Routes (Updated)
    // ═══════════════════════════════════════════════════════════════════════
    Route::get('/prescriptions/PreConAssessment', [PreConAssessmentController::class, 'create'])
        ->name('prescriptions.preconassessment');
    
    Route::post('/prescriptions/PreConAssessmentSave', [PreConAssessmentController::class, 'store'])
        ->name('prescriptions.preconassessment.save');
    
    // Get assessment history via AJAX (Latest 10 records)
    Route::get('/preconassessment/history/{patientcode}', [PreConAssessmentController::class, 'getHistory'])
        ->name('prescriptions.preconassessment.history');

    // --- SURGERY PRESCRIPTION GROUP - FIXED ROUTES ---
    Route::prefix('prescriptions/SurgeryPrescription')->name('surgery-prescriptions.')->group(function () {
        
        // Main routes
        Route::get('/', [SurgeryPrescriptionController::class, 'index'])->name('index');
        Route::get('/create', [SurgeryPrescriptionController::class, 'create'])->name('create');
        Route::post('/store', [SurgeryPrescriptionController::class, 'store'])->name('store');
        
    
        
       
        Route::get('/get-templates', [SurgeryPrescriptionController::class, 'getTemplates'])->name('get-templates');
        
       
        Route::get('/get-doctors', [SurgeryPrescriptionController::class, 'getDoctors'])->name('get-doctors');
        
       
        Route::get('/get-template-data/{id}', [SurgeryPrescriptionController::class, 'getTemplateData'])
            ->name('get-template-data')
            ->where('id', '[0-9]+');
        
        
        Route::get('/search-patients', [SurgeryPrescriptionController::class, 'searchPatients'])->name('search-patients');
        
        
        Route::get('/search-medicines', [SurgeryPrescriptionController::class, 'searchMedicines'])->name('search-medicines');
        
        
        Route::get('/{id}/pdf', [SurgeryPrescriptionController::class, 'viewPDF'])->name('pdf');
        Route::get('/{id}/download', [SurgeryPrescriptionController::class, 'generatePDF'])->name('download');
        
        
        Route::get('/search-diagnoses', [SurgeryPrescriptionController::class, 'searchDiagnoses'])->name('search-diagnoses');
        Route::get('/search-investigations', [SurgeryPrescriptionController::class, 'searchInvestigations'])->name('search-investigations');
        Route::get('/search-advices', [SurgeryPrescriptionController::class, 'searchAdvices'])->name('search-advices');
        Route::get('/search-fresh-prescriptions', [SurgeryPrescriptionController::class, 'searchFreshPrescriptions'])->name('search-fresh-prescriptions');
        Route::get('/search-discharge-summaries', [SurgeryPrescriptionController::class, 'searchDischargeSummaries'])->name('search-discharge-summaries');
        
        
        Route::get('/test', [SurgeryPrescriptionController::class, 'test'])->name('test');
    });

});