<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\BillingController;

/*
|--------------------------------------------------------------------------
| Patient Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    
    // Patient CRUD Routes
    Route::prefix('patients')->name('patients.')->group(function () {
        
        // List all patients
        Route::get('/', [PatientController::class, 'index'])->name('index');
        
        // Create new patient
        Route::get('/newpatient', [PatientController::class, 'newpatient'])->name('newpatient');
        Route::post('/', [PatientController::class, 'storepatientdata'])->name('store');
        
        // Search patient
        Route::get('/searchpatient', [PatientController::class, 'searchpatient'])->name('searchpatient');
        
        // Edit & Update patient (with optional id)
        Route::get('/{id?}/edit', [PatientController::class, 'editpatient'])->name('editpatient');
        Route::put('/{id}/update', [PatientController::class, 'updatepatient'])->name('updatepatient');
        
        /*
        |----------------------------------------------------------------------
        | Billing Routes (Nested under patients)
        |----------------------------------------------------------------------
        */
        Route::prefix('billing')->name('billing.')->group(function () {
            Route::get('/', [BillingController::class, 'index'])->name('index');
            Route::get('/create', [BillingController::class, 'create'])->name('create');
            Route::post('/', [BillingController::class, 'store'])->name('store');
            Route::get('/due', [BillingController::class, 'due'])->name('due');
            Route::get('/search', [BillingController::class, 'searchPatient'])->name('search');
            Route::get('/{id}/edit', [BillingController::class, 'edit'])->name('edit');
            Route::put('/{id}', [BillingController::class, 'update'])->name('update');
            Route::delete('/{id}', [BillingController::class, 'destroy'])->name('destroy');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Appointment Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('appointments')->name('appointments.')->group(function () {
        Route::get('/list', [AppointmentController::class, 'index'])->name('list');
        Route::get('/pastappoinments', [AppointmentController::class, 'index'])->name('pastappoinments');
        Route::get('/pastappointments', [AppointmentController::class, 'index'])->name('pastappointments');
        Route::get('/approve', [AppointmentController::class, 'appointmentapprove'])->name('approve');
        Route::post('/approve', [AppointmentController::class, 'updateStatus'])->name('updateStatus');
        Route::get('/create', [AppointmentController::class, 'create'])->name('create');
        Route::post('/', [AppointmentController::class, 'store'])->name('store');
        Route::get('/next-serial', [AppointmentController::class, 'nextSerial'])->name('nextSerial');
        Route::get('/available-serials', [AppointmentController::class, 'availableSerials'])->name('availableSerials');
        Route::get('/check-patient-date', [AppointmentController::class, 'checkPatientDate'])->name('checkPatientDate');
    });

    /*
    |--------------------------------------------------------------------------
    | Location & API Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('api')->name('api.')->group(function () {
        
        // District Routes
        Route::post('/fetch-district', [LocationController::class, 'fetchDistrict'])->name('fetch_district');
        Route::post('/district/store', [LocationController::class, 'storeDistrict'])->name('district.store');
        
        // Upozila Routes
        Route::post('/fetch-upozila', [LocationController::class, 'fetch_upozila'])->name('fetch_upozila');
        Route::post('/upozila/store', [LocationController::class, 'storeUpozila'])->name('upozila.store');
        
        // Union Routes
        Route::post('/fetch-union', [LocationController::class, 'fetch_union'])->name('fetch_union');
        Route::post('/union/store', [LocationController::class, 'storeUnion'])->name('union.store');
        
        // Village Routes
        Route::post('/fetch-village', [LocationController::class, 'fetch_village'])->name('fetch_village');
        Route::post('/village/store', [LocationController::class, 'storeVillage'])->name('village.store');
        
        // Reference Person Routes
        Route::post('/fetch-reference-person', [PatientController::class, 'fetch_reference_person'])->name('fetch_reference_person');
        Route::post('/reference-person/store', [PatientController::class, 'store_reference_person'])->name('reference_person.store');
    });
});
