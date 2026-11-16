<?php

$isProduction = (bool) env('MIDTRANS_IS_PRODUCTION', false);

return [
    'merchant_id' => env('MIDTRANS_MERCHANT_ID'),

    'client_key' => env('MIDTRANS_CLIENT_KEY'),

    'server_key' => env('MIDTRANS_SERVER_KEY'),

    'is_production' => $isProduction,

    'is_sanitized' => (bool) env('MIDTRANS_IS_SANITIZED', true),

    'is_3ds' => (bool) env('MIDTRANS_IS_3DS', true),

    'base_url' => $isProduction
        ? 'https://api.midtrans.com'
        : 'https://api.sandbox.midtrans.com',

    'qris_acquirer' => env('MIDTRANS_QRIS_ACQUIRER', 'gopay'),

    'expiry_minutes' => (int) env('MIDTRANS_EXPIRY_MINUTES', 15),
];
