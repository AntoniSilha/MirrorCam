<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class MidtransService
{
    public function isConfigured(): bool
    {
        return filled(config('midtrans.server_key'));
    }

    /**
     * Create a QRIS transaction through Midtrans Core API.
     *
     * @param  array<string, mixed>  $order
     * @return array<string, mixed>
     */
    public function createQrisTransaction(array $order): array
    {
        $this->assertConfigured();

        $payload = [
            'payment_type' => 'qris',
            'transaction_details' => [
                'order_id' => $order['order_id'],
                'gross_amount' => (int) $order['gross_amount'],
            ],
            'qris' => [
                'acquirer' => config('midtrans.qris_acquirer', 'gopay'),
            ],
            'customer_details' => array_filter([
                'first_name' => $order['customer']['first_name'] ?? null,
                'email' => $order['customer']['email'] ?? null,
                'phone' => $order['customer']['phone'] ?? null,
            ]),
            'custom_expiry' => [
                'expiry_duration' => (int) config('midtrans.expiry_minutes', 15),
                'unit' => 'minute',
            ],
        ];

        $response = $this->client()->post('/v2/charge', $payload);

        if ($response->failed()) {
            throw new RuntimeException(
                $response->json('status_message') ?? $response->body() ?? 'Midtrans charge failed.'
            );
        }

        return $response->json();
    }

    /**
     * Fetch a transaction status from Midtrans.
     *
     * @return array<string, mixed>
     */
    public function transactionStatus(string $orderId): array
    {
        $this->assertConfigured();

        $response = $this->client()->get('/v2/'.rawurlencode($orderId).'/status');

        if ($response->failed()) {
            throw new RuntimeException(
                $response->json('status_message') ?? $response->body() ?? 'Midtrans status request failed.'
            );
        }

        return $response->json();
    }

    /**
     * Verify Midtrans HTTP notification signature.
     *
     * @param  array<string, mixed>  $payload
     */
    public function verifySignature(array $payload): bool
    {
        $serverKey = (string) config('midtrans.server_key');

        if ($serverKey === '') {
            return false;
        }

        $expected = hash('sha512',
            (string) ($payload['order_id'] ?? '').
            (string) ($payload['status_code'] ?? '').
            (string) ($payload['gross_amount'] ?? '').
            $serverKey
        );

        return hash_equals($expected, (string) ($payload['signature_key'] ?? ''));
    }

    /**
     * Map Midtrans transaction status to an internal status.
     */
    public function mapTransactionStatus(string $transactionStatus, ?string $fraudStatus = null): string
    {
        $status = strtolower($transactionStatus);

        if ($status === 'capture' && strtolower((string) $fraudStatus) === 'challenge') {
            return 'pending';
        }

        if (in_array($status, ['settlement', 'capture'], true)) {
            return 'paid';
        }

        if (in_array($status, ['deny', 'cancel', 'expire', 'failure'], true)) {
            return 'failed';
        }

        return 'pending';
    }

    /**
     * Extract the QR image URL from a Midtrans QRIS response.
     *
     * @param  array<string, mixed>  $response
     */
    public function qrUrlFrom(array $response): ?string
    {
        foreach ($response['actions'] ?? [] as $action) {
            if (($action['name'] ?? null) === 'generate-qr-code') {
                return $action['url'] ?? null;
            }
        }

        return null;
    }

    private function client(): PendingRequest
    {
        return Http::withBasicAuth((string) config('midtrans.server_key'), '')
            ->acceptJson()
            ->asJson()
            ->timeout(30)
            ->baseUrl((string) config('midtrans.base_url'));
    }

    private function assertConfigured(): void
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('Midtrans belum dikonfigurasi. Isi MIDTRANS_SERVER_KEY pada .env.');
        }
    }
}
