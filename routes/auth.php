<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Account\UserCreateController;

Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('register', [RegisteredUserController::class, 'store']);
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');
    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('reset-password', [NewPasswordController::class, 'store'])->name('password.store');
});

Route::middleware('auth')->group(function () {
    // Email Verification & Password Confirmation
    Route::get('verify-email', EmailVerificationPromptController::class)->name('verification.notice');
    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');
    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');
    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])->name('password.confirm');
    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);
    Route::put('password', [PasswordController::class, 'update'])->name('password.update');
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    Route::get('change-password', [PasswordController::class, 'edit'])->name('password.change');

    // User & Menu Management (ProfClinic Logic)
    Route::get('usermenu/newuser', [UserCreateController::class, 'newuser'])->name('usermenu.newuser');
    Route::post('user', [UserCreateController::class, 'store'])->name('user.store');
    
    // Submenu Logic
    Route::get('usermenu/newsubmenu', [UserCreateController::class, 'createSubmenu'])->name('submenu.newsubmenu');
    Route::post('usermenu/storeSubmenu', [UserCreateController::class, 'storeSubmenu'])->name('usermenu.storeSubmenu');
    Route::get('usermenu/next-code/{parent}', [UserCreateController::class, 'nextSubmenuCode'])->name('usermenu.nextcode');
    Route::get('usermenu/Userlist', [UserCreateController::class, 'Userlist'])->name('usermenu.Userlist');
    
    // Parent Menu Logic
    Route::get('usermenu/newparentmenu', [UserCreateController::class, 'createparentmenu'])->name('submenu.newparentmenu');
    Route::post('parentmenu/store', [UserCreateController::class, 'Parentstore'])->name('Parentstore');

    // FIX: Added the missing toggleSub route here
  //  Route::post('usermenu/toggle-sub', [UserCreateController::class, 'toggleSub'])->name('usermenu.toggleSub');
});