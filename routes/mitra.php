<?php

use Illuminate\Support\Facades\Route;

Route::redirect('/mitra', '/mitra/dashboard')->middleware(['auth', 'role.mitra'])->name('mitra.index');

Route::middleware(['auth', 'role.mitra'])->prefix('mitra')->name('mitra.')->group(function () {
    Route::livewire('/dashboard', 'pages::mitra.dashboard')->name('dashboard');
});
