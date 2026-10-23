<?php

use App\Http\Controllers\oldpatient;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->get('/web-oldpatient/oldpatient', [oldpatient::class, 'oldpatient'])
    ->name('web-oldpatient');

Route::middleware('auth')->get('/web-oldpatient/oldpatient/{admission}/view', [oldpatient::class, 'view'])
    ->name('web-oldpatient.view');

Route::middleware('auth')->get('/web-oldpatient/admissionlist', [oldpatient::class, 'admissionList'])
    ->name('web-oldpatient.admissionlist');
