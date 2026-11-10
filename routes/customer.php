<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role.user'])->prefix('customer')->name('customer.')->group(function () {
    Route::livewire('/orders', 'pages::customer.dashboard')->name('orders.index');
});
