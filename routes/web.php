<?php

use App\Http\Controllers\GoogleAuthController;
use App\Models\DetailPemesananTenant;
use App\Models\User;
use App\Models\VerifikasiKtp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Portals\Customer\Controllers\HomeController;
use App\Portals\Customer\Resources\OrderListResource;
use App\Portals\Customer\Resources\UserProfileResource;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function (Request $request) {

        /** @var User $user */
        $user = $request->user();

        $activeFilter = $request->query('filter', 'semua');
        $activeSort = $request->query('sort', 'terbaru');

        $ktpIds = VerifikasiKtp::where('id_akun', $user->id_akun)->pluck('id_ktp');

        $query = DetailPemesananTenant::with(['tenant', 'produk.galeri'])
            ->whereIn('id_ktp', $ktpIds);

        if (in_array($activeFilter, ['sedang_disewa', 'sedang-disewa', 'renting'])) {
            $query->where('status_sewa', 'Sedang Disewa');
        } elseif (in_array($activeFilter, ['selesai', 'completed'])) {
            $query->where('status_sewa', 'Selesai');
        }

        if ($activeSort === 'terlama') {
            $query->orderBy('waktu_ambil_booking', 'asc');
        } else {
            $query->orderBy('waktu_ambil_booking', 'desc');
        }

        $ordersCollection = $query->get();
        $orders = OrderListResource::collection($ordersCollection)->resolve();

        $totalCount = DetailPemesananTenant::whereIn('id_ktp', $ktpIds)->count();
        $sedangDisewaCount = DetailPemesananTenant::whereIn('id_ktp', $ktpIds)->where('status_sewa', 'Sedang Disewa')->count();
        $selesaiCount = DetailPemesananTenant::whereIn('id_ktp', $ktpIds)->where('status_sewa', 'Selesai')->count();

        $counts = [
            'all' => $totalCount,
            'semua' => $totalCount,
            'renting' => $sedangDisewaCount,
            'sedang_disewa' => $sedangDisewaCount,
            'completed' => $selesaiCount,
            'selesai' => $selesaiCount,
        ];

        $userProfile = (new UserProfileResource($user))->resolve();

        return view('customer.orders.index', [
            'user' => $userProfile,
            'orders' => $orders,
            'activeFilter' => $activeFilter,
            'counts' => $counts,
            'activeSort' => $activeSort,
        ]);
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
