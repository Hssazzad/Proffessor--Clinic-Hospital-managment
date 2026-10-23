<?php

use App\Http\Controllers\OperationStaffController;
use Illuminate\Support\Facades\Route;

Route::prefix('settings')->middleware('auth')->group(function () {
    // Assistant Surgeons
    Route::get('assistant-surgeons',                [OperationStaffController::class, 'index'])->defaults('type', 'assistant_surgeon')->name('settings.assistant_surgeons.index');
    Route::get('assistant-surgeons/data',           [OperationStaffController::class, 'ajaxList'])->defaults('type', 'assistant_surgeon')->name('settings.assistant_surgeons.data');
    Route::get('assistant-surgeons/create',         [OperationStaffController::class, 'create'])->defaults('type', 'assistant_surgeon')->name('settings.assistant_surgeons.create');
    Route::post('assistant-surgeons',               [OperationStaffController::class, 'store'])->defaults('type', 'assistant_surgeon')->name('settings.assistant_surgeons.store');
    Route::get('assistant-surgeons/{id}/edit',      [OperationStaffController::class, 'edit'])->defaults('type', 'assistant_surgeon')->name('settings.assistant_surgeons.edit');
    Route::put('assistant-surgeons/{id}',           [OperationStaffController::class, 'update'])->defaults('type', 'assistant_surgeon')->name('settings.assistant_surgeons.update');
    Route::delete('assistant-surgeons/{id}',        [OperationStaffController::class, 'destroy'])->defaults('type', 'assistant_surgeon')->name('settings.assistant_surgeons.destroy');

    // Anesthesiologists
    Route::get('anesthesiologists',                 [OperationStaffController::class, 'index'])->defaults('type', 'anesthesiologist')->name('settings.anesthesiologists.index');
    Route::get('anesthesiologists/data',            [OperationStaffController::class, 'ajaxList'])->defaults('type', 'anesthesiologist')->name('settings.anesthesiologists.data');
    Route::get('anesthesiologists/create',          [OperationStaffController::class, 'create'])->defaults('type', 'anesthesiologist')->name('settings.anesthesiologists.create');
    Route::post('anesthesiologists',                [OperationStaffController::class, 'store'])->defaults('type', 'anesthesiologist')->name('settings.anesthesiologists.store');
    Route::get('anesthesiologists/{id}/edit',       [OperationStaffController::class, 'edit'])->defaults('type', 'anesthesiologist')->name('settings.anesthesiologists.edit');
    Route::put('anesthesiologists/{id}',            [OperationStaffController::class, 'update'])->defaults('type', 'anesthesiologist')->name('settings.anesthesiologists.update');
    Route::delete('anesthesiologists/{id}',         [OperationStaffController::class, 'destroy'])->defaults('type', 'anesthesiologist')->name('settings.anesthesiologists.destroy');

    // Operation Names
    Route::get('operation-names',                   [OperationStaffController::class, 'index'])->defaults('type', 'operation_name')->name('settings.operation_names.index');
    Route::get('operation-names/data',              [OperationStaffController::class, 'ajaxList'])->defaults('type', 'operation_name')->name('settings.operation_names.data');
    Route::get('operation-names/create',            [OperationStaffController::class, 'create'])->defaults('type', 'operation_name')->name('settings.operation_names.create');
    Route::post('operation-names',                  [OperationStaffController::class, 'store'])->defaults('type', 'operation_name')->name('settings.operation_names.store');
    Route::get('operation-names/{id}/edit',         [OperationStaffController::class, 'edit'])->defaults('type', 'operation_name')->name('settings.operation_names.edit');
    Route::put('operation-names/{id}',              [OperationStaffController::class, 'update'])->defaults('type', 'operation_name')->name('settings.operation_names.update');
    Route::delete('operation-names/{id}',           [OperationStaffController::class, 'destroy'])->defaults('type', 'operation_name')->name('settings.operation_names.destroy');

    // Discharge Diagnoses
    Route::get('discharge-diagnoses',               [OperationStaffController::class, 'index'])->defaults('type', 'discharge_diagnosis')->name('settings.discharge_diagnoses.index');
    Route::get('discharge-diagnoses/data',          [OperationStaffController::class, 'ajaxList'])->defaults('type', 'discharge_diagnosis')->name('settings.discharge_diagnoses.data');
    Route::get('discharge-diagnoses/create',        [OperationStaffController::class, 'create'])->defaults('type', 'discharge_diagnosis')->name('settings.discharge_diagnoses.create');
    Route::post('discharge-diagnoses',              [OperationStaffController::class, 'store'])->defaults('type', 'discharge_diagnosis')->name('settings.discharge_diagnoses.store');
    Route::get('discharge-diagnoses/{id}/edit',     [OperationStaffController::class, 'edit'])->defaults('type', 'discharge_diagnosis')->name('settings.discharge_diagnoses.edit');
    Route::put('discharge-diagnoses/{id}',          [OperationStaffController::class, 'update'])->defaults('type', 'discharge_diagnosis')->name('settings.discharge_diagnoses.update');
    Route::delete('discharge-diagnoses/{id}',       [OperationStaffController::class, 'destroy'])->defaults('type', 'discharge_diagnosis')->name('settings.discharge_diagnoses.destroy');

    // Discharge Instructions
    Route::get('discharge-instructions',            [OperationStaffController::class, 'index'])->defaults('type', 'discharge_instruction')->name('settings.discharge_instructions.index');
    Route::get('discharge-instructions/data',       [OperationStaffController::class, 'ajaxList'])->defaults('type', 'discharge_instruction')->name('settings.discharge_instructions.data');
    Route::get('discharge-instructions/create',     [OperationStaffController::class, 'create'])->defaults('type', 'discharge_instruction')->name('settings.discharge_instructions.create');
    Route::post('discharge-instructions',           [OperationStaffController::class, 'store'])->defaults('type', 'discharge_instruction')->name('settings.discharge_instructions.store');
    Route::get('discharge-instructions/{id}/edit',  [OperationStaffController::class, 'edit'])->defaults('type', 'discharge_instruction')->name('settings.discharge_instructions.edit');
    Route::put('discharge-instructions/{id}',       [OperationStaffController::class, 'update'])->defaults('type', 'discharge_instruction')->name('settings.discharge_instructions.update');
    Route::delete('discharge-instructions/{id}',    [OperationStaffController::class, 'destroy'])->defaults('type', 'discharge_instruction')->name('settings.discharge_instructions.destroy');

    // Operation Methods
    Route::get('operation-methods',                 [OperationStaffController::class, 'index'])->defaults('type', 'operation_method')->name('settings.operation_methods.index');
    Route::get('operation-methods/data',            [OperationStaffController::class, 'ajaxList'])->defaults('type', 'operation_method')->name('settings.operation_methods.data');
    Route::get('operation-methods/create',          [OperationStaffController::class, 'create'])->defaults('type', 'operation_method')->name('settings.operation_methods.create');
    Route::post('operation-methods',                [OperationStaffController::class, 'store'])->defaults('type', 'operation_method')->name('settings.operation_methods.store');
    Route::get('operation-methods/{id}/edit',       [OperationStaffController::class, 'edit'])->defaults('type', 'operation_method')->name('settings.operation_methods.edit');
    Route::put('operation-methods/{id}',            [OperationStaffController::class, 'update'])->defaults('type', 'operation_method')->name('settings.operation_methods.update');
    Route::delete('operation-methods/{id}',         [OperationStaffController::class, 'destroy'])->defaults('type', 'operation_method')->name('settings.operation_methods.destroy');
});
