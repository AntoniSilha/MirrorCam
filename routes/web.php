<?php

use App\Http\Controllers\MitraRegisterController;
use App\Models\DetailPemesananTenant;
use App\Models\User;
use App\Models\VerifikasiKtp;
use App\Modules\Authentication\Controllers\GoogleRegistrationController;
use Illuminate\Support\Facades\Route;

use App\Portals\Customer\Controllers\HomeController;
use App\Portals\Customer\Resources\OrderListResource;
use App\Portals\Customer\Resources\UserProfileResource;
use Illuminate\Http\Request;

// use Laravel\Mcp\Request;

Route::get('/', [HomeController::class, 'index'])->name('home');
// Route Register & Login Fallback
Route::get('/register', [GoogleRegistrationController::class, 'redirect'])->name('register');
Route::get('/login', [GoogleRegistrationController::class, 'redirect'])->name('login');

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
