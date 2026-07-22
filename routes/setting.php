<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MasterDataController;
use App\Http\Controllers\InvestigationManageController;
use App\Http\Controllers\ReferencePersonController;
use App\Http\Controllers\UserMenuController;

Route::prefix('settings')->middleware('auth')->group(function () {

    Route::get('/AddMedicine', [MasterDataController::class, 'medicinesIndex'])
        ->name('settings.medicines.index');
    Route::post('/medicines/store', [MasterDataController::class, 'medicinesStore'])
        ->name('settings.medicines.store');
    Route::put('/medicines/update/{medicine}', [MasterDataController::class, 'medicinesUpdate'])
        ->name('settings.medicines.update');
    Route::delete('/medicines/delete/{medicine}', [MasterDataController::class, 'medicinesDestroy'])
        ->name('settings.medicines.destroy');

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