<?php

use App\Http\Controllers\DoctorNotificationController;
use App\Http\Controllers\UsgFollowupController;
use App\Http\Controllers\UsgReportController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {

    Route::get('usg/patient-search', [UsgReportController::class, 'patientSearch'])
        ->name('usg.patient-search');

    Route::resource('usg/usg-reports', UsgReportController::class);

    Route::get('usg/create', [UsgReportController::class, 'create'])->name('usg-reports.create-alias');

    Route::prefix('usg/usg-followups')->name('usg-followups.')->group(function () {
        Route::get('/', [UsgFollowupController::class, 'index'])->name('index');
        Route::post('{id}/contacted', [UsgFollowupController::class, 'markContacted'])->name('contacted');
        Route::post('{id}/complete', [UsgFollowupController::class, 'complete'])->name('complete');
        Route::post('{id}/cancel', [UsgFollowupController::class, 'cancel'])->name('cancel');
    });

    Route::prefix('usg/notifications')->name('notifications.')->group(function () {
        Route::get('/', [DoctorNotificationController::class, 'index'])->name('index');
        Route::post('{id}/read', [DoctorNotificationController::class, 'markRead'])->name('read');
    });
});
