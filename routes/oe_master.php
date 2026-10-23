<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Settings\OeMasterController;



Route::middleware('auth')->prefix('settings')->group(function () {
    Route::resource('oe', OeMasterController::class)
        ->names('settings.oe')
        ->except(['show']);
});
