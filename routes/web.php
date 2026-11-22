<?php

use App\Http\Controllers\MitraRegisterController;
use App\Modules\Authentication\Controllers\GoogleRegistrationController;
use Illuminate\Support\Facades\Route;
use App\Portals\Customer\Controllers\HomeController;

// use Laravel\Mcp\Request;

Route::get('/', [HomeController::class, 'index'])->name('home');
// Route Register & Login Fallback
Route::get('/register', [GoogleRegistrationController::class, 'redirect'])->name('register');
Route::get('/login', [GoogleRegistrationController::class, 'redirect'])->name('login');

Route::middleware(['auth', 'role.user'])->group(function () {
    Route::livewire('/dashboard', 'pages::customer.dashboard')->name('dashboard');
    Route::resource('pengaduan', \App\Http\Controllers\PengaduanPelangganController::class)->only(['index', 'create', 'store', 'show']);
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
        $user = \App\Models\User::first();
        if ($user) {
            \Illuminate\Support\Facades\Auth::login($user);
            return redirect()->route('home')->with('status', 'Berhasil login sebagai ' . $user->name . ' untuk testing.');
        }
        return 'Tidak ada akun user di database.';
    })->name('dev.login');

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
                'status_mitra' => 'aktif',
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
