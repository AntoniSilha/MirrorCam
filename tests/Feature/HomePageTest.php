<?php

use App\Models\AccountGoogle;
use App\Models\KategoriProduk;
use App\Models\Produk;
use App\Models\Tenant;
use App\Models\User;

test('home page does not render mockup data when database is empty', function () {
    $response = $this->get(route('home'));

    $response->assertOk();
    $response->assertDontSee('Indonesia Battery');
    $response->assertDontSee('Studio Proyek ID');
    $response->assertDontSee('Koo Gallery');
    $response->assertDontSee('Sony FX30 Cinema Line');
    $response->assertSee('Belum ada mitra tenant resmi terdaftar');
    $response->assertSee('Belum ada peralatan kamera yang tersedia');
});

test('home page dynamically renders manually inputted tenant and product', function () {
    $account = AccountGoogle::create([
        'id_user_google' => 'test-manual-google-id',
        'email' => 'manual.tenant@mirrorcam.com',
    ]);

    $user = User::create([
        'id_user_google' => $account->id_user_google,
        'role' => 'tenant',
        'nama_lengkap_ktp' => 'Manual User Tenant',
        'nik_ktp' => '3510000000000001',
        'no_whatsapp' => '081234567899',
        'foto_ktp' => 'ktp/manual.jpg',
    ]);

    $tenant = Tenant::create([
        'google_id_token' => $account->id_user_google,
        'id_akun_pemilik' => $user->id_akun,
        'nama_toko' => 'Kamera Sentral Manual',
        'bio_deskripsi' => 'Toko sewa kamera manual input resmi.',
        'whatsapp_resmi' => '628999888777',
        'nama_penanggungjawab' => 'Budi Manual',
        'jabatan_penanggungjawab' => 'Owner',
        'email_resmi' => 'kamerasentral@example.com',
        'alamat_toko' => 'Jl. Pahlawan No. 10, Malang',
        'status_mitra' => 'official',
    ]);

    $category = KategoriProduk::firstOrCreate(
        ['id_kategori' => 1],
        ['nama_kategori' => 'Kamera Cinema & Mirrorless']
    );

    $product = Produk::create([
        'id_tenant' => $tenant->id_tenant,
        'id_kategori' => $category->id_kategori,
        'nama_produk' => 'Fujifilm X-T5 Manual Edition',
        'deskripsi_lengkap' => 'Kamera mirrorless resolusi tinggi untuk fotografi & sinema.',
        'harga_sewa' => 300000,
        'total_stok_unit' => 3,
    ]);

    $response = $this->get(route('home'));

    $response->assertOk();
    $response->assertSee('Kamera Sentral Manual');
    $response->assertSee('Fujifilm X-T5 Manual Edition');
    $response->assertSee('Rp 300.000 / hari');
    $response->assertDontSee('Belum ada mitra tenant resmi terdaftar');
    $response->assertDontSee('Belum ada peralatan kamera yang tersedia');

    // Test search filter
    $searchResponse = $this->get(route('home', ['keyword' => 'Fujifilm']));
    $searchResponse->assertOk();
    $searchResponse->assertSee('Fujifilm X-T5 Manual Edition');

    // Clean up
    $product->delete();
    $tenant->delete();
    $user->delete();
    $account->delete();
});
