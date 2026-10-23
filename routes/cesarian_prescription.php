<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CesPresStepsController;
use App\Http\Controllers\PrescriptionWizardController;


// ═══════════════════════════════════════════════════════════════════
// CESAREAN WIZARD — dedicated ces.* namespace
// Fully separated from the Normal Prescription Wizard (rx.*).
// Business AJAX (Medicine / Doctor / O/E) stays shared below.
// ═══════════════════════════════════════════════════════════════════

// Master (server-side tabs)
Route::get('/prescriptions/Caesarean', [CesPresStepsController::class, 'show'])->name('ces.wizard');

// Patients
Route::post('/ces/patient/save', [CesPresStepsController::class, 'savePatient'])->name('ces.patient.save');

// Complain
Route::post('/ces/complain/save', [CesPresStepsController::class, 'saveComplain'])->name('ces.complain.save');
Route::post('/ces/complain/ajax', [CesPresStepsController::class, 'ajaxComplain'])->name('ces.complain.ajax');

// Investigations (single-row add/update/delete)
Route::post('/ces/investigations',                   [CesPresStepsController::class, 'storeInvestigation'])->name('ces.investigation.store');
Route::put('/ces/investigations/{inv}',              [CesPresStepsController::class, 'updateInvestigation'])->name('ces.investigation.update');
Route::delete('/ces/investigations/{inv}',           [CesPresStepsController::class, 'destroyInvestigation'])->name('ces.investigation.destroy');

Route::post('/ces/rx/inv/list',   [CesPresStepsController::class, 'ajaxListInvestigations'])->name('ces.inv.list');
Route::post('/ces/rx/inv/store',  [CesPresStepsController::class, 'ajaxStoreInvestigation'])->name('ces.inv.store');
Route::post('/ces/rx/inv/update/{id}', [CesPresStepsController::class, 'ajaxUpdateInvestigation'])->name('ces.inv.update');
Route::post('/ces/rx/inv/delete/{id}', [CesPresStepsController::class, 'ajaxDeleteInvestigation'])->name('ces.inv.delete');
Route::post('/ces/rx/inv/ajax', [CesPresStepsController::class, 'ajaxInvestigation'])
    ->name('ces.inv.ajax');

// Diagnosis (single-row add/update/delete)
Route::post('/ces/diagnoses',                        [CesPresStepsController::class, 'storeDiagnosis'])->name('ces.diagnosis.store');
Route::put('/ces/diagnoses/{diag}',                  [CesPresStepsController::class, 'updateDiagnosis'])->name('ces.diagnosis.update');
Route::delete('/ces/diagnoses/{diag}',               [CesPresStepsController::class, 'destroyDiagnosis'])->name('ces.diagnosis.destroy');

Route::post('/ces/rx/diag/ajax', [CesPresStepsController::class, 'ajaxDiagnosis'])
    ->name('ces.diag.ajax');

// Medicine (single-row add/update/delete)
Route::post('/ces/medicines',                        [CesPresStepsController::class, 'storeMedicine'])->name('ces.medicine.store');
Route::put('/ces/medicines/{med}',                   [CesPresStepsController::class, 'updateMedicine'])->name('ces.medicine.update');
Route::delete('/ces/medicines/{med}',                [CesPresStepsController::class, 'destroyMedicine'])->name('ces.medicine.destroy');

Route::post('/ces/rx/med/ajax', [CesPresStepsController::class, 'ajaxMedicine'])
    ->name('ces.med.ajax');

// Doctors — SHARED business methods, aliased into the Cesarean namespace
// (same PrescriptionWizardController methods used by the Normal wizard;
//  no controller logic duplicated)
Route::post('/ces/doctors', [PrescriptionWizardController::class, 'storeDoctor'])->name('ces.doctor.store');
Route::delete('/ces/doctors/{doc}', [PrescriptionWizardController::class, 'destroyDoctor'])->name('ces.doctor.destroy');

// Finish / New Prescription
Route::post('/ces/rx/finish/new', [CesPresStepsController::class, 'finishNew'])
    ->name('ces.finish.new');

// O/E (Cesarean entry — keeps the user inside the Cesarean wizard)
Route::post('/ces/rx/oe/save', [CesPresStepsController::class, 'saveOe'])
    ->name('ces.oe.save');
Route::post('/ces/rx/oe/ajax', [CesPresStepsController::class, 'ajaxOe'])
    ->name('ces.oe.ajax');

// Previous prescriptions (Cesarean namespace)
Route::get(
    '/ces/patient/{patientId}/previous-prescriptions',
    [CesPresStepsController::class, 'previousPrescriptions']
)->name('ces.patient.prev');

Route::get(
    '/ces/prescription/{rxId}/details',
    [CesPresStepsController::class, 'previousPrescriptionDetails']
)->name('ces.prev.details');

// ═══════════════════════════════════════════════════════════════════
// SHARED BUSINESS ROUTES — used by BOTH wizards. Do not rename/duplicate.
// rx.saveOe / rx.oe.ajax live in this controller and serve both wizards;
// the redirect inside saveOe() returns to the wizard that submitted it.
// ═══════════════════════════════════════════════════════════════════
Route::post('/rx/oe/save', [CesPresStepsController::class, 'saveOe'])
    ->name('rx.saveOe');
Route::post('/rx/oe/ajax', [CesPresStepsController::class, 'ajaxOe'])
    ->name('rx.oe.ajax');
