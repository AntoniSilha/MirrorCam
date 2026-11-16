<?php

use App\Models\AccountGoogle;
use App\Models\DetailPemesananTenant;
use App\Models\DurasiProduk;
use App\Models\KategoriProduk;
use App\Models\Produk;
use App\Models\ReviewUlasanTenant;
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
        'status_mitra' => 'verified',
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

it('shows the real review rating and count on the product page', function () {
    $produk = bookingTestCreateProduct();
    $penyewa = bookingTestCreateUser();
    $ktp = VerifikasiKtp::where('id_akun', $penyewa->id_akun)->firstOrFail();

    $pemesanan = DetailPemesananTenant::create([
        'sub_order_eksternal' => 'SUB-RATING-TEST',
        'id_tenant' => $produk->id_tenant,
        'id_produk' => $produk->id_produk,
        'id_ktp' => $ktp->id_ktp,
        'url_foto_ktp' => 'ktp/test.jpg',
        'waktu_ambil_booking' => now(),
        'waktu_kembali_booking' => now()->addDay(),
        'durasi_sewa_hari' => 1,
        'harga_sewa_booking' => 145000,
        'total_harga' => 145000,
        'status_sewa' => 'Selesai',
    ]);

    ReviewUlasanTenant::create([
        'id_akun_penyewa' => $penyewa->id_akun,
        'id_pemesanan' => $pemesanan->id_pemesanan,
        'id_tenant' => $produk->id_tenant,
        'rating_nilai' => 4,
        'komentar_text' => 'Bagus',
    ]);

    ReviewUlasanTenant::create([
        'id_akun_penyewa' => $penyewa->id_akun,
        'id_pemesanan' => $pemesanan->id_pemesanan,
        'id_tenant' => $produk->id_tenant,
        'rating_nilai' => 5,
        'komentar_text' => 'Sangat bagus',
    ]);

    $this->get(route('produk.show', $produk->id_produk))
        ->assertOk()
        ->assertSee('4.5')
        ->assertSee('2 ulasan');
});

it('shows the real cart quantity in the navbar', function () {
    $produk = bookingTestCreateProduct();

    $this->withSession(['mirrorcam_cart' => [
        'item-a' => ['id_produk' => $produk->id_produk, 'qty' => 1],
        'item-b' => ['id_produk' => $produk->id_produk, 'qty' => 2],
    ]])->get(route('home'))
        ->assertOk()
        ->assertSee('Keranjang Belanja (3 item)');
});

it('ignores cart entries whose product no longer exists', function () {
    $this->withSession(['mirrorcam_cart' => [
        'stale' => ['id_produk' => 999999, 'qty' => 3],
    ]])->get(route('home'))
        ->assertOk()
        ->assertSee('Keranjang Belanja (0 item)')
        ->assertSessionHas('mirrorcam_cart', []);
});
