<?php

use App\Http\Controllers\MitraRegisterController;
use App\Modules\Authentication\Controllers\GoogleRegistrationController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth'])->group(function () {
    Route::livewire('/dashboard', 'pages::customer.dashboard')->name('dashboard');
});

Route::get('/auth/google/redirect', [GoogleRegistrationController::class, 'redirect'])->name('google.redirect');
Route::get('/auth/google/callback', [GoogleRegistrationController::class, 'callback'])->name('google.callback');

Route::get('/complete-profile', function () {
    if (! session()->has('google_id')) {
        return redirect()->route('login');
    }

    return view('pages.auth.complete-profile');
})->name('complete.profile');

Route::post('/complete-profile', [GoogleRegistrationController::class, 'storeDataProfile'])->name('simpan.profile');

require __DIR__.'/settings.php';
require __DIR__.'/customer.php';

// Route Fitur Registrasi Mitra
Route::get('/register-mitra', [MitraRegisterController::class, 'create'])->name('register.mitra');
Route::post('/register-mitra', [MitraRegisterController::class, 'store'])->name('register.mitra.store');
