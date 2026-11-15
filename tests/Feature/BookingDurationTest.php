<?php

use App\Models\AccountGoogle;
use App\Models\DurasiProduk;
use App\Models\KategoriProduk;
use App\Models\Produk;
use App\Models\Tenant;
use App\Models\User;
use App\Models\VerifikasiKtp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

function bookingTestCreateUser(string $role = 'user'): User
{
    $googleId = 'google-booking-'.uniqid();

    AccountGoogle::create([
        'id_user_google' => $googleId,
        'email' => $googleId.'@example.com',
    ]);

    $user = User::create([
        'id_user_google' => $googleId,
        'role' => $role,
        'nama_lengkap_ktp' => 'Booking Test User',
        'nik_ktp' => (string) random_int(1000000000000000, 9999999999999999),
        'no_whatsapp' => '081234567890',
        'foto_ktp' => 'ktp/test.jpg',
    ]);

    VerifikasiKtp::create([
        'id_akun' => $user->id_akun,
        'nik_ktp' => $user->nik_ktp,
        'url_foto_ktp' => 'ktp/test.jpg',
        'status_verifikasi' => 'Terverifikasi',
    ]);

    return $user;
}

function bookingTestCreateProduct(): Produk
{
    $owner = bookingTestCreateUser('mitra');

    $tenant = Tenant::create([
        'google_id_token' => $owner->id_user_google,
        'id_akun_pemilik' => $owner->id_akun,
        'nama_toko' => 'Booking Test Tenant',
        'bio_deskripsi' => 'Tenant untuk pengujian durasi booking',
        'whatsapp_resmi' => '6281298765432',
        'nama_penanggungjawab' => 'Penanggung Jawab',
        'jabatan_penanggungjawab' => 'Head of Ops',
        'email_resmi' => 'booking-tenant@example.com',
        'alamat_toko' => 'Jl. Kamera No. 10, Jakarta',
        'status_mitra' => 'aktif',
    ]);

    $category = KategoriProduk::create([
        'id_kategori' => 1,
        'nama_kategori' => 'Kamera Cinema & Mirrorless',
    ]);

    $stockCol = Schema::hasColumn('produks', 'total_stok_unit')
        ? 'total_stok_unit'
        : 'total_stock_unit';

    $produk = Produk::create([
        'id_tenant' => $tenant->id_tenant,
        'id_kategori' => $category->id_kategori,
        'nama_produk' => 'Sony Booking Test',
        'deskripsi_lengkap' => 'Produk untuk pengujian durasi booking',
        'color_produk' => 'black',
        'harga_sewa' => 150000,
        $stockCol => 5,
    ]);

    DurasiProduk::create([
        'id_produk' => $produk->id_produk,
        'durasi' => '12_jam',
        'nama_durasi' => '12 Jam',
        'harga' => 145000,
    ]);

    return $produk;
}

it('rejects booking with a duration the tenant does not offer', function () {
    $user = bookingTestCreateUser();
    $produk = bookingTestCreateProduct();

    $response = $this->actingAs($user)->post(route('keranjang.add'), [
        'id_produk' => $produk->id_produk,
        'durasi_kode' => '3_bulan',
        'durasi_label' => 'Durasi sewa: 3 Bulan',
        'warna' => 'black',
        'total_harga' => 1000,
    ]);

    $response->assertSessionHasErrors('durasi_kode');
    expect(session('mirrorcam_cart'))->toBeNull();
});

it('uses the configured duration price when adding to cart', function () {
    $user = bookingTestCreateUser();
    $produk = bookingTestCreateProduct();

    $response = $this->actingAs($user)->post(route('keranjang.add'), [
        'id_produk' => $produk->id_produk,
        'durasi_kode' => '12_jam',
        'durasi_label' => 'Durasi sewa: 12 Jam',
        'warna' => 'black',
        'total_harga' => 1000,
    ]);

    $response->assertRedirect(route('keranjang.index'));

    $cart = session('mirrorcam_cart');

    expect($cart)->toHaveCount(1)
        ->and(array_values($cart)[0]['harga'])->toBe(145000)
        ->and(array_values($cart)[0]['durasi_label'])->toBe('Durasi sewa: 12 Jam');
});
