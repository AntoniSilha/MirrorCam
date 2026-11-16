<?php

use App\Models\AccountGoogle;
use App\Models\User;
use App\Models\VerifikasiKtp;
use App\Services\MidtransService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

function paymentTestCreateUser(): User
{
    $googleId = 'google-payment-'.uniqid();

    AccountGoogle::create([
        'id_user_google' => $googleId,
        'email' => $googleId.'@example.com',
    ]);

    $user = User::create([
        'id_user_google' => $googleId,
        'role' => 'user',
        'nama_lengkap_ktp' => 'Payment Test User',
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

function paymentTestCart(int $harga = 200000): array
{
    return [
        'item-1' => [
            'item_key' => 'item-1',
            'id_produk' => 1,
            'id_tenant' => 1,
            'nama_toko' => 'Toko Test',
            'nama_produk' => 'Kamera Test',
            'foto' => 'https://example.com/kamera.jpg',
            'warna' => 'black',
            'durasi_label' => 'Durasi sewa: 12 Jam',
            'harga' => $harga,
        ],
    ];
}

it('creates a pending QRIS payment order from checkout', function () {
    $user = paymentTestCreateUser();

    $response = $this->actingAs($user)->withSession([
        'mirrorcam_cart' => paymentTestCart(),
        'mirrorcam_trx_code' => 'MC-TEST-0001',
    ])->post(route('keranjang.checkout'), [
        'tipe_bayar' => 'dp_50',
    ]);

    $response->assertRedirect();
    expect($response->headers->get('Location'))->toContain('/pembayaran/');

    $payments = session('mirrorcam_payments');
    $order = array_values($payments)[0];

    expect($payments)->toHaveCount(1)
        ->and($order['total_booking'])->toBe(200000)
        ->and($order['gross_amount'])->toBe(100000)
        ->and($order['tipe_bayar'])->toBe('dp_50')
        ->and($order['status'])->toBe('pending')
        ->and(session('mirrorcam_cart'))->toHaveCount(1);
});

it('shows the QRIS popup with the payment amount in demo mode', function () {
    $user = paymentTestCreateUser();

    $this->actingAs($user)->withSession([
        'mirrorcam_cart' => paymentTestCart(),
    ])->post(route('keranjang.checkout'), [
        'tipe_bayar' => 'lunas',
    ]);

    $order = array_values(session('mirrorcam_payments'))[0];

    $this->get(route('pembayaran.show', $order['token']))
        ->assertOk()
        ->assertSee('Pembayaran QRIS')
        ->assertSee('Rp 200.000')
        ->assertSee('Mode demo')
        ->assertSee('Nominal otomatis terisi saat QRIS dipindai');
});

it('marks an order paid when the payment notification status is settled', function () {
    $user = paymentTestCreateUser();

    $this->actingAs($user)->withSession([
        'mirrorcam_cart' => paymentTestCart(),
    ])->post(route('keranjang.checkout'), [
        'tipe_bayar' => 'lunas',
    ]);

    $order = array_values(session('mirrorcam_payments'))[0];

    Cache::put('midtrans:status:'.$order['order_id'], [
        'order_id' => $order['order_id'],
        'transaction_status' => 'settlement',
        'fraud_status' => 'accept',
        'status_code' => '200',
        'gross_amount' => (string) $order['gross_amount'].'.00',
    ], now()->addDay());

    $this->getJson(route('pembayaran.status', $order['token']))
        ->assertOk()
        ->assertJson([
            'paid' => true,
            'status' => 'paid',
        ]);

    $updated = array_values(session('mirrorcam_payments'))[0];

    expect($updated['status'])->toBe('paid')
        ->and(session('mirrorcam_cart'))->toBeNull();

    $this->get(route('pembayaran.sukses', $order['token']))
        ->assertOk()
        ->assertSee('Pembayaran Berhasil')
        ->assertSee('Rp 200.000');
});

it('rejects invalid Midtrans callback signatures and stores valid ones', function () {
    config()->set('midtrans.server_key', 'test-server-key');

    $payload = [
        'order_id' => 'MC-CALLBACK-1',
        'status_code' => '200',
        'gross_amount' => '100000.00',
        'transaction_status' => 'settlement',
        'fraud_status' => 'accept',
        'signature_key' => hash('sha512', 'MC-CALLBACK-1'.'200'.'100000.00'.'test-server-key'),
    ];

    $this->postJson(route('midtrans.callback'), $payload)->assertOk();

    expect(Cache::get('midtrans:status:MC-CALLBACK-1'))->not->toBeNull();

    $invalid = array_merge($payload, ['signature_key' => 'invalid']);

    $this->postJson(route('midtrans.callback'), $invalid)->assertForbidden();
});

it('creates a QRIS charge through the Midtrans service when configured', function () {
    config()->set('midtrans.server_key', 'test-server-key');
    config()->set('midtrans.client_key', 'test-client-key');

    Http::fake([
        'https://api.sandbox.midtrans.com/v2/charge' => Http::response([
            'status_code' => '201',
            'transaction_status' => 'pending',
            'order_id' => 'MC-SERVICE-1',
            'gross_amount' => '100000.00',
            'qr_string' => '00020101021226610014ID.CO.QRIS.WWW',
            'actions' => [
                ['name' => 'generate-qr-code', 'method' => 'GET', 'url' => 'https://example.com/qr.png'],
            ],
        ], 201),
    ]);

    $response = app(MidtransService::class)->createQrisTransaction([
        'order_id' => 'MC-SERVICE-1',
        'gross_amount' => 100000,
        'customer' => ['first_name' => 'Payment Test', 'email' => 'test@example.com'],
    ]);

    expect($response['qr_string'])->toBe('00020101021226610014ID.CO.QRIS.WWW')
        ->and($response['actions'][0]['url'])->toBe('https://example.com/qr.png');
});
