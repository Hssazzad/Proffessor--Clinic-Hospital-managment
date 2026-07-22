<?php

use App\Http\Controllers\OperationConsentController;

Route::middleware(['auth'])->prefix('operation')->group(function () {
    Route::get('/consentlist',                [OperationConsentController::class, 'index'])->name('operation.consent.index');
    Route::get('/consentform',                [OperationConsentController::class, 'search'])->name('operation.consent.search');
    Route::get('/consentform/{admission_id}', [OperationConsentController::class, 'create'])->name('operation.consent.create');
    Route::post('/consentform/store',         [OperationConsentController::class, 'store'])->name('operation.consent.store');
    Route::get('/consentform/print/{id}',     [OperationConsentController::class, 'print'])->name('operation.consent.print');
});