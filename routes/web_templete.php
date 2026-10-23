<?php
use App\Http\Controllers\TemplateController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {

    // Common Medicine List (for autocomplete in Template Medicine form)
    Route::get('common-medicine/list', function () {
        $medicines = \App\Models\CommonMedicine::where('active', 1)
            ->orderBy('name')
            ->get(['id', 'name', 'GroupName', 'strength']);
        return response()->json(['ok' => true, 'medicines' => $medicines]);
    })->name('common.medicine.list');

    Route::prefix('templates')->group(function () {
        
        // Template Main Routes
        Route::get('/', [TemplateController::class, 'index'])->name('templates.index');
        Route::get('create', [TemplateController::class, 'create'])->name('templates.create');
        Route::post('store', [TemplateController::class, 'store'])->name('templates.store');

        // Chief Complain Section
        Route::get('add-complain', [TemplateController::class, 'addComplain'])->name('templates.addcomplain');
        Route::post('complain/ajax/add', [TemplateController::class, 'ajaxAddComplain'])->name('templates.complain.ajax.add');
        Route::get('complain/ajax/list', [TemplateController::class, 'ajaxListComplain'])->name('templates.complain.ajax.list');
        Route::delete('complain/ajax/{id}', [TemplateController::class, 'ajaxDeleteComplain'])->name('templates.complain.ajax.delete');

        // Diagnosis Section
        Route::get('adddiagonosis', [TemplateController::class, 'addDiagnosis'])->name('templates.adddiagnosis');
        Route::post('diagonosis/ajax/add', [TemplateController::class, 'ajaxAddDiagnosis'])->name('templates.diagnosis.ajax.add');
        Route::get('diagonosis/ajax/list', [TemplateController::class, 'ajaxListDiagnosis'])->name('templates.diagnosis.ajax.list');
        Route::delete('diagonosis/ajax/{id}', [TemplateController::class, 'ajaxDeleteDiagnosis'])->name('templates.diagnosis.ajax.delete');

        // Investigation Section
        Route::get('add-investigation', [TemplateController::class, 'addInvestigation'])->name('templates.addinvestigation');
        Route::post('investigation/ajax/add', [TemplateController::class, 'ajaxAddInvestigation'])->name('templates.investigation.ajax.add');
        Route::get('investigation/ajax/list', [TemplateController::class, 'ajaxListInvestigation'])->name('templates.investigation.ajax.list');
        Route::delete('investigation/ajax/{id}', [TemplateController::class, 'ajaxDeleteInvestigation'])->name('templates.investigation.ajax.delete');

        // Medicine Section
        Route::get('add-medicine', [TemplateController::class, 'addMedicine'])->name('templates.addmedicine');

        // Company-wise medicine selection (AJAX, reuses prescription filter logic)
        Route::get('medicine/companies', [TemplateController::class, 'ajaxMedicineCompanies'])->name('templates.medicine.companies');
        Route::get('medicine/groups',    [TemplateController::class, 'ajaxMedicineGroups'])->name('templates.medicine.groups');
        Route::get('medicine/options',   [TemplateController::class, 'ajaxMedicineOptions'])->name('templates.medicine.options');

        Route::post('medicine/ajax/add', [TemplateController::class, 'ajaxAddMedicine'])->name('templates.medicine.ajax.add');
        Route::get('medicine/ajax/list', [TemplateController::class, 'ajaxListMedicine'])->name('templates.medicine.ajax.list');
        Route::delete('medicine/ajax/{id}', [TemplateController::class, 'ajaxDeleteMedicine'])->name('templates.medicine.ajax.delete');
        Route::get('medicine/ajax/{id}', [TemplateController::class, 'ajaxGetMedicine'])->name('templates.medicine.ajax.get');
        Route::put('medicine/ajax/{id}', [TemplateController::class, 'ajaxUpdateMedicine'])->name('templates.medicine.ajax.update');
		
		// Advice Section
        Route::get('addadvice', [TemplateController::class, 'addAdvice'])->name('templates.addadvice');
        Route::post('advice/ajax/add', [TemplateController::class, 'ajaxAddAdvice'])->name('templates.advice.ajax.add');
        Route::get('advice/ajax/list', [TemplateController::class, 'ajaxListAdvice'])->name('templates.advice.ajax.list');
        Route::delete('advice/ajax/{id}', [TemplateController::class, 'ajaxDeleteAdvice'])->name('templates.advice.ajax.delete');

        // Fresh Prescription
        Route::get('addfreshprescription', [TemplateController::class, 'addFreshPrescription'])->name('templates.addfreshprescription');
        Route::post('fresh-prescription/ajax/store', [TemplateController::class, 'ajaxStoreFreshPrescription'])->name('templates.fresh.ajax.store');

        // Discharge Section
        Route::get('adddischarge', [TemplateController::class, 'addDischarge'])->name('templates.adddischarge');
        Route::post('discharge/ajax/add', [TemplateController::class, 'ajaxAddDischarge'])->name('templates.discharge.ajax.add');
		
		// Admission Section
        Route::post('admission/store', [TemplateController::class, 'storeAdmission'])->name('admission.store');
        Route::get('admission/pdf/{id}', [TemplateController::class, 'downloadPDF'])->name('admission.pdf');

        // Prescription List & View
        Route::get('prescriptions', [TemplateController::class, 'listSavedPrescriptions'])->name('prescriptions.index');
        Route::get('prescriptions/{templateid}', [TemplateController::class, 'showPrescription'])->name('prescriptions.show');
        Route::delete('prescriptions/{templateid}', [TemplateController::class, 'deletePrescription'])->name('prescriptions.destroy');

        // Display Template
        Route::get('displaytemplete', [TemplateController::class, 'displayTemplate'])->name('templates.display');
		
		// Ajax Routes
        Route::get('ajax/details', [TemplateController::class, 'ajaxTemplateDetails'])->name('templates.ajax.details');
        Route::get('discharge/ajax/get', [TemplateController::class, 'ajaxGetDischarge'])->name('templates.discharge.ajax.get');
        Route::get('oe/ajax/list', [TemplateController::class, 'ajaxListOE'])->name('templates.oe.ajax.list');

        // Prescription View
        Route::get('prescription/view', [TemplateController::class, 'prescriptionView'])->name('prescription.view');
		
		Route::get('prescription/multi-step', [TemplateController::class, 'multiStepForm'])->name('prescription.multi.step');
		
		Route::post('prescription/store', [TemplateController::class, 'storePrescription'])->name('prescription.store');
		
    });
});