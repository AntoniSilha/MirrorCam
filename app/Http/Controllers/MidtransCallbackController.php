<?php

namespace App\Http\Controllers;

use App\Services\MidtransService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class MidtransCallbackController extends Controller
{
    public function __invoke(Request $request, MidtransService $midtrans): JsonResponse
    {
        $payload = $request->all();

        if (! $midtrans->verifySignature($payload)) {
            Log::warning('Midtrans callback signature mismatch.', ['order_id' => $payload['order_id'] ?? null]);

            return response()->json(['message' => 'Invalid signature.'], 403);
        }

        $orderId = (string) ($payload['order_id'] ?? '');

        if ($orderId === '') {
            return response()->json(['message' => 'order_id is required.'], 422);
        }

        Cache::put('midtrans:status:'.$orderId, [
            'order_id' => $orderId,
            'transaction_id' => $payload['transaction_id'] ?? null,
            'transaction_status' => $payload['transaction_status'] ?? null,
            'fraud_status' => $payload['fraud_status'] ?? null,
            'payment_type' => $payload['payment_type'] ?? null,
            'status_code' => $payload['status_code'] ?? null,
            'gross_amount' => $payload['gross_amount'] ?? null,
            'settlement_time' => $payload['settlement_time'] ?? null,
        ], now()->addDay());

        Log::info('Midtrans callback received.', [
            'order_id' => $orderId,
            'transaction_status' => $payload['transaction_status'] ?? null,
        ]);

        return response()->json(['message' => 'OK']);
    }
}
