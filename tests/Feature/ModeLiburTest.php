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
use Livewire\Livewire;

uses(RefreshDatabase::class);

function helperModeLiburUser(string $role = 'mitra'): User
{
    $googleId = 'google-'.uniqid();

    AccountGoogle::create([
        'id_user_google' => $googleId,
        'email' => $role.'-'.uniqid().'@mirrorcam.test',
    ]);

    $user = User::create([
        'id_user_google' => $googleId,
        'role' => $role,
        'nama_lengkap_ktp' => ucfirst($role).' MirrorCam',
        'nik_ktp' => (string) random_int(1000000000000000, 9999999999999999),
        'no_whatsapp' => '081234567890',
        'foto_ktp' => 'ktp/'.uniqid().'.jpg',
    ]);

    VerifikasiKtp::create([
        'id_akun' => $user->id_akun,
        'nik_ktp' => $user->nik_ktp,
        'url_foto_ktp' => $user->foto_ktp,
        'status_verifikasi' => 'Terverifikasi',
    ]);

    return $user;
}

function helperModeLiburTenant(User $owner, bool $modeLibur = false): Tenant
{
    return Tenant::create([
        'google_id_token' => $owner->id_user_google,
        'id_akun_pemilik' => $owner->id_akun,
        'nama_toko' => 'Toko Mode Libur '.uniqid(),
        'bio_deskripsi' => 'Tenant untuk pengujian mode libur.',
        'whatsapp_resmi' => '081298765432',
        'nama_penanggungjawab' => 'PIC Tenant',
        'jabatan_penanggungjawab' => 'Owner',
        'email_resmi' => 'tenant-'.uniqid().'@mirrorcam.test',
        'alamat_toko' => 'Jl. Pengujian No. 1, Jakarta',
        'status_mitra' => 'verified',
        'mode_libur' => $modeLibur,
    ]);
}

function helperModeLiburProduct(Tenant $tenant): Produk
{
    $category = KategoriProduk::firstOrCreate(
        ['id_kategori' => 1],
        ['nama_kategori' => 'Kamera Cinema & Mirrorless']
    );

    $stockColumn = Schema::hasColumn('produks', 'total_stok_unit')
        ? 'total_stok_unit'
        : 'total_stock_unit';

    $product = Produk::create([
        'id_tenant' => $tenant->id_tenant,
        'id_kategori' => $category->id_kategori,
        'nama_produk' => 'Kamera Uji Mode Libur',
        'deskripsi_lengkap' => 'Produk untuk pengujian mode libur.',
        $stockColumn => 3,
    ]);

    DurasiProduk::create([
        'id_produk' => $product->id_produk,
        'durasi' => '1_hari',
        'nama_durasi' => '1 Hari',
        'harga' => 250000,
    ]);

    return $product;
}

test('mitra can toggle mode libur and it persists to the tenant record', function () {
    $owner = helperModeLiburUser('mitra');
    $tenant = helperModeLiburTenant($owner, false);

    Livewire::actingAs($owner)
        ->test('pages::mitra.dashboard')
        ->assertSet('maintenanceMode', false)
        ->call('toggleMaintenance')
        ->assertSet('maintenanceMode', true)
        ->assertSee('Mode libur diaktifkan');

    expect($tenant->fresh()->mode_libur)->toBeTrue();

    Livewire::actingAs($owner)
        ->test('pages::mitra.dashboard')
        ->assertSet('maintenanceMode', true)
        ->call('toggleMaintenance')
        ->assertSet('maintenanceMode', false);

    expect($tenant->fresh()->mode_libur)->toBeFalse();
});

test('save preferences persists the mode libur flag', function () {
    $owner = helperModeLiburUser('mitra');
    $tenant = helperModeLiburTenant($owner, false);

    Livewire::actingAs($owner)
        ->test('pages::mitra.dashboard')
        ->set('maintenanceMode', true)
        ->call('savePreferences')
        ->assertHasNoErrors();

    expect($tenant->fresh()->mode_libur)->toBeTrue();
});

test('mitra dashboard page loads over http after the split', function () {
    $owner = helperModeLiburUser('mitra');
    helperModeLiburTenant($owner, false);

    $this->actingAs($owner)
        ->get(route('mitra.dashboard'))
        ->assertOk()
        ->assertSee('Mode Libur Toko')
        ->assertSee('Ringkasan');
});

test('mitra dashboard renders every page section after the split', function () {
    $owner = helperModeLiburUser('mitra');
    $tenant = helperModeLiburTenant($owner, false);
    helperModeLiburProduct($tenant);

    $component = Livewire::actingAs($owner)->test('pages::mitra.dashboard');

    foreach (['ringkasan', 'produk', 'pesanan', 'profil', 'keamanan', 'komisi'] as $section) {
        $component->call('showSection', $section)->assertOk();
    }

    $component->call('openProductModal')->assertOk();
});

test('customers cannot add products from a tenant in mode libur to the cart', function () {
    $owner = helperModeLiburUser('mitra');
    $customer = helperModeLiburUser('user');
    $tenant = helperModeLiburTenant($owner, true);
    $product = helperModeLiburProduct($tenant);

    $this->actingAs($customer)
        ->from(route('tenant.show', $tenant->slug))
        ->post(route('keranjang.add'), [
            'id_produk' => $product->id_produk,
            'durasi_kode' => '1_hari',
            'durasi_label' => '1 Hari',
            'warna' => 'Black',
        ])
        ->assertRedirect(route('tenant.show', $tenant->slug))
        ->assertSessionHasErrors('id_produk');

    expect(session()->get('mirrorcam_cart', []))->toBeEmpty();
});

test('checkout is blocked when the cart contains a tenant in mode libur', function () {
    $owner = helperModeLiburUser('mitra');
    $customer = helperModeLiburUser('user');
    $tenant = helperModeLiburTenant($owner, true);
    $product = helperModeLiburProduct($tenant);

    $this->actingAs($customer)
        ->withSession([
            'mirrorcam_cart' => [
                $product->id_produk.'_1_hari_default' => [
                    'item_key' => $product->id_produk.'_1_hari_default',
                    'id_produk' => $product->id_produk,
                    'id_tenant' => $tenant->id_tenant,
                    'nama_toko' => $tenant->nama_toko,
                    'nama_produk' => $product->nama_produk,
                    'foto' => null,
                    'warna' => 'Black',
                    'durasi_kode' => '1_hari',
                    'durasi_label' => '1 Hari',
                    'harga' => 250000,
                ],
            ],
        ])
        ->post(route('keranjang.checkout'), ['tipe_bayar' => 'lunas'])
        ->assertRedirect(route('keranjang.index'))
        ->assertSessionHasErrors('cart');
});
