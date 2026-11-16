<?php

use App\Models\AccountGoogle;
use App\Models\DetailOperasionalTenant;
use App\Models\DetailPemesananTenant;
use App\Models\KategoriProduk;
use App\Models\Produk;
use App\Models\ReviewUlasanTenant;
use App\Models\Tenant;
use App\Models\User;
use App\Models\VerifikasiKtp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

function helperCreateUser(string $name = 'User Test', string $role = 'user'): User
{
    $googleId = 'google-'.uniqid();
    AccountGoogle::create([
        'id_user_google' => $googleId,
        'email' => strtolower(str_replace(' ', '.', $name)).'-'.uniqid().'@example.com',
    ]);

    $user = User::create([
        'id_user_google' => $googleId,
        'role' => $role,
        'nama_lengkap_ktp' => $name,
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

function helperCreateTenant(string $name, string $city = 'Jakarta', string $status = 'verified'): Tenant
{
    $owner = helperCreateUser('Owner '.$name, 'mitra');

    $tenant = Tenant::create([
        'google_id_token' => $owner->id_user_google,
        'id_akun_pemilik' => $owner->id_akun,
        'nama_toko' => $name,
        'bio_deskripsi' => 'Deskripsi spesialisasi gear '.$name,
        'whatsapp_resmi' => '6281298765432',
        'nama_penanggungjawab' => 'Penanggung Jawab '.$name,
        'jabatan_penanggungjawab' => 'Head of Ops',
        'email_resmi' => strtolower(str_replace(' ', '', $name)).'@tenant.test',
        'alamat_toko' => 'Jl. Kamera No. 10, '.$city,
        'status_mitra' => $status,
    ]);

    DetailOperasionalTenant::create([
        'id_tenant' => $tenant->id_tenant,
        'jam_buka' => '08:00:00',
        'jam_tutup' => '21:00:00',
        'hari_operasional' => 'Senin - Minggu',
    ]);

    return $tenant;
}

function helperCreateProduct(array $attributes): Produk
{
    $stockCol = Schema::hasColumn('produks', 'total_stok_unit')
        ? 'total_stok_unit'
        : 'total_stock_unit';

    $stock = $attributes['total_stok_unit'] ?? $attributes['total_stock_unit'] ?? 1;
    unset($attributes['total_stok_unit'], $attributes['total_stock_unit']);
    $attributes[$stockCol] = $stock;

    return Produk::create($attributes);
}

it('displays empty state when no active tenants are present in database', function () {
    $response = $this->get(route('tenant.index'));

    $response->assertStatus(200);
    $response->assertSee('Eksplorasi Tenant');
    $response->assertSee('Belum Ada Mitra Tenant Aktif');
    $response->assertSee('Belum ada mitra tenant aktif yang tersedia saat ini.');
    $response->assertDontSee('Muat Lebih Banyak Tenan');
});

it('displays active tenants dynamically from database', function () {
    $tenant = helperCreateTenant('Indonesia Gallery', 'Banyuwangi', 'verified');

    $category = KategoriProduk::create([
        'id_kategori' => 1,
        'nama_kategori' => 'Kamera Cinema & Mirrorless',
    ]);

    helperCreateProduct([
        'id_tenant' => $tenant->id_tenant,
        'id_kategori' => $category->id_kategori,
        'nama_produk' => 'Sony FX3 Cinema Line',
        'deskripsi_lengkap' => 'Cinema line full frame kamera',
        'harga_sewa' => 750000,
        'total_stock_unit' => 12,
    ]);

    $response = $this->get(route('tenant.index'));

    $response->assertStatus(200);
    $response->assertSee('Indonesia Gallery');
    $response->assertSee('IG');
    $response->assertSee('Banyuwangi');
    $response->assertSee('Rental Kamera');
    $response->assertSee('Unit Siap Pakai');
    $response->assertSee('12');
    $response->assertSee('Kunjungi Tenant');
    $response->assertSee(route('tenant.show', $tenant->slug));
});

it('excludes unverified tenants from public exploration (SRS UC-02 & UC-23)', function () {
    $activeTenant = helperCreateTenant('Toko Aktif Jaya', 'Surabaya', 'verified');
    $pendingTenant = helperCreateTenant('Toko Pending', 'Bandung', 'noverified');
    $inactiveTenant = helperCreateTenant('Toko Nonaktif', 'Medan', 'noverified');

    $response = $this->get(route('tenant.index'));

    $response->assertStatus(200);
    $response->assertSee('Toko Aktif Jaya');
    $response->assertDontSee('Toko Pending');
    $response->assertDontSee('Toko Nonaktif');
});

it('dynamically determines service category subtitle based on products', function () {
    $tenantPhysicalOnly = helperCreateTenant('Focus Gear', 'Yogyakarta', 'verified');
    $categoryCamera = KategoriProduk::create(['id_kategori' => 10, 'nama_kategori' => 'Kamera']);
    helperCreateProduct([
        'id_tenant' => $tenantPhysicalOnly->id_tenant,
        'id_kategori' => $categoryCamera->id_kategori,
        'nama_produk' => 'Canon EOS R5',
        'deskripsi_lengkap' => 'Mirrorless 8K',
        'harga_sewa' => 500000,
        'total_stock_unit' => 5,
    ]);

    $tenantWithStudio = helperCreateTenant('Sun Creative Studio', 'Semarang', 'verified');
    $categoryStudio = KategoriProduk::create(['id_kategori' => 11, 'nama_kategori' => 'Studio Editing']);
    helperCreateProduct([
        'id_tenant' => $tenantWithStudio->id_tenant,
        'id_kategori' => $categoryStudio->id_kategori,
        'nama_produk' => 'Mac Studio M2 Max Suite',
        'deskripsi_lengkap' => 'Studio pasca-produksi',
        'harga_sewa' => 1500000,
        'total_stock_unit' => 2,
    ]);

    $response = $this->get(route('tenant.index'));

    $response->assertStatus(200);
    $response->assertSee('Focus Gear');
    $response->assertSee('Rental Kamera');
    $response->assertSee('Sun Creative Studio');
    $response->assertSee('Rental Kamera & Studio');
});

it('filters tenants dynamically by search keyword', function () {
    helperCreateTenant('Indonesia Gallery', 'Banyuwangi', 'verified');
    helperCreateTenant('Indo Project', 'Jakarta', 'verified');

    $responseSearchBanyuwangi = $this->get(route('tenant.index', ['search' => 'Banyuwangi']));
    $responseSearchBanyuwangi->assertStatus(200);
    $responseSearchBanyuwangi->assertSee('Indonesia Gallery');
    $responseSearchBanyuwangi->assertDontSee('Indo Project');

    $responseSearchProject = $this->get(route('tenant.index', ['search' => 'Project']));
    $responseSearchProject->assertStatus(200);
    $responseSearchProject->assertSee('Indo Project');
    $responseSearchProject->assertDontSee('Indonesia Gallery');
});

it('filters tenants dynamically by rating pill', function () {
    $customer = helperCreateUser('Customer Ulasan');
    $tenantHigh = helperCreateTenant('Toko Bintang Lima', 'Malang', 'verified');
    $tenantLow = helperCreateTenant('Toko Bintang Tiga', 'Solo', 'verified');

    $cat = KategoriProduk::create(['id_kategori' => 20, 'nama_kategori' => 'Kamera']);
    $prod1 = helperCreateProduct([
        'id_tenant' => $tenantHigh->id_tenant,
        'id_kategori' => $cat->id_kategori,
        'nama_produk' => 'Kamera A',
        'deskripsi_lengkap' => 'Deskripsi',
        'harga_sewa' => 100000,
        'total_stock_unit' => 1,
    ]);
    $prod2 = helperCreateProduct([
        'id_tenant' => $tenantLow->id_tenant,
        'id_kategori' => $cat->id_kategori,
        'nama_produk' => 'Kamera B',
        'deskripsi_lengkap' => 'Deskripsi',
        'harga_sewa' => 100000,
        'total_stock_unit' => 1,
    ]);

    $ktp = VerifikasiKtp::where('id_akun', $customer->id_akun)->first();
    $pemesanan1 = DetailPemesananTenant::create([
        'sub_order_eksternal' => 'ORD-1',
        'id_tenant' => $tenantHigh->id_tenant,
        'id_produk' => $prod1->id_produk,
        'id_ktp' => $ktp->id_ktp,
        'url_foto_ktp' => 'ktp/test.jpg',
        'waktu_ambil_booking' => now(),
        'waktu_kembali_booking' => now()->addDays(1),
        'durasi_sewa_hari' => 1,
        'harga_sewa_booking' => 100000,
        'total_harga' => 100000,
        'status_sewa' => 'Selesai',
    ]);
    $pemesanan2 = DetailPemesananTenant::create([
        'sub_order_eksternal' => 'ORD-2',
        'id_tenant' => $tenantLow->id_tenant,
        'id_produk' => $prod2->id_produk,
        'id_ktp' => $ktp->id_ktp,
        'url_foto_ktp' => 'ktp/test.jpg',
        'waktu_ambil_booking' => now(),
        'waktu_kembali_booking' => now()->addDays(1),
        'durasi_sewa_hari' => 1,
        'harga_sewa_booking' => 100000,
        'total_harga' => 100000,
        'status_sewa' => 'Selesai',
    ]);

    ReviewUlasanTenant::create([
        'id_akun_penyewa' => $customer->id_akun,
        'id_pemesanan' => $pemesanan1->id_pemesanan,
        'id_tenant' => $tenantHigh->id_tenant,
        'rating_nilai' => 5,
        'komentar_text' => 'Pelayanan sempurna bintang 5!',
    ]);

    ReviewUlasanTenant::create([
        'id_akun_penyewa' => $customer->id_akun,
        'id_pemesanan' => $pemesanan2->id_pemesanan,
        'id_tenant' => $tenantLow->id_tenant,
        'rating_nilai' => 3,
        'komentar_text' => 'Standar bintang 3.',
    ]);

    $responseRating5 = $this->get(route('tenant.index', ['rating' => '5.0']));
    $responseRating5->assertStatus(200);
    $responseRating5->assertSee('Toko Bintang Lima');
    $responseRating5->assertDontSee('Toko Bintang Tiga');

    $responseRating3 = $this->get(route('tenant.index', ['rating' => '3.0']));
    $responseRating3->assertStatus(200);
    $responseRating3->assertSee('Toko Bintang Tiga');
    $responseRating3->assertDontSee('Toko Bintang Lima');
});

it('shows load more button when remaining tenants > 0 and hides when <= 9', function () {
    for ($i = 1; $i <= 11; $i++) {
        helperCreateTenant("Mitra Tenant Ke-{$i}", 'Jakarta', 'verified');
    }

    // Default per_page is 9, so 11 - 9 = 2 remaining
    $response = $this->get(route('tenant.index'));
    $response->assertStatus(200);
    $response->assertSee('Muat Lebih Banyak Tenan (Tersedia 2 Lainnya)');

    // Loading 18 items displays all 11, button should disappear
    $responseAll = $this->get(route('tenant.index', ['per_page' => 18]));
    $responseAll->assertStatus(200);
    $responseAll->assertDontSee('Muat Lebih Banyak Tenan');
});

it('displays tenant detail page at /tenant/{slug}', function () {
    $tenant = helperCreateTenant('Indonesia Gallery', 'Banyuwangi', 'verified');

    $response = $this->get(route('tenant.show', $tenant->slug));

    $response->assertStatus(200);
    $response->assertSee('Indonesia Gallery');
    $response->assertSee('Banyuwangi');
    $response->assertSee('Katalog Gear Tersedia');
    $response->assertSee('Kembali ke Eksplorasi Tenant');
});

it('returns 404 for non-existent slug and still shows unverified tenant detail', function () {
    $unverified = helperCreateTenant('Toko Belum Verifikasi', 'Padang', 'noverified');

    $this->get(route('tenant.show', 'unknown-slug'))->assertStatus(404);
    $this->get(route('tenant.show', $unverified->slug))->assertStatus(200);
});
