<?php

use App\Http\Controllers\DoctorController;
use Illuminate\Support\Facades\Route;

Route::prefix('settings')->middleware('auth')->group(function () {
    Route::get('AddDoctor', [DoctorController::class, 'create'])->name('settings.doctors.create');
    Route::post('AddDoctor', [DoctorController::class, 'store'])->name('settings.doctors.store');

    Route::get('doctors', [DoctorController::class, 'index'])->name('settings.doctors.index');
    Route::get('doctors/data', [DoctorController::class, 'ajaxList'])->name('settings.doctors.data');
    Route::get('doctors/{id}/edit', [DoctorController::class, 'edit'])->name('settings.doctors.edit');
    Route::put('doctors/{id}', [DoctorController::class, 'update'])->name('settings.doctors.update');
    Route::delete('doctors/{id}', [DoctorController::class, 'destroy'])->name('settings.doctors.destroy');
});
