<?php

use App\Models\AccountGoogle;
use App\Models\DetailPemesananTenant;
use App\Models\KategoriProduk;
use App\Models\PengaduanPelanggan;
use App\Models\Produk;
use App\Models\Tenant;
use App\Models\User;
use App\Models\VerifikasiKtp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

function createTestCustomer(string $name = 'Budi Santoso'): User
{
    $googleId = 'google-'.uniqid();
    AccountGoogle::create([
        'id_user_google' => $googleId,
        'email' => strtolower(str_replace(' ', '.', $name)).'-'.uniqid().'@example.com',
    ]);

    $user = User::create([
        'id_user_google' => $googleId,
        'role' => 'user',
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

function createTestTenant(string $shopName = 'Indo Project'): Tenant
{
    $owner = createTestCustomer('Owner '.$shopName);
    $owner->update(['role' => 'mitra']);

    return Tenant::create([
        'google_id_token' => $owner->id_user_google,
        'id_akun_pemilik' => $owner->id_akun,
        'nama_toko' => $shopName,
        'bio_deskripsi' => 'Pusat sewa gear multimedia',
        'whatsapp_resmi' => '6281234567890',
        'nama_penanggungjawab' => 'Penanggung Jawab',
        'jabatan_penanggungjawab' => 'Manager',
        'email_resmi' => 'shop@example.com',
        'alamat_toko' => 'Jl. Sudirman No. 1, Jakarta',
        'status_mitra' => 'official',
    ]);
}

function createTestBooking(User $customer, Tenant $tenant, string $productName = 'Sony FX30 Cinema Line'): DetailPemesananTenant
{
    $category = KategoriProduk::firstOrCreate(
        ['id_kategori' => 1],
        ['nama_kategori' => 'Kamera Cinema & Mirrorless']
    );

    $product = Produk::create([
        'id_tenant' => $tenant->id_tenant,
        'id_kategori' => $category->id_kategori,
        'nama_produk' => $productName,
        'deskripsi_lengkap' => 'Deskripsi '.$productName,
        'harga_sewa' => 550000,
        'total_stock_unit' => 2,
    ]);

    $ktp = VerifikasiKtp::where('id_akun', $customer->id_akun)->first();

    return DetailPemesananTenant::create([
        'sub_order_eksternal' => 'INV-2026-MC'.random_int(100, 999),
        'id_tenant' => $tenant->id_tenant,
        'id_produk' => $product->id_produk,
        'id_ktp' => $ktp->id_ktp,
        'url_foto_ktp' => 'ktp/sample.jpg',
        'waktu_ambil_booking' => now()->subDays(2),
        'waktu_kembali_booking' => now()->addDays(2),
        'durasi_sewa_hari' => 4,
        'harga_sewa_booking' => 550000,
        'total_harga' => 2200000,
        'status_sewa' => 'Sedang Disewa',
    ]);
}

test('guest cannot access pengaduan page and is redirected to login', function () {
    $response = $this->get(route('pengaduan.index'));
    $response->assertRedirect(route('login'));
});

test('customer can access pengaduan page and sees empty state when no complaints exist', function () {
    $customer = createTestCustomer('Antoni Silha');

    $response = $this->actingAs($customer)->get(route('pengaduan.index'));

    $response->assertOk();
    $response->assertSee('Formulir Laporan & Pengaduan Pelanggaran');
    $response->assertSee('Laporan Rahasia & Terenkripsi');
    $response->assertSee('Nomor Booking / Kode Invoice');
    $response->assertSee('Kategori Dugaan Pelanggaran');
    $response->assertSee('Kirim Laporan');
    $response->assertDontSee('Riwayat Pengaduan Anda');
});

test('customer can see complaint details on the complaint detail page', function () {
    $customer = createTestCustomer('Lucky User');
    $tenant = createTestTenant('Indo Project');
    $booking = createTestBooking($customer, $tenant, 'Sony FX30 Cinema Line Full-Frame + FE 24-70mm f/2.8 GM II');

    $complaint = PengaduanPelanggan::create([
        'nomor_tiket' => 'AMC-2026-0891',
        'id_akun' => $customer->id_akun,
        'id_tenant' => $tenant->id_tenant,
        'id_pemesanan' => $booking->id_pemesanan,
        'kategori_pelanggaran' => 'Sensor Kotor & Malfungsi Optik',
        'waktu_kejadian' => now()->subHours(5),
        'sla_tenggat' => now()->addHours(19),
        'estimasi_klaim' => 1500000,
        'kronologi_kejadian' => 'Terdapat noda oil spot pada sensor saat aperture f/8 ke atas.',
        'status_kasus' => 'Aktif',
        'status_klaim' => 'Escrow Ditahan',
        'pesan_peringatan' => 'Tenggat Waktu Tanggapan Tenant: 03 Jam 45 Menit (Sesuai SLA QC Digital)',
    ]);

    $response = $this->actingAs($customer)->get(route('pengaduan.show', $complaint->nomor_tiket));

    $response->assertOk();
    $response->assertSee('Tiket #AMC-2026-0891');
    $response->assertSee('Indo Project');
    $response->assertSee('Sony FX30 Cinema Line Full-Frame + FE 24-70mm f/2.8 GM II');
    $response->assertSee('Sensor Kotor & Malfungsi Optik');
    $response->assertSee('Terdapat noda oil spot pada sensor');
    $response->assertSee('Rp 1.500.000');
});

test('complaint query strictly isolates data per authenticated customer', function () {
    $customer1 = createTestCustomer('Customer Satu');
    $customer2 = createTestCustomer('Customer Dua');

    $tenant = createTestTenant('Galeri Kamera Solo');
    $booking1 = createTestBooking($customer1, $tenant, 'Canon EOS R6 Mark II');
    $booking2 = createTestBooking($customer2, $tenant, 'Sony Alpha 7 IV');

    PengaduanPelanggan::create([
        'nomor_tiket' => 'AMC-2026-0001',
        'id_akun' => $customer1->id_akun,
        'id_tenant' => $tenant->id_tenant,
        'id_pemesanan' => $booking1->id_pemesanan,
        'kategori_pelanggaran' => 'Kerusakan Fisik Gear & Aksesoris',
        'waktu_kejadian' => now(),
        'kronologi_kejadian' => 'Keluhan rahasia dari Customer Satu.',
        'status_kasus' => 'Aktif',
    ]);

    PengaduanPelanggan::create([
        'nomor_tiket' => 'AMC-2026-0002',
        'id_akun' => $customer2->id_akun,
        'id_tenant' => $tenant->id_tenant,
        'id_pemesanan' => $booking2->id_pemesanan,
        'kategori_pelanggaran' => 'Fasilitas Studio Tidak Berfungsi Optimal',
        'waktu_kejadian' => now(),
        'kronologi_kejadian' => 'Keluhan rahasia dari Customer Dua.',
        'status_kasus' => 'Aktif',
    ]);

    // Each customer can only open their own complaint detail
    $response1 = $this->actingAs($customer1)->get(route('pengaduan.show', 'AMC-2026-0001'));
    $response1->assertOk();
    $response1->assertSee('Keluhan rahasia dari Customer Satu');
    $response1->assertDontSee('Keluhan rahasia dari Customer Dua');

    $response2 = $this->actingAs($customer2)->get(route('pengaduan.show', 'AMC-2026-0002'));
    $response2->assertOk();
    $response2->assertSee('Keluhan rahasia dari Customer Dua');
    $response2->assertDontSee('Keluhan rahasia dari Customer Satu');

    // Complaints belonging to another customer must not be accessible
    $this->actingAs($customer1)
        ->get(route('pengaduan.show', 'AMC-2026-0002'))
        ->assertNotFound();

    $this->actingAs($customer2)
        ->get(route('pengaduan.show', 'AMC-2026-0001'))
        ->assertNotFound();
});

test('customer can submit a new complaint with file upload', function () {
    Storage::fake('public');

    $customer = createTestCustomer('Pelapor Resmi');
    $tenant = createTestTenant('Sewa Pro Surabaya');
    $booking = createTestBooking($customer, $tenant, 'DJI Ronin RS 3 Pro Gimbal');

    $file = UploadedFile::fake()->create('foto_sensor.jpg', 500, 'image/jpeg');

    $response = $this->actingAs($customer)->post(route('pengaduan.store'), [
        'id_pemesanan' => $booking->id_pemesanan,
        'kategori_pelanggaran' => 'Kerusakan Fisik Gear & Aksesoris',
        'waktu_kejadian' => now()->format('Y-m-d\TH:i'),
        'kronologi_kejadian' => 'Terdapat retak pada mount baterai saat membuka segel koper.',
        'bukti_dokumen' => [$file],
        'estimasi_klaim' => 450000,
    ]);

    $response->assertRedirect(route('pengaduan.index'));
    $response->assertSessionHas('status');

    $created = PengaduanPelanggan::where('id_akun', $customer->id_akun)->first();
    expect($created)->not->toBeNull();
    expect($created->kategori_pelanggaran)->toBe('Kerusakan Fisik Gear & Aksesoris');
    expect($created->id_tenant)->toBe($tenant->id_tenant);
    expect($created->nomor_tiket)->toStartWith('AMC-');
    expect($created->url_bukti_dokumen)->not->toBeNull();
});

test('complaint show endpoint returns json detail for ajax/api', function () {
    $customer = createTestCustomer('Antoni Detail');
    $tenant = createTestTenant('Banyuwangi Rental');
    $booking = createTestBooking($customer, $tenant, 'Aputure LS 600d Pro');

    $complaint = PengaduanPelanggan::create([
        'nomor_tiket' => 'AMC-2026-0999',
        'id_akun' => $customer->id_akun,
        'id_tenant' => $tenant->id_tenant,
        'id_pemesanan' => $booking->id_pemesanan,
        'kategori_pelanggaran' => 'Fasilitas Studio Tidak Berfungsi Optimal',
        'waktu_kejadian' => now(),
        'kronologi_kejadian' => 'Lampu LED continuous mati total setelah 15 menit pemakaian.',
        'status_kasus' => 'Selesai',
        'catatan_resolusi' => 'Kompensasi kredit sewa Rp 350.000 telah masuk ke saldo MirrorCam.',
    ]);

    $response = $this->actingAs($customer)->getJson(route('pengaduan.show', $complaint->id_pengaduan));

    $response->assertOk();
    $response->assertJsonPath('success', true);
    $response->assertJsonPath('data.nomor_tiket', 'AMC-2026-0999');
    $response->assertJsonPath('data.kategori', 'Fasilitas Studio Tidak Berfungsi Optimal');
    $response->assertJsonPath('data.status_kasus', 'Selesai');
});
