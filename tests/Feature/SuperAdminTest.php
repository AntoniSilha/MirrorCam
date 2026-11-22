<?php

use App\Livewire\SuperAdmin\Dashboard;
use App\Livewire\SuperAdmin\TenantManagement;
use App\Models\AccountGoogle;
use App\Models\AuditLog;
use App\Models\DetailOperasionalTenant;
use App\Models\DetailPemesananTenant;
use App\Models\KategoriProduk;
use App\Models\KomisiPlatform;
use App\Models\PengaduanPelanggan;
use App\Models\Produk;
use App\Models\Tenant;
use App\Models\User;
use App\Models\VerifikasiKtp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function helperCreateSuperAdminUser(string $role = 'superadmin'): User
{
    $googleId = 'google-'.uniqid();
    AccountGoogle::create([
        'id_user_google' => $googleId,
        'email' => 'admin-'.uniqid().'@mirrorcam.test',
    ]);

    $user = User::create([
        'id_user_google' => $googleId,
        'role' => $role,
        'nama_lengkap_ktp' => 'SuperAdmin MirrorCam',
        'nik_ktp' => (string) random_int(1000000000000000, 9999999999999999),
        'no_whatsapp' => '081234567890',
        'foto_ktp' => 'ktp/superadmin.jpg',
    ]);

    VerifikasiKtp::create([
        'id_akun' => $user->id_akun,
        'nik_ktp' => $user->nik_ktp,
        'url_foto_ktp' => 'ktp/superadmin.jpg',
        'status_verifikasi' => 'Terverifikasi',
    ]);

    return $user;
}

function helperCreateDummyTenant(string $name = 'Jakarta Cine Gear', string $status = 'verified', string $city = 'Jakarta Selatan'): Tenant
{
    $owner = helperCreateSuperAdminUser('mitra');

    $tenant = Tenant::create([
        'google_id_token' => $owner->id_user_google,
        'id_akun_pemilik' => $owner->id_akun,
        'nama_toko' => $name,
        'bio_deskripsi' => 'Deskripsi toko '.$name,
        'whatsapp_resmi' => '081298765432',
        'nama_penanggungjawab' => 'Budi Santoso',
        'jabatan_penanggungjawab' => 'Head of Rental',
        'email_resmi' => 'contact@'.strtolower(str_replace(' ', '', $name)).'.test',
        'alamat_toko' => 'Jl. Gatot Subroto No. 45, '.$city,
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

function helperCreateDummyBooking(User $customer, Tenant $tenant, float $totalHarga = 2000000, string $status = 'Selesai'): DetailPemesananTenant
{
    $category = KategoriProduk::firstOrCreate(
        ['id_kategori' => 1],
        ['nama_kategori' => 'Kamera Cinema & Mirrorless']
    );

    $stockCol = Schema::hasColumn('produks', 'total_stok_unit')
        ? 'total_stok_unit'
        : 'total_stock_unit';

    $product = Produk::create([
        'id_tenant' => $tenant->id_tenant,
        'id_kategori' => $category->id_kategori,
        'nama_produk' => 'Sony FX3 Cinema Line',
        'deskripsi_lengkap' => 'Cinema Camera Full-Frame',
        'harga_sewa' => 500000,
        $stockCol => 5,
    ]);

    $ktp = VerifikasiKtp::where('id_akun', $customer->id_akun)->first();

    return DetailPemesananTenant::create([
        'sub_order_eksternal' => 'ORD-'.strtoupper(uniqid()),
        'id_tenant' => $tenant->id_tenant,
        'id_produk' => $product->id_produk,
        'id_ktp' => $ktp->id_ktp,
        'url_foto_ktp' => 'ktp/sample.jpg',
        'waktu_ambil_booking' => now()->subDays(3),
        'waktu_kembali_booking' => now()->subDay(),
        'durasi_sewa_hari' => 2,
        'harga_sewa_booking' => 500000,
        'total_harga' => $totalHarga,
        'status_sewa' => $status,
    ]);
}

// -------------------------------------------------------------
// 1. ACCESS CONTROL & ROLE MIDDLEWARE (NFR-SEC-04, UC-19)
// -------------------------------------------------------------

test('guests are redirected from superadmin dashboard to login', function () {
    $response = $this->get('/superadmin/dashboard');
    $response->assertRedirect('/login');
});

test('regular users receive 403 forbidden when accessing superadmin pages', function () {
    $user = helperCreateSuperAdminUser('user');

    $this->actingAs($user)->get('/superadmin/dashboard')->assertForbidden();
    $this->actingAs($user)->get('/superadmin/tenant')->assertForbidden();
    $this->actingAs($user)->get('/superadmin/komisi')->assertForbidden();
});

test('superadmin can access all 3 dashboard pages successfully', function () {
    $superadmin = helperCreateSuperAdminUser('superadmin');

    $this->actingAs($superadmin)
        ->get('/superadmin/dashboard')
        ->assertOk()
        ->assertSee('Dashboard MirrorCam')
        ->assertSee('Pusat Aduan');

    $this->actingAs($superadmin)
        ->get('/superadmin/tenant')
        ->assertOk()
        ->assertSee('Manajemen Tenant Mitra');

    $this->actingAs($superadmin)
        ->get('/superadmin/komisi')
        ->assertOk()
        ->assertSee('Rekapitulasi Komisi Platform');
});

// -------------------------------------------------------------
// 2. DASHBOARD & PUSAT ADUAN (UC-19, UC-25, NFR-AUD-01)
// -------------------------------------------------------------

test('dashboard loads cleanly when database is empty', function () {
    $superadmin = helperCreateSuperAdminUser('superadmin');

    $response = $this->actingAs($superadmin)->get('/superadmin/dashboard');

    $response->assertOk();
    $response->assertSee('Total Tenant Mitra');
    $response->assertSee('Total Pesanan Sewa');
    $response->assertSee('Pendapatan Komisi');
    $response->assertSee('Belum Ada Laporan Pengaduan');
});

test('superadmin can resolve customer complaint and record audit log', function () {
    $superadmin = helperCreateSuperAdminUser('superadmin');
    $customer = helperCreateSuperAdminUser('user');
    $tenant = helperCreateDummyTenant('Bandung Cinema Lab');
    $booking = helperCreateDummyBooking($customer, $tenant, 1500000, 'Sedang Disewa');

    $complaint = PengaduanPelanggan::create([
        'nomor_tiket' => 'TKT-20261115-001',
        'id_akun' => $customer->id_akun,
        'id_tenant' => $tenant->id_tenant,
        'id_pemesanan' => $booking->id_pemesanan,
        'kategori_pelanggaran' => 'Alat Rusak / Cacat',
        'kronologi_kejadian' => 'Lensa 24-70mm autofocus macet saat digunakan.',
        'url_bukti_dokumen' => 'complaints/lens.jpg',
        'status_kasus' => 'aktif',
    ]);

    $response = $this->actingAs($superadmin)->post("/superadmin/pengaduan/{$complaint->id_pengaduan}/resolve", [
        'justifikasi' => 'Tenant bersedia mengganti unit lensa baru dalam 2 jam.',
        'keputusan' => 'Ganti Unit',
    ]);

    $response->assertRedirect();
    $complaint->refresh();

    expect($complaint->status_kasus)->toBe('Selesai');
    expect($complaint->catatan_resolusi)->toBe('Tenant bersedia mengganti unit lensa baru dalam 2 jam.');

    // Verify AuditLog written (SRS NFR-AUD-01 & NFR-DAT-04)
    $auditLog = AuditLog::where('action', 'pengaduan_resolved')
        ->where('auditable_id', $complaint->id_pengaduan)
        ->first();

    expect($auditLog)->not->toBeNull();
    expect($auditLog->user_id)->toBe($superadmin->id_akun);
    expect($auditLog->description)->toContain('SuperAdmin menyelesaikan sengketa pengaduan tiket TKT-20261115-001');
});

// -------------------------------------------------------------
// 3. MANAJEMEN TENANT MITRA (UC-23, NFR-AUD-01)
// -------------------------------------------------------------

test('superadmin can approve pending tenant and record audit log', function () {
    $superadmin = helperCreateSuperAdminUser('superadmin');
    $tenant = helperCreateDummyTenant('Bali Prime Camera', 'noverified');

    $response = $this->actingAs($superadmin)->post("/superadmin/tenant/{$tenant->id_tenant}/approve");

    $response->assertRedirect();
    $tenant->refresh();

    expect(strtolower($tenant->status_mitra))->toBe('verified');

    $auditLog = AuditLog::where('action', 'tenant_approved')
        ->where('auditable_id', $tenant->id_tenant)
        ->first();

    expect($auditLog)->not->toBeNull();
    expect($auditLog->user_id)->toBe($superadmin->id_akun);
    expect($auditLog->description)->toContain('Bali Prime Camera');
});

test('superadmin can suspend tenant with reason and record audit log', function () {
    $superadmin = helperCreateSuperAdminUser('superadmin');
    $tenant = helperCreateDummyTenant('Surabaya Optics Rental', 'verified');

    $response = $this->actingAs($superadmin)->post("/superadmin/tenant/{$tenant->id_tenant}/suspend", [
        'alasan' => 'Banyak laporan pelanggan mengenai alat tidak terawat.',
    ]);

    $response->assertRedirect();
    $tenant->refresh();

    expect(strtolower($tenant->status_mitra))->toBe('noverified');

    $auditLog = AuditLog::where('action', 'tenant_suspended')
        ->where('auditable_id', $tenant->id_tenant)
        ->first();

    expect($auditLog)->not->toBeNull();
    expect($auditLog->user_id)->toBe($superadmin->id_akun);
    expect($auditLog->details['alasan'])->toBe('Banyak laporan pelanggan mengenai alat tidak terawat.');
});

test('superadmin can manually onboard a tenant and record audit log', function () {
    $superadmin = helperCreateSuperAdminUser('superadmin');

    $payload = [
        'nama_toko' => 'Jogja Film Gear',
        'nama_penanggungjawab' => 'Arya Sena',
        'whatsapp_resmi' => '081399887766',
        'email_resmi' => 'arya@jogjafilm.id',
        'kota' => 'Sleman, DI Yogyakarta',
        'alamat_toko' => 'Jl. Kaliurang KM 9, Sleman',
        'status_mitra' => 'verified',
        'nama_bank' => 'BCA',
        'nomor_rekening' => '8899001122',
        'nama_pemilik_rekening' => 'Arya Sena',
    ];

    $response = $this->actingAs($superadmin)->post('/superadmin/tenant', $payload);

    $response->assertRedirect();

    $tenant = Tenant::where('nama_toko', 'Jogja Film Gear')->first();
    expect($tenant)->not->toBeNull();
    expect($tenant->rekeningSettlement)->not->toBeNull();
    expect($tenant->rekeningSettlement->nomor_rekening)->toBe('8899001122');

    $auditLog = AuditLog::where('action', 'tenant_created_manual')
        ->where('auditable_id', $tenant->id_tenant)
        ->first();

    expect($auditLog)->not->toBeNull();
    expect($auditLog->user_id)->toBe($superadmin->id_akun);
});

test('superadmin can export tenant list to csv', function () {
    $superadmin = helperCreateSuperAdminUser('superadmin');
    helperCreateDummyTenant('Export Shop 1');

    $response = $this->actingAs($superadmin)->get('/superadmin/tenant/export');

    $response->assertOk();
    $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    expect($response->getContent())->toContain('ID Tenant,Nama Toko,PIC');
    expect($response->getContent())->toContain('Export Shop 1');
});

// -------------------------------------------------------------
// 4. REKAPITULASI KOMISI PLATFORM (UC-24, NFR-DAT-09, NFR-AUD-01)
// -------------------------------------------------------------

test('superadmin can view commission page with integer Rupiah formatting', function () {
    $superadmin = helperCreateSuperAdminUser('superadmin');
    $customer = helperCreateSuperAdminUser('user');
    $tenant = helperCreateDummyTenant('Cinema Gear ID');
    $booking = helperCreateDummyBooking($customer, $tenant, 2000000, 'Selesai');

    $komisi = KomisiPlatform::create([
        'id_pemesanan' => $booking->id_pemesanan,
        'kode_komisi' => 'KM-99001',
        'total_komisi' => 200000,
        'status_komisi' => 'Menunggu',
    ]);

    $response = $this->actingAs($superadmin)->get('/superadmin/komisi');

    $response->assertOk();
    $response->assertSee('KM-99001');
    $response->assertSee('Rp 2.000.000');
    $response->assertSee('Rp 200.000');
    $response->assertSee('Rp 1.800.000');
});

test('superadmin can verify commission payout settlement and record audit log', function () {
    $superadmin = helperCreateSuperAdminUser('superadmin');
    $customer = helperCreateSuperAdminUser('user');
    $tenant = helperCreateDummyTenant('Bali Camera Station');
    $booking = helperCreateDummyBooking($customer, $tenant, 5000000, 'Selesai');

    $komisi = KomisiPlatform::create([
        'id_pemesanan' => $booking->id_pemesanan,
        'kode_komisi' => 'KM-99002',
        'total_komisi' => 500000,
        'status_komisi' => 'Menunggu',
    ]);

    $response = $this->actingAs($superadmin)->post("/superadmin/komisi/{$komisi->id_komisi}/verify", [
        'catatan' => 'Dana settlement sebesar Rp 4.500.000 telah ditransfer ke BCA.',
    ]);

    $response->assertRedirect();
    $komisi->refresh();

    expect($komisi->status_komisi)->toBe('Selesai');
    expect($komisi->catatan_verifikasi)->toContain('Dana settlement');

    $auditLog = AuditLog::where('action', 'komisi_payment_verified')
        ->where('auditable_id', $komisi->id_komisi)
        ->first();

    expect($auditLog)->not->toBeNull();
    expect($auditLog->user_id)->toBe($superadmin->id_akun);
    expect($auditLog->details['new_status'])->toBe('Selesai');
});

test('superadmin can export commission report to csv', function () {
    $superadmin = helperCreateSuperAdminUser('superadmin');

    $response = $this->actingAs($superadmin)->get('/superadmin/komisi/export');

    $response->assertOk();
    $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    expect($response->getContent())->toContain('ID Transaksi Komisi,Tanggal & Waktu,Nama Tenant');
});

test('superadmin can approve a tenant from the livewire tenant table', function () {
    $superadmin = helperCreateSuperAdminUser('superadmin');
    $tenant = helperCreateDummyTenant('Livewire Camera House', 'noverified');

    Livewire::actingAs($superadmin)
        ->test(TenantManagement::class)
        ->call('approve', $tenant->id_tenant)
        ->assertHasNoErrors();

    expect($tenant->fresh()->status_mitra)->toBe('verified');
});

test('superadmin can resolve a complaint from the livewire dashboard modal', function () {
    $superadmin = helperCreateSuperAdminUser('superadmin');
    $customer = helperCreateSuperAdminUser('user');
    $tenant = helperCreateDummyTenant('Livewire Cinema Lab');
    $booking = helperCreateDummyBooking($customer, $tenant, 1500000, 'Sedang Disewa');

    $complaint = PengaduanPelanggan::create([
        'nomor_tiket' => 'TKT-LIVEWIRE-001',
        'id_akun' => $customer->id_akun,
        'id_tenant' => $tenant->id_tenant,
        'id_pemesanan' => $booking->id_pemesanan,
        'kategori_pelanggaran' => 'Alat Rusak / Cacat',
        'kronologi_kejadian' => 'Unit kamera tidak menyala saat diterima.',
        'status_kasus' => 'aktif',
    ]);

    Livewire::actingAs($superadmin)
        ->test(Dashboard::class)
        ->call('openResolve', $complaint->id_pengaduan, $complaint->nomor_tiket)
        ->set('justifikasi', 'Mitra mengganti unit baru dan penyewa menyetujui mediasi.')
        ->set('keputusan', 'Klaim Penggantian Disetujui')
        ->call('resolveComplaint')
        ->assertHasNoErrors();

    expect($complaint->fresh()->status_kasus)->toBe('Selesai');
});

test('superadmin can verify commission settlement from the livewire commission modal', function () {
    $superadmin = helperCreateSuperAdminUser('superadmin');
    $customer = helperCreateSuperAdminUser('user');
    $tenant = helperCreateDummyTenant('Livewire Bali Camera');
    $booking = helperCreateDummyBooking($customer, $tenant, 3000000, 'Selesai');

    $komisi = KomisiPlatform::create([
        'id_pemesanan' => $booking->id_pemesanan,
        'kode_komisi' => 'KM-LIVEWIRE-01',
        'total_komisi' => 300000,
        'status_komisi' => 'Menunggu',
    ]);

    Livewire::actingAs($superadmin)
        ->test(App\Livewire\SuperAdmin\KomisiPlatform::class)
        ->call('openVerify', $komisi->id_komisi)
        ->set('catatan', 'Settlement 90% sudah ditransfer ke rekening tenant.')
        ->call('verify')
        ->assertHasNoErrors();

    expect($komisi->fresh()->status_komisi)->toBe('Selesai');
});
