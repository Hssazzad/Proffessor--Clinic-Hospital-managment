<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MasterDataController;
use App\Http\Controllers\InvestigationManageController;
use App\Http\Controllers\ReferencePersonController;
use App\Http\Controllers\UserMenuController;
use App\Http\Controllers\Settings\MedicineMasterController;
use App\Http\Controllers\Settings\OeMasterController;

Route::prefix('settings')->middleware('auth')->group(function () {

    Route::get('/AddMedicine', [MedicineMasterController::class, 'medicinesIndex'])
        ->name('settings.medicines.index');
    Route::post('/medicines/store', [MedicineMasterController::class, 'medicinesStore'])
        ->name('settings.medicines.store');
    Route::put('/medicines/update/{medicine}', [MedicineMasterController::class, 'medicinesUpdate'])
        ->name('settings.medicines.update');
    Route::delete('/medicines/delete/{medicine}', [MedicineMasterController::class, 'medicinesDestroy'])
        ->name('settings.medicines.destroy');

    // Quick Add Medicine (strength optional)
    Route::post('/medicines/quick-add', [MedicineMasterController::class, 'medicinesQuickAdd'])
        ->name('settings.medicines.quick-add');

    // AJAX: re-render medicine list table (after Quick Add / without page reload)
    Route::get('/medicines/list-html', [MedicineMasterController::class, 'medicinesListHtml'])
        ->name('settings.medicines.list-html');

    // AJAX helpers used by the Add Medicine form (sub medicine types)
    Route::get('/medicines/sub-types', [MedicineMasterController::class, 'subMedicineTypeAjaxList'])
        ->name('settings.medicines.sub-types.ajax');
    Route::post('/medicines/sub-types/store', [MedicineMasterController::class, 'subMedicineTypeAjaxStore'])
        ->name('settings.medicines.sub-types.store');

    // AJAX: Company inline add (used by Medicine Master form + Quick Add modal)
    Route::post('/medicines/companies/store', [MedicineMasterController::class, 'companyAjaxStore'])
        ->name('settings.medicines.companies.store');

    // AJAX: Medicine Type inline add (used by Medicine Master form + Quick Add modal)
    Route::post('/medicines/types/store', [MedicineMasterController::class, 'medicineTypeAjaxStore'])
        ->name('settings.medicines.types.store');

    // AJAX: Dosage Form inline add (used by Medicine Master form + Quick Add modal)
    Route::post('/medicines/dosage-forms/store', [MedicineMasterController::class, 'dosageFormAjaxStore'])
        ->name('settings.medicines.dosage-forms.store');

    // AJAX: Live duplicate check while typing the medicine name
    Route::get('/medicines/check-duplicate', [MedicineMasterController::class, 'medicinesCheckDuplicate'])
        ->name('settings.medicines.check-duplicate');

    // CSV Export / Import (Medicine Master)
    Route::get('/medicines/csv-download', [MedicineMasterController::class, 'medicinesCsvDownload'])
        ->name('settings.medicines.csv.download');
    Route::post('/medicines/csv-import', [MedicineMasterController::class, 'medicinesCsvImport'])
        ->name('settings.medicines.csv.import');

    Route::get('/AddWardBed', [MasterDataController::class, 'wardBedIndex'])
        ->name('settings.wardbed.index');
    Route::post('/wardbed/store', [MasterDataController::class, 'wardBedStore'])
        ->name('settings.wardbed.store');
    Route::put('/wardbed/update/{id}', [MasterDataController::class, 'wardBedUpdate'])
        ->name('settings.wardbed.update');
    Route::delete('/wardbed/delete/{id}', [MasterDataController::class, 'wardBedDestroy'])
        ->name('settings.wardbed.destroy');

    Route::prefix('InvestigationPayment')->name('investigations.')->group(function () {
        Route::get('/', [InvestigationManageController::class, 'index'])
            ->name('index');
        Route::get('/categories', [InvestigationManageController::class, 'getCategories'])
            ->name('categories');
        
        Route::post('/store', [InvestigationManageController::class, 'store'])
            ->name('store');
        Route::post('/update', [InvestigationManageController::class, 'update'])
            ->name('update');
            
        Route::post('/categories/store', [InvestigationManageController::class, 'storeCategory'])
            ->name('categories.store');
        Route::post('/categories/update', [InvestigationManageController::class, 'updateCategory'])
            ->name('categories.update');
        Route::delete('/categories/{id}', [InvestigationManageController::class, 'destroyCategory'])
            ->name('categories.destroy');
        
        Route::delete('/{id}', [InvestigationManageController::class, 'destroy'])
            ->name('destroy');
    });

    Route::get('/ReferencePerson', [ReferencePersonController::class, 'index'])
        ->name('settings.referenceperson.index');
    Route::post('/referenceperson/store', [ReferencePersonController::class, 'store'])
        ->name('settings.referenceperson.store');
    Route::put('/referenceperson/update/{id}', [ReferencePersonController::class, 'update'])
        ->name('settings.referenceperson.update');
    Route::delete('/referenceperson/delete/{id}', [ReferencePersonController::class, 'destroy'])
        ->name('settings.referenceperson.destroy');

    Route::prefix('types')->name('settings.types.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\MedicineTypeController::class, 'index'])->name('index');
        Route::post('/', [App\Http\Controllers\Admin\MedicineTypeController::class, 'store'])->name('store');
        Route::put('{medicineType}', [App\Http\Controllers\Admin\MedicineTypeController::class, 'update'])->name('update');
        Route::delete('{medicineType}', [App\Http\Controllers\Admin\MedicineTypeController::class, 'destroy'])->name('destroy');

        Route::get('{medicineType}/sub-types', [App\Http\Controllers\Admin\MedicineTypeController::class, 'subTypesAjax'])->name('sub-types.ajax');
        Route::post('sub-types', [App\Http\Controllers\Admin\MedicineTypeController::class, 'subTypeStore'])->name('sub-types.store');
        Route::put('sub-types/{subType}', [App\Http\Controllers\Admin\MedicineTypeController::class, 'subTypeUpdate'])->name('sub-types.update');
        Route::delete('sub-types/{subType}', [App\Http\Controllers\Admin\MedicineTypeController::class, 'subTypeDestroy'])->name('sub-types.destroy');
    });

    // O/E Master
    Route::resource('oe', OeMasterController::class)
        ->names('settings.oe')
        ->except(['show']);

    // Treatment Types Management
    Route::prefix('treatment-types')->group(function () {
        Route::get('/', [App\Http\Controllers\Settings\TreatmentTypeController::class, 'index'])
            ->name('settings.treatment_types.index');
        Route::post('/store', [App\Http\Controllers\Settings\TreatmentTypeController::class, 'store'])
            ->name('settings.treatment_types.store');
        Route::put('/update/{id}', [App\Http\Controllers\Settings\TreatmentTypeController::class, 'update'])
            ->name('settings.treatment_types.update');
        Route::delete('/delete/{id}', [App\Http\Controllers\Settings\TreatmentTypeController::class, 'destroy'])
            ->name('settings.treatment_types.destroy');
    });

    // Chamber Fee Configuration
    Route::prefix('chamber-fee')->name('settings.chamber_fee.')->group(function () {
        Route::get('/', [App\Http\Controllers\Billing\ChamberFeeConfigController::class, 'index'])
            ->name('index');
        Route::post('/store', [App\Http\Controllers\Billing\ChamberFeeConfigController::class, 'store'])
            ->name('store');
        Route::put('/update/{id}', [App\Http\Controllers\Billing\ChamberFeeConfigController::class, 'update'])
            ->name('update');
        Route::patch('/toggle-status/{id}', [App\Http\Controllers\Billing\ChamberFeeConfigController::class, 'toggleStatus'])
            ->name('toggle-status');
        Route::delete('/delete/{id}', [App\Http\Controllers\Billing\ChamberFeeConfigController::class, 'destroy'])
            ->name('destroy');
    });

});

Route::middleware('auth')->group(function () {
    
    Route::get('usermenu/mainmenu', [UserMenuController::class, 'index'])
        ->name('parentmenu.index');
    Route::post('usermenu/mainmenu/store', [UserMenuController::class, 'parentStore'])
        ->name('Parentstore');
    Route::get('usermenu/mainmenu/{id}/edit', [UserMenuController::class, 'edit'])
        ->name('parentmenu.edit');
    Route::put('usermenu/mainmenu/{id}', [UserMenuController::class, 'update'])
        ->name('parentmenu.update');
    Route::delete('usermenu/mainmenu/{id}', [UserMenuController::class, 'destroy'])
        ->name('parentmenu.destroy');

    Route::get('usermenu/submenulist', [UserMenuController::class, 'submenulist'])
        ->name('usermenu.submenulist');
    Route::post('usermenu/submenulist/store', [UserMenuController::class, 'store'])
        ->name('usermenu.submenulist.store');
    Route::put('usermenu/submenulist/update/{id}', [UserMenuController::class, 'update'])
        ->name('usermenu.submenulist.update');
    Route::delete('usermenu/submenulist/delete/{id}', [UserMenuController::class, 'destroy'])
        ->name('usermenu.submenulist.destroy');
        
});