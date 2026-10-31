<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\MitraRegisterController;
use Illuminate\Http\Request;
use App\Models\DetailPemesananTenant;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function (Request $request) {

        /** @var \App\Models\User $user */
        $user = $request->user();

        $activeFilter = $request->query('filter', 'semua');
        $activeSort = $request->query('sort', 'terbaru');

        $query = DetailPemesananTenant::where('id_ktp', $user->id_akun);

        if ($activeFilter === 'sedang_disewa') {
            $query->where('status_sewa', 'Sedang Disewa');
        } elseif ($activeFilter === 'selesai') {
            $query->where('status_sewa', 'Selesai');
        }

        if ($activeSort === 'terlama') {
            $query->orderBy('waktu_ambil_booking', 'asc');
        } else {

            $query->orderBy('waktu_ambil_booking', 'desc');
        }

        $orders = $query->get();

        $counts = [
            'semua' => DetailPemesananTenant::where('id_ktp', $user->id_akun)->count(),
            'sedang_disewa' => DetailPemesananTenant::where('id_ktp', $user->id_akun)->where('status_sewa', 'Sedang Disewa')->count(),
            'selesai' => DetailPemesananTenant::where('id_ktp', $user->id_akun)->where('status_sewa', 'Selesai')->count(),
        ];

        return view('customer.orders.index', compact('user', 'orders', 'activeFilter', 'counts', 'activeSort'));
    })->name('dashboard');
});

Route::get('/auth/google/redirect', [GoogleAuthController::class, 'redirect'])->name('google.redirect');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('google.callback');

Route::get('/complete-profile', function () {
    if (!session()->has('google_id')) {
        return redirect('/register');
    }
    return view('pages.auth.complete-profile');
})->name('complete.profile');

Route::post('/complete-profile', [GoogleAuthController::class, 'storeDataProfile'])->name('simpan.profile');

require __DIR__.'/settings.php';
require __DIR__.'/customer.php';

// Route Fitur Registrasi Mitra
Route::get('/register-mitra', [MitraRegisterController::class, 'create'])->name('register.mitra');
Route::post('/register-mitra', [MitraRegisterController::class, 'store'])->name('register.mitra.store');
