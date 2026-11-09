<?php

use Illuminate\Support\Facades\Route;

Route::redirect('/mitra', '/mitra/dashboard')->middleware('auth')->name('mitra.index');

Route::middleware(['auth'])->prefix('mitra')->name('mitra.')->group(function () {
    Route::livewire('/dashboard', 'pages::mitra.dashboard')->name('dashboard');
});
