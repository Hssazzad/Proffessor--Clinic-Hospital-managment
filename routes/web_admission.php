<?php
use App\Http\Controllers\PatientAdmitController;
use App\Http\Controllers\AdmissionController;
use App\Http\Controllers\PatientAccomodationController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {

    // --- Admission for select templete Routes ---
    Route::get('admission/admitpatient', [PatientAdmitController::class, 'admitpatient'])
        ->name('admission.admitpatient');
	  
    Route::post('admission/admitpatient', [PatientAdmitController::class, 'storeadmitpatient'])
        ->name('admission.admitpatient.store');

    Route::get('admission/admit-patient/medicine-rows', [PatientAdmitController::class, 'medicineRowsHtml'])
        ->name('admission.admitpatient.medicine_rows');
		
	Route::get('admission/show-admission-slip', [PatientAdmitController::class, 'showAdmissionSlip'])
    ->name('admission.showAdmissionSlip');

    Route::post('admission/update-procedure-type', [PatientAdmitController::class, 'updateProcedureType'])
        ->name('admission.updateProcedureType');

    Route::get('admission/PatientAccomodation', [PatientAccomodationController::class, 'index'])
        ->name('admission.patient_accomodation');

    Route::get('admission/patient-accomodation', [PatientAccomodationController::class, 'index']);

    Route::post('admission/PatientAccomodation/store', [PatientAccomodationController::class, 'store'])
        ->name('admission.patient_accomodation.store');

    Route::post('admission/PatientAccomodation/change-bed', [PatientAccomodationController::class, 'changeBed'])
        ->name('admission.patient_accomodation.changeBed');

    Route::get('admission/PatientAccomodation/{bed}/details', [PatientAccomodationController::class, 'bedDetails'])
        ->name('admission.patient_accomodation.bedDetails');

    Route::get('admission/PatientAccomodation/{bed}/notes', [PatientAccomodationController::class, 'notes'])
        ->name('admission.patient_accomodation.notes');

    Route::get('admission/PatientAccomodation/{bed}/update-status', [PatientAccomodationController::class, 'updateStatusForm'])
        ->name('admission.patient_accomodation.updateStatus');
		
	
		
		
	 // --- Admission for select Admission Medicine---	
	Route::get('admission/admedicine', [PatientAdmitController::class, 'admedicine'])
        ->name('admission.admedicine');	
		
	Route::match(['get', 'post'], '/admission/showadMedicine', [PatientAdmitController::class, 'showadMedicine'])
    ->name('admission.showadMedicine');
	
	
	Route::post('/admission/admedicineList/list', [PatientAdmitController::class, 'admedicineList'])
    ->name('admission.admedicineList.list');

	Route::post('/admission/admedicineSave/save', [PatientAdmitController::class, 'admedicineSave'])
		->name('admission.admedicineSave.save');

	Route::post('/admission/admedicine-save-all-admit', [PatientAdmitController::class, 'admedicineSaveAllAdmit'])
		->name('admission.admedicineSaveAllAdmit');

	Route::post('/admission/admedicineDelete/delete', [PatientAdmitController::class, 'admedicineDelete'])
		->name('admission.admedicine.delete');

	Route::get('admission/admedicine-print', [PatientAdmitController::class, 'admedicinePrint'])
		->name('admission.admedicine.print');

	Route::get('admission/premedicine-print', [PatientAdmitController::class, 'premedicinePrint'])
		->name('admission.premedicine.print');

	Route::get('admission/postmedicine-print', [PatientAdmitController::class, 'postmedicinePrint'])
		->name('admission.postmedicine.print');

	Route::get('admission/roundmedicine-print', [PatientAdmitController::class, 'roundmedicinePrint'])
		->name('admission.roundmedicine.print');

	Route::get('admission/freshmedicine-print', [PatientAdmitController::class, 'freshmedicinePrint'])
		->name('admission.freshmedicine.print');
		
	 // --- Admission for select Pre surgery Medicine---	
	
	Route::get('admission/presurgery', [PatientAdmitController::class, 'premedicine'])
        ->name('admission.presurgery');		
	Route::post('/admission/showpreMedicine', [PatientAdmitController::class, 'showpreMedicine'])
    ->name('admission.showpreMedicine');	
	Route::post('/admission/premedicineList/list', [PatientAdmitController::class, 'premedicineList'])
    ->name('admission.premedicineList.list');
	Route::post('/admission/premedicineSave/save', [PatientAdmitController::class, 'premedicineSave'])
		->name('admission.premedicineSave.save');
	Route::post('/admission/premedicine-save-all-preorder', [PatientAdmitController::class, 'premedicineSaveAllPreorder'])
		->name('admission.premedicineSaveAllPreorder');
	Route::post('/admission/premedicineDelete/delete', [PatientAdmitController::class, 'premedicineDelete'])
		->name('admission.premedicine.delete');
		
     // --- Admission for select Post surgery Medicine---	
	
	Route::get('admission/postsurgery', [PatientAdmitController::class, 'postmedicine'])
        ->name('admission.postsurgery');		
	Route::post('/admission/showpostMedicine', [PatientAdmitController::class, 'showpostMedicine'])
    ->name('admission.showpostMedicine');	
	Route::post('/admission/postmedicineList/list', [PatientAdmitController::class, 'postmedicineList'])
    ->name('admission.postmedicineList.list');
	Route::post('/admission/postmedicineSave/save', [PatientAdmitController::class, 'postmedicineSave'])
		->name('admission.postmedicineSave.save');
	Route::post('/admission/postmedicine-save-all-postorder', [PatientAdmitController::class, 'postmedicineSaveAllPostorder'])
		->name('admission.postmedicineSaveAllPostorder');
	Route::post('/admission/postmedicineDelete/delete', [PatientAdmitController::class, 'postmedicineDelete'])
		->name('admission.postmedicine.delete');
		

    /* -------------------------------------------------------------------------- */
    /* 🏥 ROUND PATIENT ROUTES (ইউআরএল ফিক্স করা হয়েছে)                             */
    /* -------------------------------------------------------------------------- */    
  
    Route::get('admission/roundpatient', [PatientAdmitController::class, 'roundmedicine'])
        ->name('admission.roundmedicine');		
	Route::post('/admission/showroundMedicine', [PatientAdmitController::class, 'showroundMedicine'])
    ->name('admission.showroundMedicine');	
	Route::post('/admission/roundmedicineList/list', [PatientAdmitController::class, 'roundmedicineList'])
    ->name('admission.roundmedicineList.list');
	Route::post('/admission/roundmedicineSave/save', [PatientAdmitController::class, 'roundmedicineSave'])
		->name('admission.roundmedicineSave.save');
	Route::post('/admission/roundmedicineDelete/delete', [PatientAdmitController::class, 'roundmedicineDelete'])
		->name('admission.roundmedicine.delete');
	Route::post('/admission/round-medicine-save-all', [PatientAdmitController::class, 'roundmedicineSaveAll'])
    ->name('admission.roundmedicineSaveAll.save');	
		
	// --- Admission for select Fresh Medicine---	
	
	Route::get('admission/freshprescription', [PatientAdmitController::class, 'freshprescription'])
        ->name('admission.freshprescription');		
	Route::post('/admission/showfreshMedicine', [PatientAdmitController::class, 'showfreshMedicine'])
    ->name('admission.showfreshMedicine');	
	Route::post('/admission/freshmedicineList/list', [PatientAdmitController::class, 'freshmedicineList'])
    ->name('admission.freshmedicineList.list');
	Route::post('/admission/freshmedicineSave/save', [PatientAdmitController::class, 'freshmedicineSave'])
		->name('admission.freshmedicineSave.save');
	Route::post('/admission/freshmedicineDelete/delete', [PatientAdmitController::class, 'freshmedicineDelete'])
		->name('admission.freshmedicine.delete');
	Route::post('/admission/freshmedicineSaveAll/save', [PatientAdmitController::class, 'freshmedicineSaveAll'])
		->name('admission.freshmedicineSaveAll.save');

	// --- Shared draft-based medicine endpoints (all order types) ---

	Route::post('/admission/medicine-load-template', [PatientAdmitController::class, 'loadTemplateMedicines'])
		->name('admission.medicine.load-template');
		
	// --- Admission for Release Patient---	
	
	Route::get('admission/releasepatient', [PatientAdmitController::class, 'releasepatient'])
        ->name('admission.releasepatient');

    Route::get('admission/Discharge', [PatientAdmitController::class, 'dischargeList'])
        ->name('admission.discharge');

    Route::get('admission/discharge-history', [PatientAdmitController::class, 'dischargeHistory'])
        ->name('admission.discharge.history');

    Route::post('/admission/showdischargeMedicine', [PatientAdmitController::class, 'showdischargeMedicine'])
        ->name('admission.showdischargeMedicine');

    Route::post('/admission/discharge-submit', [PatientAdmitController::class, 'submitDischargeForApproval'])
        ->name('admission.discharge.submit');

    Route::post('/admission/discharge-approve', [PatientAdmitController::class, 'approveDischarge'])
        ->name('admission.discharge.approve');

    Route::post('/admission/discharge-do', [PatientAdmitController::class, 'doDischarge'])
        ->name('admission.discharge.do');

	// --- Next Stage (move patient to next status) ---
	Route::post('/admission/next-stage', [PatientAdmitController::class, 'nextStage'])
		->name('admission.nextStage');

	// --- Get Patient Current Status ---
	Route::post('/admission/get-patient-status', [PatientAdmitController::class, 'getPatientStatus'])
		->name('admission.getPatientStatus');

});
