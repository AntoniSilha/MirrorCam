<?php

use App\Models\AccountGoogle;
use App\Models\DetailPemesananTenant;
use App\Models\KategoriProduk;
use App\Models\Produk;
use App\Models\Tenant;
use App\Models\User;
use App\Models\VerifikasiKtp;
use Livewire\Livewire;

function makeDashboardAccount(string $label): AccountGoogle
{
    return AccountGoogle::create([
        'id_user_google' => 'google-'.uniqid(),
        'email' => strtolower(str_replace(' ', '.', $label)).'-'.uniqid().'@example.com',
    ]);
}

function makeDashboardUser(string $name): User
{
    $account = makeDashboardAccount($name);

    return User::create([
        'id_user_google' => $account->id_user_google,
        'role' => 'penyewa',
        'nama_lengkap_ktp' => $name,
        'nik_ktp' => (string) random_int(1000000000000000, 9999999999999999),
        'no_whatsapp' => '081234567890',
        'foto_ktp' => 'ktp/test.jpg',
    ]);
}

function makeDashboardKtp(User $user): VerifikasiKtp
{
    return VerifikasiKtp::create([
        'id_akun' => $user->id_akun,
        'nik_ktp' => $user->nik_ktp,
        'url_foto_ktp' => 'ktp/test.jpg',
        'status_verifikasi' => 'Terverifikasi',
    ]);
}

function makeDashboardTenant(User $owner): Tenant
{
    return Tenant::create([
        'google_id_token' => $owner->id_user_google,
        'id_akun_pemilik' => $owner->id_akun,
        'nama_toko' => 'MirrorCam Studio',
        'whatsapp_resmi' => '6281234567890',
        'nama_penanggungjawab' => $owner->nama_lengkap_ktp,
        'jabatan_penanggungjawab' => 'Pemilik',
        'email_resmi' => 'studio@example.com',
        'alamat_toko' => 'Kebon Jeruk',
        'status_mitra' => 'Aktif',
    ]);
}

function makeDashboardProduct(Tenant $tenant, KategoriProduk $category, string $name): Produk
{
    return Produk::create([
        'id_tenant' => $tenant->id_tenant,
        'id_kategori' => $category->id_kategori,
        'nama_produk' => $name,
        'deskripsi_lengkap' => 'Unit test',
        'harga_sewa' => 100000,
        'total_stok_unit' => 2,
    ]);
}

function makeDashboardOrder(VerifikasiKtp $ktp, Tenant $tenant, Produk $product, array $overrides = []): DetailPemesananTenant
{
    return DetailPemesananTenant::create(array_merge([
        'sub_order_eksternal' => 'MC-'.uniqid(),
        'id_tenant' => $tenant->id_tenant,
        'id_produk' => $product->id_produk,
        'id_ktp' => $ktp->id_ktp,
        'url_foto_ktp' => 'ktp/test.jpg',
        'waktu_ambil_booking' => now()->subDays(2),
        'waktu_kembali_booking' => now()->addDays(2),
        'durasi_sewa_hari' => 4,
        'harga_sewa_booking' => 100000,
        'total_harga' => 400000,
        'status_sewa' => 'Sedang Disewa',
    ], $overrides));
}

beforeEach(function () {
    $this->user = makeDashboardUser('Budi Santoso');
    $this->ktp = makeDashboardKtp($this->user);
    $this->category = KategoriProduk::create(['nama_kategori' => 'Kamera']);
    $this->tenant = makeDashboardTenant($this->user);
    $this->product = makeDashboardProduct($this->tenant, $this->category, 'Sony FX3 Cinema Line');
});

test('guests are redirected to the login page', function () {
    $this->get(route('dashboard'))->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $this->actingAs($this->user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('DASHBOARD USER')
        ->assertSee('Budi Santoso');
});

test('dashboard only displays orders that belong to the authenticated user', function () {
    makeDashboardOrder($this->ktp, $this->tenant, $this->product, [
        'sub_order_eksternal' => 'MC-OWN-001',
    ]);

    $otherUser = makeDashboardUser('Siti Aminah');
    $otherKtp = makeDashboardKtp($otherUser);
    $otherProduct = makeDashboardProduct($this->tenant, $this->category, 'Produk Milik Orang Lain');
    makeDashboardOrder($otherKtp, $this->tenant, $otherProduct, [
        'sub_order_eksternal' => 'MC-OTHER-001',
    ]);

    $this->actingAs($this->user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Sony FX3 Cinema Line')
        ->assertDontSee('Produk Milik Orang Lain');
});

test('dashboard displays filter counts for each order status', function () {
    makeDashboardOrder($this->ktp, $this->tenant, $this->product, ['status_sewa' => 'Sedang Disewa']);
    makeDashboardOrder($this->ktp, $this->tenant, $this->product, ['status_sewa' => 'Selesai']);

    $this->actingAs($this->user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Semua Pesanan (2)')
        ->assertSee('Sedang Disewa (1)')
        ->assertSee('Selesai (1)');
});

test('dashboard can filter orders by sedang disewa', function () {
    $activeProduct = makeDashboardProduct($this->tenant, $this->category, 'Kamera Aktif');
    $finishedProduct = makeDashboardProduct($this->tenant, $this->category, 'Kamera Selesai');
    makeDashboardOrder($this->ktp, $this->tenant, $activeProduct, ['status_sewa' => 'Sedang Disewa']);
    makeDashboardOrder($this->ktp, $this->tenant, $finishedProduct, ['status_sewa' => 'Selesai']);

    $this->actingAs($this->user);

    Livewire::test('pages::customer.dashboard')
        ->assertSet('activeFilter', 'semua')
        ->assertSee('Kamera Aktif')
        ->assertSee('Kamera Selesai')
        ->call('setFilter', 'sedang_disewa')
        ->assertSet('activeFilter', 'sedang_disewa')
        ->assertSee('Kamera Aktif')
        ->assertDontSee('Kamera Selesai');
});

test('dashboard can filter orders by selesai', function () {
    $activeProduct = makeDashboardProduct($this->tenant, $this->category, 'Kamera Aktif');
    $finishedProduct = makeDashboardProduct($this->tenant, $this->category, 'Kamera Selesai');
    makeDashboardOrder($this->ktp, $this->tenant, $activeProduct, ['status_sewa' => 'Sedang Disewa']);
    makeDashboardOrder($this->ktp, $this->tenant, $finishedProduct, ['status_sewa' => 'Selesai']);

    $this->actingAs($this->user);

    Livewire::test('pages::customer.dashboard')
        ->call('setFilter', 'selesai')
        ->assertSet('activeFilter', 'selesai')
        ->assertSee('Kamera Selesai')
        ->assertDontSee('Kamera Aktif');
});

test('dashboard can sort orders from newest to oldest', function () {
    $oldProduct = makeDashboardProduct($this->tenant, $this->category, 'Kamera Lama');
    $newProduct = makeDashboardProduct($this->tenant, $this->category, 'Kamera Baru');
    makeDashboardOrder($this->ktp, $this->tenant, $oldProduct, [
        'waktu_ambil_booking' => now()->subDays(10),
    ]);
    makeDashboardOrder($this->ktp, $this->tenant, $newProduct, [
        'waktu_ambil_booking' => now()->subDay(),
    ]);

    $this->actingAs($this->user);

    Livewire::test('pages::customer.dashboard')
        ->assertSet('activeSort', 'terbaru')
        ->assertSeeInOrder(['Kamera Baru', 'Kamera Lama'])
        ->set('activeSort', 'terlama')
        ->assertSet('activeSort', 'terlama')
        ->assertSeeInOrder(['Kamera Lama', 'Kamera Baru']);
});

test('dashboard applies filter and sort from the query string', function () {
    makeDashboardOrder($this->ktp, $this->tenant, $this->product, [
        'status_sewa' => 'Selesai',
        'waktu_ambil_booking' => now()->subDay(),
    ]);

    $this->actingAs($this->user)
        ->get(route('dashboard', ['filter' => 'selesai', 'sort' => 'terlama']))
        ->assertOk()
        ->assertSee('Sony FX3 Cinema Line');
});
