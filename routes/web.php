<?php

use App\Http\Controllers\MitraRegisterController;
use App\Http\Controllers\PengaduanPelangganController;
use App\Http\Controllers\SuperAdmin\DashboardController;
use App\Http\Controllers\SuperAdmin\KomisiPlatformController;
use App\Http\Controllers\SuperAdmin\TenantManagementController;
use App\Http\Controllers\TenantController;
use App\Models\User;
use App\Modules\Authentication\Controllers\GoogleRegistrationController;
use App\Portals\Customer\Controllers\BookingCartController;
use App\Portals\Customer\Controllers\HomeController;
use App\Portals\Customer\Controllers\ProductCatalogController;
use App\Portals\Customer\Controllers\TenantCatalogController;
use Illuminate\Support\Facades\Route;

// use Laravel\Mcp\Request;

Route::get('/', [HomeController::class, 'index'])->name('home');
// Route Eksplorasi Tenant
Route::get('/tenant', [TenantController::class, 'index'])->name('tenant.index');
Route::get('/tenant/{tenant:slug}', [TenantController::class, 'show'])->name('tenant.show');

// Route Register & Login Fallback
Route::get('/register', [GoogleRegistrationController::class, 'redirect'])->name('register');
Route::get('/login', [GoogleRegistrationController::class, 'redirect'])->name('login');

// Halaman Katalog Produk Lintas Tenant (Semua Alat dari Seluruh Mitra)
Route::get('/katalog', [ProductCatalogController::class, 'index'])->name('katalog.index');

// Halaman Katalog Spesifik Per Tenant
Route::get('/katalog-tenant', [TenantCatalogController::class, 'show'])->name('tenant.katalog.default');
Route::get('/tenant/{id?}/katalog', [TenantCatalogController::class, 'show'])->name('tenant.katalog');

// Halaman Detail Produk & Pilihan Durasi Booking (Saat tombol "Sewa" diklik)
Route::get('/produk/{id}', [BookingCartController::class, 'showProduct'])->name('produk.show');

// Route Terproteksi Customer (Wajib Login)
Route::middleware(['auth', 'role.user'])->group(function () {
    Route::livewire('/dashboard', 'pages::customer.dashboard')->name('dashboard');
    Route::resource('pengaduan', \App\Http\Controllers\PengaduanPelangganController::class)->only(['index', 'create', 'store', 'show']);

    // Halaman Keranjang Sewa Multi-Tenant & Checkout
    Route::get('/keranjang', [BookingCartController::class, 'cartIndex'])->name('keranjang.index');
    Route::post('/keranjang/tambah', [BookingCartController::class, 'addToCart'])->name('keranjang.add');
    Route::post('/keranjang/hapus', [BookingCartController::class, 'removeFromCart'])->name('keranjang.remove');
    Route::post('/keranjang/checkout', [BookingCartController::class, 'checkout'])->name('keranjang.checkout');
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

// Route SuperAdmin Platform (SRS UC-19, UC-23, UC-24, UC-25)
Route::middleware(['auth', 'role.superadmin'])->prefix('superadmin')->name('superadmin.')->group(function () {
    Route::redirect('/', '/superadmin/dashboard');

    // Halaman 1: Ringkasan & Dashboard + Pusat Aduan
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/pengaduan/{id}/resolve', [DashboardController::class, 'resolveComplaint'])->name('pengaduan.resolve');

    // Halaman 2: Manajemen Tenant Mitra
    Route::get('/tenant/export', [TenantManagementController::class, 'export'])->name('tenant.export');
    Route::get('/tenant', [TenantManagementController::class, 'index'])->name('tenant.index');
    Route::post('/tenant', [TenantManagementController::class, 'store'])->name('tenant.store');
    Route::post('/tenant/{id}/approve', [TenantManagementController::class, 'approve'])->name('tenant.approve');
    Route::post('/tenant/{id}/suspend', [TenantManagementController::class, 'suspend'])->name('tenant.suspend');

    // Halaman 3: Rekapitulasi Komisi Platform
    Route::get('/komisi/export', [KomisiPlatformController::class, 'export'])->name('komisi.export');
    Route::get('/komisi', [KomisiPlatformController::class, 'index'])->name('komisi.index');
    Route::post('/komisi/{id}/verify', [KomisiPlatformController::class, 'verify'])->name('komisi.verify');
});

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

    Route::get('/dev-login-superadmin', function () {
        $user = User::where('role', 'superadmin')->first();
        if (! $user) {
            $user = User::first();
            if ($user) {
                $user->update(['role' => 'superadmin']);
            } else {
                $user = User::create([
                    'id_user_google' => 'google-superadmin-dev',
                    'role' => 'superadmin',
                    'nama_lengkap_ktp' => 'MirrorCam Admin',
                    'nik_ktp' => '3271234567890001',
                    'no_whatsapp' => '081234567890',
                ]);
            }
        }
        Auth::login($user);

        return redirect()->route('superadmin.dashboard')->with('status', 'Berhasil login sebagai SuperAdmin untuk testing.');
    })->name('dev.login.superadmin');
}
