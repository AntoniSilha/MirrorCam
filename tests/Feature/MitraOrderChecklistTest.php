<?php

use App\Models\AccountGoogle;
use App\Models\AuditLog;
use App\Models\DetailPemesananTenant;
use App\Models\KategoriProduk;
use App\Models\Produk;
use App\Models\Tenant;
use App\Models\User;
use App\Models\VerifikasiKtp;
use App\Services\Mitra\OrderChecklistService;
use Carbon\Carbon;
use DomainException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function createMitraEnvironment(string $suffix = '1'): array
{
    $googleMitra = AccountGoogle::create([
        'id_user_google' => 'google_mitra_'.$suffix.'_'.uniqid(),
        'email' => "mitra{$suffix}_".uniqid().'@example.com',
    ]);

    $userMitra = User::create([
        'id_user_google' => $googleMitra->id_user_google,
        'role' => 'mitra',
        'nama_lengkap_ktp' => 'Owner Mitra '.$suffix,
        'no_whatsapp' => '08123456789',
    ]);

    $tenant = Tenant::create([
        'google_id_token' => $googleMitra->id_user_google,
        'id_akun_pemilik' => $userMitra->id_akun,
        'nama_toko' => 'Tenant Kamera '.$suffix,
        'bio_deskripsi' => 'Rental kamera resmi '.$suffix,
        'whatsapp_resmi' => '08123456789'.$suffix,
        'nama_penanggungjawab' => 'Penanggung Jawab '.$suffix,
        'jabatan_penanggungjawab' => 'Owner',
        'email_resmi' => "tenant{$suffix}_".uniqid().'@example.com',
        'alamat_toko' => 'Jl. Kamera No. '.$suffix.', Jakarta',
        'status_mitra' => 'aktif',
    ]);

    $kategori = KategoriProduk::first() ?? KategoriProduk::create([
        'nama_kategori' => 'Kamera DSLR',
    ]);

    $produk = Produk::create([
        'id_tenant' => $tenant->id_tenant,
        'id_kategori' => $kategori->id_kategori,
        'nama_produk' => 'Sony A7 IV - Toko '.$suffix,
        'deskripsi_lengkap' => 'Kamera mirrorless full-frame profesional',
        'harga_sewa' => 200000,
        'total_stock_unit' => 3,
    ]);

    $googleCustomer = AccountGoogle::create([
        'id_user_google' => 'google_cust_'.$suffix.'_'.uniqid(),
        'email' => "cust{$suffix}_".uniqid().'@example.com',
    ]);

    $userCustomer = User::create([
        'id_user_google' => $googleCustomer->id_user_google,
        'role' => 'user',
        'nama_lengkap_ktp' => 'Pelanggan Setia '.$suffix,
        'no_whatsapp' => '08987654321',
    ]);

    $ktp = VerifikasiKtp::create([
        'id_akun' => $userCustomer->id_akun,
        'nik_ktp' => '320123456789000'.$suffix,
        'url_foto_ktp' => 'https://example.com/ktp_'.$suffix.'.jpg',
        'status_verifikasi' => 'terverifikasi',
        'waktu_verifikasi' => Carbon::now(),
    ]);

    $booking = DetailPemesananTenant::create([
        'sub_order_eksternal' => 'MC-TEST-'.$suffix.'-'.rand(1000, 9999),
        'id_tenant' => $tenant->id_tenant,
        'id_produk' => $produk->id_produk,
        'id_ktp' => $ktp->id_ktp,
        'url_foto_ktp' => $ktp->url_foto_ktp,
        'waktu_ambil_booking' => Carbon::now(),
        'waktu_kembali_booking' => Carbon::now()->addDays(2),
        'durasi_sewa_hari' => 2,
        'harga_sewa_booking' => 200000,
        'total_harga' => 400000,
        'status_harga' => 'dp',
        'nominal_dp' => 200000,
        'sisa_tagihan' => 200000,
        'is_payment_verified' => false,
        'status_sewa' => 'menunggu_serah_terima',
    ]);

    return [
        'mitra' => $userMitra,
        'tenant' => $tenant,
        'produk' => $produk,
        'customer' => $userCustomer,
        'ktp' => $ktp,
        'booking' => $booking,
    ];
}

test('multi-tenant isolation prevents tenant operator from modifying another tenant order', function () {
    $envA = createMitraEnvironment('A');
    $envB = createMitraEnvironment('B');

    $service = app(OrderChecklistService::class);

    // Operator Mitra B should NOT be able to verify payment of Tenant A
    expect(fn () => $service->verifyPayment($envB['mitra'], $envA['booking']->id_pemesanan))
        ->toThrow(ModelNotFoundException::class);

    // Operator Mitra B should NOT be able to handover Tenant A unit
    expect(fn () => $service->confirmHandover($envB['mitra'], $envA['booking']->id_pemesanan, [
        'identitas_terverifikasi' => true,
        'kondisi_fisik_awal_ok' => true,
    ]))->toThrow(ModelNotFoundException::class);

    // Operator Mitra B should NOT be able to return Tenant A unit
    expect(fn () => $service->confirmReturn($envB['mitra'], $envA['booking']->id_pemesanan, [
        'kondisi_fisik_akhir_ok' => true,
        'kondisi_kerusakan' => 'baik',
    ]))->toThrow(ModelNotFoundException::class);
});

test('tahap 1: ceklist pembayaran selesai verifies dp and updates status to lunas', function () {
    $env = createMitraEnvironment('T1');
    $service = app(OrderChecklistService::class);

    expect($env['booking']->isPaymentComplete())->toBeFalse();
    expect($env['booking']->status_harga)->toBe('dp');

    $updated = $service->verifyPayment($env['mitra'], $env['booking']->id_pemesanan, [
        'catatan' => 'Lunas via cash saat pengambilan',
    ]);

    expect($updated->is_payment_verified)->toBeTrue()
        ->and($updated->status_harga)->toBe('lunas')
        ->and((float) $updated->sisa_tagihan)->toBe(0.0)
        ->and($updated->payment_verified_at)->not->toBeNull()
        ->and($updated->isPaymentComplete())->toBeTrue();

    // Verify AuditLog is written
    $log = AuditLog::where('auditable_id', $env['booking']->id_pemesanan)
        ->where('action', 'CHECKLIST_PEMBAYARAN_SELESAI')
        ->first();

    expect($log)->not->toBeNull()
        ->and($log->user_id)->toBe($env['mitra']->id_akun)
        ->and($log->details['new_status_harga'])->toBe('lunas');
});

test('tahap 2 guard: blocks handover if payment is not settled', function () {
    $env = createMitraEnvironment('T2G');
    $service = app(OrderChecklistService::class);

    // Booking still has status_harga = 'dp' and is_payment_verified = false
    expect(fn () => $service->confirmHandover($env['mitra'], $env['booking']->id_pemesanan, [
        'identitas_terverifikasi' => true,
        'kondisi_fisik_awal_ok' => true,
    ]))->toThrow(DomainException::class, 'Tahap 2 tidak dapat diselesaikan karena status pembayaran belum lunas.');
});

test('tahap 2: confirm handover changes status to sedang disewa and sets return deadline', function () {
    $env = createMitraEnvironment('T2');
    $service = app(OrderChecklistService::class);

    // Complete Tahap 1 first
    $service->verifyPayment($env['mitra'], $env['booking']->id_pemesanan);

    // Perform Tahap 2
    $updated = $service->confirmHandover($env['mitra'], $env['booking']->id_pemesanan, [
        'identitas_terverifikasi' => true,
        'kondisi_fisik_awal_ok' => true,
        'foto_kondisi_awal' => 'https://example.com/handover.jpg',
    ]);

    expect($updated->status_sewa)->toBe('sedang disewa')
        ->and($updated->isInUse())->toBeTrue()
        ->and($updated->actual_handover_at)->not->toBeNull()
        ->and($updated->return_deadline)->not->toBeNull()
        ->and($updated->foto_kondisi_awal)->toBe('https://example.com/handover.jpg');

    // Deadline should be handover + 2 days
    $expectedDeadline = Carbon::parse($updated->actual_handover_at)->addDays(2)->format('Y-m-d H:i');
    expect(Carbon::parse($updated->return_deadline)->format('Y-m-d H:i'))->toBe($expectedDeadline);

    // Verify AuditLog
    $log = AuditLog::where('auditable_id', $env['booking']->id_pemesanan)
        ->where('action', 'CHECKLIST_BARANG_DIGUNAKAN')
        ->first();

    expect($log)->not->toBeNull()
        ->and($log->user_id)->toBe($env['mitra']->id_akun)
        ->and($log->details['new_status_sewa'])->toBe('sedang disewa');
});

test('tahap 3 guard: blocks return confirmation if order is not in use', function () {
    $env = createMitraEnvironment('T3G');
    $service = app(OrderChecklistService::class);

    // Booking is still in status 'menunggu_serah_terima'
    expect(fn () => $service->confirmReturn($env['mitra'], $env['booking']->id_pemesanan, [
        'kondisi_fisik_akhir_ok' => true,
        'kondisi_kerusakan' => 'baik',
    ]))->toThrow(DomainException::class, 'Tahap 3 hanya dapat dilakukan apabila pesanan sudah berstatus sedang disewa (in_use).');
});

test('tahap 3: confirm return in good condition closes order as selesai', function () {
    $env = createMitraEnvironment('T3A');
    $service = app(OrderChecklistService::class);

    $service->verifyPayment($env['mitra'], $env['booking']->id_pemesanan);
    $service->confirmHandover($env['mitra'], $env['booking']->id_pemesanan, [
        'identitas_terverifikasi' => true,
        'kondisi_fisik_awal_ok' => true,
    ]);

    $updated = $service->confirmReturn($env['mitra'], $env['booking']->id_pemesanan, [
        'kondisi_fisik_akhir_ok' => true,
        'kondisi_kerusakan' => 'baik',
        'foto_kondisi_akhir' => 'https://example.com/return_ok.jpg',
    ]);

    expect($updated->status_sewa)->toBe('selesai')
        ->and($updated->isCompleted())->toBeTrue()
        ->and($updated->actual_returned_at)->not->toBeNull()
        ->and((float) $updated->denda_keterlambatan)->toBe(0.0)
        ->and($updated->kondisi_barang_kembali)->toBe('baik');

    // Verify AuditLog
    $log = AuditLog::where('auditable_id', $env['booking']->id_pemesanan)
        ->where('action', 'CHECKLIST_PENANDA_PEMINJAMAN_SELESAI')
        ->first();

    expect($log)->not->toBeNull()
        ->and($log->details['new_status_sewa'])->toBe('selesai');
});

test('tahap 3: automatically calculates late fine without manual override when overdue', function () {
    $env = createMitraEnvironment('T3B');
    $service = app(OrderChecklistService::class);

    $service->verifyPayment($env['mitra'], $env['booking']->id_pemesanan);
    $service->confirmHandover($env['mitra'], $env['booking']->id_pemesanan, [
        'identitas_terverifikasi' => true,
        'kondisi_fisik_awal_ok' => true,
    ]);

    // Simulate 3 days overdue: return_deadline was 3 days ago
    $env['booking']->return_deadline = Carbon::now()->subDays(3);
    $env['booking']->save();

    $updated = $service->confirmReturn($env['mitra'], $env['booking']->id_pemesanan, [
        'kondisi_fisik_akhir_ok' => true,
        'kondisi_kerusakan' => 'baik',
    ]);

    // Rate is 200000/day * 3 days late = 600000
    expect((float) $updated->denda_keterlambatan)->toBeGreaterThanOrEqual(600000.0);
});

test('tahap 3: damage condition marks order as disputed with claim description', function () {
    $env = createMitraEnvironment('T3C');
    $service = app(OrderChecklistService::class);

    $service->verifyPayment($env['mitra'], $env['booking']->id_pemesanan);
    $service->confirmHandover($env['mitra'], $env['booking']->id_pemesanan, [
        'identitas_terverifikasi' => true,
        'kondisi_fisik_awal_ok' => true,
    ]);

    $updated = $service->confirmReturn($env['mitra'], $env['booking']->id_pemesanan, [
        'kondisi_fisik_akhir_ok' => true,
        'kondisi_kerusakan' => 'rusak',
        'deskripsi_kerusakan' => 'Lensa retak di bagian optik depan',
        'estimasi_biaya_kerusakan' => 750000,
        'foto_kondisi_akhir' => 'https://example.com/broken_lens.jpg',
    ]);

    expect($updated->status_sewa)->toBe('disputed')
        ->and($updated->isDisputed())->toBeTrue()
        ->and($updated->kondisi_barang_kembali)->toBe('rusak')
        ->and($updated->deskripsi_kerusakan)->toBe('Lensa retak di bagian optik depan')
        ->and((float) $updated->estimasi_biaya_kerusakan)->toBe(750000.0);

    // Verify AuditLog
    $log = AuditLog::where('auditable_id', $env['booking']->id_pemesanan)
        ->where('action', 'CHECKLIST_PENANDA_PEMINJAMAN_SELESAI')
        ->first();

    expect($log)->not->toBeNull()
        ->and($log->details['new_status_sewa'])->toBe('disputed')
        ->and((float) $log->details['estimasi_biaya_kerusakan'])->toBe(750000.0);
});

test('livewire mitra dashboard renders checklist button and handles modal checklist actions', function () {
    $env = createMitraEnvironment('LW');

    Livewire::actingAs($env['mitra'])
        ->test('pages::mitra.dashboard')
        ->set('section', 'pesanan')
        ->assertSee('Kelola Pesanan Sewa')
        ->assertSee($env['booking']->sub_order_eksternal)
        ->assertSee('Checklist Alur')
        ->call('openChecklistModal', $env['booking']->id_pemesanan)
        ->assertSet('showChecklistModal', true)
        ->assertSee('Tahap 1: Ceklist Pembayaran Selesai')
        ->call('verifyPayment', $env['booking']->id_pemesanan)
        ->assertSee('Tahap 1 Berhasil')
        ->set('checklistIdentitasOk', true)
        ->set('checklistKondisiAwalOk', true)
        ->call('confirmHandover', $env['booking']->id_pemesanan)
        ->assertSee('Tahap 2 Berhasil')
        ->set('checklistKondisiAkhirOk', true)
        ->set('kondisiKerusakan', 'baik')
        ->call('confirmReturn', $env['booking']->id_pemesanan)
        ->assertSee('Tahap 3 Berhasil');
});
