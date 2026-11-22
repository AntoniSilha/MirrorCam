<?php

namespace App\Portals\Customer\Controllers;

use App\Http\Controllers\Controller;
use App\Services\MidtransService;
use App\Services\PaymentOrderService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class PaymentController extends Controller
{
    public function __construct(
        private readonly PaymentOrderService $orders,
        private readonly MidtransService $midtrans
    ) {}

    public function show(string $token): View|RedirectResponse
    {
        $order = $this->orders->findForUser($token, Auth::user());

        abort_unless($order, 404);

        if (($order['status'] ?? null) === 'paid') {
            return redirect()->route('pembayaran.sukses', $token);
        }

        if (empty($order['qr_data_uri']) && empty($order['qr_url']) && empty($order['error'])) {
            $order = $this->generateQr($order);
        }

        return view('customer.pembayaran.show', [
            'order' => $order,
            'midtransConfigured' => $this->midtrans->isConfigured(),
        ]);
    }

    public function generate(string $token): RedirectResponse
    {
        $order = $this->orders->findForUser($token, Auth::user());

        abort_unless($order, 404);

        $this->generateQr($order);

        return redirect()->route('pembayaran.show', $token);
    }

    public function status(string $token): JsonResponse
    {
        $order = $this->orders->findForUser($token, Auth::user());

        abort_unless($order, 404);

        $order = $this->refreshStatus($order);

        if (($order['status'] ?? null) === 'paid') {
            session()->forget(['mirrorcam_cart', 'mirrorcam_trx_code']);

            return response()->json([
                'paid' => true,
                'status' => 'paid',
                'redirect' => route('pembayaran.sukses', $token),
                'message' => 'Pembayaran berhasil diterima.',
            ]);
        }

        return response()->json([
            'paid' => false,
            'status' => $order['status'] ?? 'pending',
            'message' => ($order['status'] ?? null) === 'failed'
                ? 'Pembayaran gagal atau kedaluwarsa. Silakan buat QRIS baru.'
                : 'Menunggu pembayaran.',
        ]);
    }

    public function success(string $token): View|RedirectResponse
    {
        $order = $this->orders->findForUser($token, Auth::user());

        abort_unless($order, 404);

        if (($order['status'] ?? null) !== 'paid') {
            $order = $this->refreshStatus($order);
        }

        if (($order['status'] ?? null) !== 'paid') {
            return redirect()->route('pembayaran.show', $token)
                ->with('error', 'Pembayaran belum diterima. Silakan selesaikan pembayaran QRIS.');
        }

        session()->forget(['mirrorcam_cart', 'mirrorcam_trx_code']);

        return view('customer.pembayaran.sukses', compact('order'));
    }

    /**
     * @param  array<string, mixed>  $order
     * @return array<string, mixed>
     */
    private function generateQr(array $order): array
    {
        if (! $this->midtrans->isConfigured()) {
            $demoPayload = implode('|', [
                'MIRRORCAM-DEMO-QRIS',
                'ORDER:'.$order['order_id'],
                'AMOUNT:'.$order['gross_amount'],
                'TYPE:'.$order['tipe_bayar'],
            ]);

            return $this->orders->update($order['token'], [
                'mode' => 'demo',
                'status' => 'pending',
                'qr_string' => $demoPayload,
                'qr_data_uri' => $this->orders->qrDataUri($demoPayload),
                'error' => null,
            ]) ?? $order;
        }

        try {
            $orderId = ($order['mode'] ?? null) === 'midtrans' || ! empty($order['midtrans']) || ! empty($order['error'])
                ? $this->orders->newOrderId()
                : $order['order_id'];

            $charge = $this->midtrans->createQrisTransaction(array_merge($order, [
                'order_id' => $orderId,
            ]));
            $qrString = $charge['qr_string'] ?? null;

            return $this->orders->update($order['token'], [
                'order_id' => $orderId,
                'mode' => 'midtrans',
                'status' => 'pending',
                'qr_string' => $qrString,
                'qr_url' => $this->midtrans->qrUrlFrom($charge),
                'qr_data_uri' => $qrString ? $this->orders->qrDataUri($qrString) : null,
                'midtrans' => $charge,
                'expires_at' => $charge['expiry_time'] ?? $order['expires_at'],
                'error' => null,
            ]) ?? $order;
        } catch (\Throwable $exception) {
            report($exception);

            return $this->orders->update($order['token'], [
                'mode' => 'midtrans',
                'error' => $exception->getMessage(),
            ]) ?? $order;
        }
    }

    /**
     * @param  array<string, mixed>  $order
     * @return array<string, mixed>
     */
    private function refreshStatus(array $order): array
    {
        if (($order['status'] ?? null) === 'paid') {
            return $order;
        }

        $notification = Cache::get('midtrans:status:'.$order['order_id']);
        $remote = is_array($notification) ? $notification : null;

        if (! $remote && ($order['mode'] ?? null) === 'midtrans' && $this->midtrans->isConfigured()) {
            try {
                $remote = $this->midtrans->transactionStatus($order['order_id']);
            } catch (\Throwable $exception) {
                report($exception);
            }
        }

        if (! is_array($remote)) {
            return $order;
        }

        $mapped = $this->midtrans->mapTransactionStatus(
            (string) ($remote['transaction_status'] ?? ''),
            $remote['fraud_status'] ?? null
        );

        if ($mapped === 'paid') {
            return $this->orders->markPaid($order['token']) ?? $order;
        }

        if ($mapped === 'failed') {
            return $this->orders->update($order['token'], ['status' => 'failed']) ?? $order;
        }

        return $order;
    }
}
