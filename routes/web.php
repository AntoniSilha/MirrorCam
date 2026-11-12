<?php

use App\Http\Controllers\MitraRegisterController;
use App\Http\Controllers\PengaduanPelangganController;
use App\Http\Controllers\TenantController;
use App\Models\User;
use App\Modules\Authentication\Controllers\GoogleRegistrationController;
use App\Portals\Customer\Controllers\HomeController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// use Laravel\Mcp\Request;

Route::get('/', [HomeController::class, 'index'])->name('home');
// Route Eksplorasi Tenant
Route::get('/tenant', [TenantController::class, 'index'])->name('tenant.index');
Route::get('/tenant/{tenant:slug}', [TenantController::class, 'show'])->name('tenant.show');

// Route Register & Login Fallback
Route::get('/register', [GoogleRegistrationController::class, 'redirect'])->name('register');
Route::get('/login', [GoogleRegistrationController::class, 'redirect'])->name('login');

Route::middleware(['auth', 'role.user'])->group(function () {
    Route::livewire('/dashboard', 'pages::customer.dashboard')->name('dashboard');
    Route::resource('pengaduan', PengaduanPelangganController::class)->only(['index', 'create', 'store', 'show']);
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

// require __DIR__.'/settings.php';
require __DIR__.'/customer.php';
require __DIR__.'/mitra.php';

// Route Fitur Registrasi Mitra
Route::middleware(['auth'])->group(function () {
    Route::get('/register-mitra', [MitraRegisterController::class, 'create'])->name('register.mitra');
    Route::post('/register-mitra', [MitraRegisterController::class, 'store'])->name('register.mitra.store');
});

if (app()->environment('local')) {
    Route::get('/dev-login', function () {
        $user = User::first();
        if ($user) {
            Auth::login($user);

            return redirect()->route('pengaduan.index')->with('status', 'Berhasil login sebagai '.$user->name.' untuk testing.');
        }

        return 'Tidak ada akun user di database.';
    })->name('dev.login');
}
