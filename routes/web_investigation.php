<?php
use App\Http\Controllers\InvestigationController;

Route::prefix('settings')->group(function () {

    Route::permanentRedirect('AddInvestigation', 'investigations');

    Route::get('investigations', [InvestigationController::class, 'index'])
        ->name('settings.investigation.index');

    Route::post('investigations/add', [InvestigationController::class, 'store'])
        ->name('settings.investigation.store');

    Route::get('investigations/{id}/edit', [InvestigationController::class, 'edit'])
        ->name('settings.investigation.edit');

    Route::put('investigations/{id}', [InvestigationController::class, 'update'])
        ->name('settings.investigation.update');

    Route::delete('investigations/{id}', [InvestigationController::class, 'destroy'])
        ->name('settings.investigation.destroy');
});
