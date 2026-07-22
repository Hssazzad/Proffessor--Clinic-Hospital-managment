<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AppointmentController;

/*
|--------------------------------------------------------------------------
| Appointment Routes
|--------------------------------------------------------------------------
*/

Route::prefix('appointments')->group(function () {
    Route::get('/list', [AppointmentController::class, 'index'])
        ->name('appointments.list');

    Route::get('/pastappoinments', [AppointmentController::class, 'index'])
        ->name('appointments.pastappoinments');

    Route::get('/pastappointments', [AppointmentController::class, 'index'])
        ->name('appointments.pastappointments');
    
    // ১. অ্যাপয়েন্টমেন্ট লিস্ট দেখা এবং অ্যাপ্রুভ করা
    Route::get('/appointmentapprove', [AppointmentController::class, 'appointmentapprove'])
        ->name('appointments.approve');

    Route::post('/appointmentapprove', [AppointmentController::class, 'updateStatus'])
        ->name('appointments.updateStatus');

    // ২. অ্যাপয়েন্টমেন্ট ক্রিয়েট পেজ
    Route::get('/create', [AppointmentController::class, 'create'])
        ->name('appointments.create');

    // ৩. অ্যাপয়েন্টমেন্ট সেভ করার রাউট (উভয় নামের সাপোর্ট রাখা হলো এরর এড়াতে)
    Route::post('/store', [AppointmentController::class, 'store'])
        ->name('appointments.store'); // ভিউ ফাইলে এই নামটি বেশি ব্যবহৃত হয়েছে

    /* --- AJAX Routes --- */
    // এই রাউটগুলো সিরিয়াল এবং ডুপ্লিকেট চেকিংয়ের জন্য
    Route::get('/next-serial', [AppointmentController::class, 'nextSerial'])
        ->name('appointments.nextSerial');

    Route::get('/available-serials', [AppointmentController::class, 'availableSerials'])
        ->name('appointments.availableSerials');

    Route::get('/check-patient-date', [AppointmentController::class, 'checkPatientDate'])
        ->name('appointments.checkPatientDate');
});
