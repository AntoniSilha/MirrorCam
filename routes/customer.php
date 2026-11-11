<?php

use App\Http\Controllers\PengaduanPelangganController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role.user'])->prefix('customer')->name('customer.')->group(function () {
    Route::livewire('/orders', 'pages::customer.dashboard')->name('orders.index');
    Route::resource('pengaduan', PengaduanPelangganController::class)->only(['index', 'create', 'store', 'show']);
});
