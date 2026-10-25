<?php

use App\Portals\Customer\Controllers\CustomerOrderController;
use App\Portals\Customer\Controllers\CustomerSecurityController;
use App\Portals\Customer\Controllers\GoogleAuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('customer')->name('customer.')->group(function () {
    // Orders
    Route::get('/orders', [CustomerOrderController::class, 'index'])->name('orders.index');

    // Security & Password Reset
    Route::get('/security/reset-password', [CustomerSecurityController::class, 'edit'])->name('security.reset-password');
    Route::put('/security/change-password', [CustomerSecurityController::class, 'update'])->name('security.change-password');

    // Google OAuth
    Route::get('/auth/google', [GoogleAuthController::class, 'redirectToGoogle'])->name('auth.google');
    Route::get('/auth/google/callback', [GoogleAuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');
});
