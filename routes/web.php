<?php

use App\Http\Controllers\MidtransCallbackController;
use App\Http\Controllers\SuperAdmin\DashboardController;
use App\Http\Controllers\SuperAdmin\KomisiPlatformController;
use App\Http\Controllers\SuperAdmin\TenantManagementController;
use App\Livewire\SuperAdmin\Dashboard as SuperAdminDashboard;
use App\Livewire\SuperAdmin\KomisiPlatform as SuperAdminKomisiPlatform;
use App\Livewire\SuperAdmin\TenantManagement as SuperAdminTenantManagement;
use App\Models\User;
use App\Modules\Authentication\Controllers\GoogleRegistrationController;
use App\Modules\TenantManagement\Controllers\MitraRegisterController;
use App\Portals\Customer\Controllers\BookingCartController;
use App\Portals\Customer\Controllers\HomeController;
use App\Portals\Customer\Controllers\OrderTrackingController;
use App\Portals\Customer\Controllers\PaymentController;
use App\Portals\Customer\Controllers\PengaduanPelangganController;
use App\Portals\Customer\Controllers\ProductCatalogController;
use App\Portals\Customer\Controllers\TenantCatalogController;
use App\Portals\Customer\Controllers\TenantController;
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
    Route::livewire('/profil', 'pages::customer.profile')->name('profile.edit');
    Route::resource('pengaduan', PengaduanPelangganController::class)->only(['index', 'create', 'store', 'show']);

    // Halaman Keranjang Sewa Multi-Tenant & Checkout
    Route::get('/keranjang', [BookingCartController::class, 'cartIndex'])->name('keranjang.index');
    Route::post('/keranjang/tambah', [BookingCartController::class, 'addToCart'])->name('keranjang.add');
    Route::post('/keranjang/hapus', [BookingCartController::class, 'removeFromCart'])->name('keranjang.remove');
    Route::post('/keranjang/checkout', [BookingCartController::class, 'checkout'])->name('keranjang.checkout');

    // Halaman Pembayaran QRIS (Midtrans)
    Route::get('/pembayaran/{token}', [PaymentController::class, 'show'])->name('pembayaran.show');
    Route::post('/pembayaran/{token}/qris', [PaymentController::class, 'generate'])->name('pembayaran.qris');
    Route::get('/pembayaran/{token}/status', [PaymentController::class, 'status'])->name('pembayaran.status');
    Route::get('/pembayaran/{token}/sukses', [PaymentController::class, 'success'])->name('pembayaran.sukses');
    Route::get('/pesanan/{bookingId}/status', [OrderTrackingController::class, 'show'])->name('pesanan.status');
    Route::get('/pesanan/{bookingId}/nota', [OrderTrackingController::class, 'receipt'])->name('pesanan.nota');
    Route::post('/pesanan/{bookingId}/komentar', [OrderTrackingController::class, 'storeReview'])->name('pesanan.komentar');
});

// Callback HTTP notification dari Midtrans (tanpa auth, CSRF dikecualikan di bootstrap/app.php)
Route::post('/midtrans/callback', MidtransCallbackController::class)->name('midtrans.callback');

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
    Route::get('/dashboard', SuperAdminDashboard::class)->name('dashboard');
    Route::post('/pengaduan/{id}/resolve', [DashboardController::class, 'resolveComplaint'])->name('pengaduan.resolve');

    // Halaman 2: Manajemen Tenant Mitra
    Route::get('/tenant/export', [TenantManagementController::class, 'export'])->name('tenant.export');
    Route::get('/tenant', SuperAdminTenantManagement::class)->name('tenant.index');
    Route::post('/tenant', [TenantManagementController::class, 'store'])->name('tenant.store');
    Route::post('/tenant/{id}/approve', [TenantManagementController::class, 'approve'])->name('tenant.approve');
    Route::post('/tenant/{id}/suspend', [TenantManagementController::class, 'suspend'])->name('tenant.suspend');

    // Halaman 3: Rekapitulasi Komisi Platform
    Route::get('/komisi/export', [KomisiPlatformController::class, 'export'])->name('komisi.export');
    Route::get('/komisi', SuperAdminKomisiPlatform::class)->name('komisi.index');
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

    Route::get('/dev-login-mitra', function () {
        $user = \App\Models\User::first();
        if (! $user) {
            return 'Tidak ada akun user di database.';
        }
        \Illuminate\Support\Facades\Auth::login($user);

        // Jika user belum memiliki profil tenant, buatkan otomatis untuk testing di lokal
        $tenant = \App\Models\Tenant::where('id_akun_pemilik', $user->id_akun)->first();
        if (! $tenant && $user->id_user_google) {
            \App\Models\Tenant::create([
                'google_id_token' => $user->id_user_google,
                'id_akun_pemilik' => $user->id_akun,
                'nama_toko' => 'MirrorCam Rental Store',
                'bio_deskripsi' => 'Pusat sewa kamera dan lensa profesional.',
                'whatsapp_resmi' => $user->no_whatsapp ?: '081234567890',
                'nama_penanggungjawab' => $user->name,
                'jabatan_penanggungjawab' => 'Owner',
                'email_resmi' => $user->email ?: 'mitra@mirrorcam.test',
                'alamat_toko' => 'Jl. Sudirman No. 12, Jakarta',
                'status_mitra' => 'verified',
            ]);
        }

        return redirect()->route('mitra.dashboard')->with('status', 'Berhasil login sebagai Mitra (' . $user->name . ').');
    })->name('dev.login.mitra');

    Route::get('/dev-create-order', function () {
        $user = \App\Models\User::first();
        if (! $user) {
            return 'User belum ada.';
        }
        $tenant = \App\Models\Tenant::where('id_akun_pemilik', $user->id_akun)->first();
        if (! $tenant) {
            return redirect()->route('dev.login.mitra');
        }
        $kategori = \App\Models\KategoriProduk::first();
        $produk = \App\Models\Produk::firstOrCreate(
            ['id_tenant' => $tenant->id_tenant, 'nama_produk' => 'Sony Alpha A7 IV Body Only'],
            [
                'id_kategori' => $kategori ? $kategori->id_kategori : 1,
                'deskripsi_lengkap' => 'Kamera mirrorless full-frame profesional 33MP.',
                'harga_sewa' => 250000,
                'total_stock_unit' => 5,
            ]
        );
        $ktp = \App\Models\VerifikasiKtp::firstOrCreate(
            ['id_akun' => $user->id_akun],
            [
                'nik_ktp' => $user->nik_ktp ?: '3510121611060002',
                'url_foto_ktp' => $user->foto_ktp ?: 'ktp/sample.jpg',
                'status_verifikasi' => 'terverifikasi',
                'waktu_verifikasi' => now(),
            ]
        );
        $booking = \App\Models\DetailPemesananTenant::create([
            'sub_order_eksternal' => 'MC-ORD-' . rand(1000, 9999),
            'id_tenant' => $tenant->id_tenant,
            'id_produk' => $produk->id_produk,
            'id_ktp' => $ktp->id_ktp,
            'url_foto_ktp' => $ktp->url_foto_ktp,
            'waktu_ambil_booking' => now(),
            'waktu_kembali_booking' => now()->addDays(2),
            'durasi_sewa_hari' => 2,
            'harga_sewa_booking' => 250000,
            'total_harga' => 500000,
            'status_harga' => 'dp',
            'nominal_dp' => 250000,
            'sisa_tagihan' => 250000,
            'is_payment_verified' => false,
            'status_sewa' => 'menunggu_serah_terima',
        ]);

        return redirect()->route('mitra.dashboard')->with('status', 'Pesanan testing ' . $booking->sub_order_eksternal . ' berhasil dibuat untuk menguji alur checklist!');
    })->name('dev.create.order');
}
