<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CesPresStepsController;


// Master (server-side tabs)
Route::get('/prescriptions/Caesarean', [CesPresStepsController::class, 'show'])->name('rx.wizard');

// Patients
Route::post('/prescriptions/patient/save', [CesPresStepsController::class, 'savePatient'])->name('rx.patient.save');

// Investigations (single-row add/update/delete)
Route::post('/prescriptions/investigations',                   [CesPresStepsController::class, 'storeInvestigation'])->name('rx.investigation.store');
Route::put('/prescriptions/investigations/{inv}',              [CesPresStepsController::class, 'updateInvestigation'])->name('rx.investigation.update');
Route::delete('/prescriptions/investigations/{inv}',           [CesPresStepsController::class, 'destroyInvestigation'])->name('rx.investigation.destroy');

// Diagnosis (single-row add/update/delete)
Route::post('/prescriptions/diagnoses',                        [CesPresStepsController::class, 'storeDiagnosis'])->name('rx.diagnosis.store');
Route::put('/prescriptions/diagnoses/{diag}',                  [CesPresStepsController::class, 'updateDiagnosis'])->name('rx.diagnosis.update');
Route::delete('/prescriptions/diagnoses/{diag}',               [CesPresStepsController::class, 'destroyDiagnosis'])->name('rx.diagnosis.destroy');

// Medicine (single-row add/update/delete)
Route::post('/prescriptions/medicines',                        [CesPresStepsController::class, 'storeMedicine'])->name('rx.medicine.store');
Route::put('/prescriptions/medicines/{med}',                   [CesPresStepsController::class, 'updateMedicine'])->name('rx.medicine.update');
Route::delete('/prescriptions/medicines/{med}',                [CesPresStepsController::class, 'destroyMedicine'])->name('rx.medicine.destroy');

Route::post('/rx/inv/list',   [CesPresStepsController::class, 'ajaxListInvestigations'])->name('rx.inv.list');
Route::post('/rx/inv/store',  [CesPresStepsController::class, 'ajaxStoreInvestigation'])->name('rx.inv.store');

// New for inline edit & delete
Route::post('/rx/inv/update/{id}', [CesPresStepsController::class, 'ajaxUpdateInvestigation'])->name('rx.inv.update');
Route::post('/rx/inv/delete/{id}', [CesPresStepsController::class, 'ajaxDeleteInvestigation'])->name('rx.inv.delete');

Route::post('/rx/inv/ajax', [CesPresStepsController::class, 'ajaxInvestigation'])
    ->name('rx.inv.ajax');
Route::post('/rx/diag/ajax', [CesPresStepsController::class, 'ajaxDiagnosis'])
    ->name('rx.diag.ajax');
    
Route::post('/rx/med/ajax', [CesPresStepsController::class, 'ajaxMedicine'])
    ->name('rx.med.ajax');
Route::post('/rx/finish/new', [CesPresStepsController::class, 'finishNew'])
    ->name('rx.finish.new');

Route::post('/rx/complain/save', [CesPresStepsController::class, 'saveComplain'])
    ->name('rx.complain.save');   
Route::post('/rx/complain/ajax', [CesPresStepsController::class, 'ajaxComplain'])
    ->name('rx.complain.ajax');

// ✅ O/E Save Route (নতুন)
Route::post('/rx/oe/save', [CesPresStepsController::class, 'saveOe'])
    ->name('rx.saveOe');

Route::get('/patients/search-ajax', [CesPresStepsController::class, 'patientsSearchAjax'])
    ->name('patients.search.ajax');
	
Route::get('/ajax/load-medicines', function () {
    return DB::table('common_medicine')
        ->orderBy('name')
        ->get(['id','name','strength','GroupName']);
})->name('ajax.load.medicines');


Route::get(
    '/rx/patient/{patientId}/previous-prescriptions',
    [CesPresStepsController::class, 'previousPrescriptions']
)->name('rx.patient.prev');

Route::get(
    '/rx/prescription/{rxId}/details',
    [CesPresStepsController::class, 'previousPrescriptionDetails']
)->name('rx.prev.details');

Route::get(
    '/prescriptions/{id}/pdf',
    [CesPresStepsController::class, 'pdf']
)->name('rx.pdf.inline');