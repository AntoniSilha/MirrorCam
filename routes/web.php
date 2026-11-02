<?php

use App\Http\Controllers\GoogleAuthController;
use App\Models\DetailPemesananTenant;
use App\Models\User;
use App\Models\VerifikasiKtp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function (Request $request) {

        /** @var User $user */
        $user = $request->user();

        $activeFilter = $request->query('filter', 'semua');
        $activeSort = $request->query('sort', 'terbaru');

        $ktpIds = VerifikasiKtp::where('id_akun', $user->id_akun)->pluck('id_ktp');

        $query = DetailPemesananTenant::whereIn('id_ktp', $ktpIds);

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
            'semua' => DetailPemesananTenant::whereIn('id_ktp', $ktpIds)->count(),
            'sedang_disewa' => DetailPemesananTenant::whereIn('id_ktp', $ktpIds)->where('status_sewa', 'Sedang Disewa')->count(),
            'selesai' => DetailPemesananTenant::whereIn('id_ktp', $ktpIds)->where('status_sewa', 'Selesai')->count(),
        ];

        return view('customer.orders.index', compact('user', 'orders', 'activeFilter', 'counts', 'activeSort'));
    })->name('dashboard');
});

Route::get('/auth/google/redirect', [GoogleAuthController::class, 'redirect'])->name('google.redirect');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('google.callback');

Route::get('/complete-profile', function () {
    if (! session()->has('google_id')) {
        return redirect('/register');
    }

    return view('pages.auth.complete-profile');
})->name('complete.profile');

Route::post('/complete-profile', [GoogleAuthController::class, 'storeDataProfile'])->name('simpan.profile');

require __DIR__.'/settings.php';
require __DIR__.'/customer.php';
require __DIR__.'/mitra.php';
