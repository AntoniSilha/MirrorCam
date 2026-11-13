<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Rekening Tujuan Pembayaran Komisi
    |--------------------------------------------------------------------------
    |
    | Informasi rekening platform yang ditampilkan pada halaman pembayaran
    | komisi tenant di dashboard mitra. Nilai dapat disesuaikan via env.
    |
    */

    'platform_bank' => [
        'atas_nama' => env('PLATFORM_BANK_ACCOUNT_NAME', 'PT. MirrorCam'),
        'nama_bank' => env('PLATFORM_BANK_NAME', 'BCA PRIORITAS'),
        'nomor_rekening' => env('PLATFORM_BANK_ACCOUNT_NUMBER', '9999999999999999'),
    ],

];
