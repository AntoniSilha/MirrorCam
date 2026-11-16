@extends('layouts.landing')

@section('title', 'Nota Transaksi — MirrorCam')

@section('content')
@php
    $paidAt = $transaction['settlement_time'] ?? $transaction['transaction_time'] ?? null;
    $amount = (float) ($transaction['gross_amount'] ?? $booking->total_harga);
    $paymentStatus = ucfirst((string) ($transaction['transaction_status'] ?? 'pending'));
@endphp
<div class="mx-auto max-w-2xl px-4 py-10 sm:px-6 lg:px-8">
    <article class="rounded-lg border border-neutral-200 bg-white p-6 sm:p-8">
        <div class="flex items-start justify-between gap-4 border-b border-dashed border-neutral-300 pb-5">
            <div>
                <p class="text-xs font-extrabold tracking-widest text-black">MIRRORCAM</p>
                <h1 class="mt-2 text-xl font-bold text-black">Nota Transaksi</h1>
                <p class="mt-1 text-xs text-neutral-500">Bukti transaksi berdasarkan data Midtrans</p>
            </div>
            <span class="rounded-full bg-green-100 px-3 py-1 text-[10px] font-bold uppercase text-green-800">{{ $paymentStatus }}</span>
        </div>

        <dl class="mt-5 space-y-3 text-xs">
            <div class="flex justify-between gap-4"><dt class="text-neutral-500">ID Transaksi</dt><dd class="text-right font-semibold text-black">{{ $transaction['transaction_id'] ?? $orderId }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-neutral-500">ID Pesanan</dt><dd class="text-right font-semibold text-black">{{ $transaction['order_id'] ?? $orderId }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-neutral-500">Status Pembayaran</dt><dd class="text-right font-semibold text-black">{{ $paymentStatus }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-neutral-500">Metode Pembayaran</dt><dd class="text-right font-semibold uppercase text-black">{{ $transaction['payment_type'] ?? 'QRIS' }}</dd></div>
            @if ($paidAt)
                <div class="flex justify-between gap-4"><dt class="text-neutral-500">Waktu Transaksi</dt><dd class="text-right font-semibold text-black">{{ \Illuminate\Support\Carbon::parse($paidAt)->locale('id')->translatedFormat('d M Y, H:i') }}</dd></div>
            @endif
        </dl>

        <div class="mt-6 border-y border-dashed border-neutral-300 py-5">
            <p class="text-[10px] font-bold uppercase tracking-wider text-neutral-500">Detail Pesanan</p>
            <div class="mt-3 flex justify-between gap-4 text-xs">
                <div>
                    <p class="font-bold text-black">{{ $booking->produk?->nama_produk ?? 'Produk' }}</p>
                    <p class="mt-1 text-neutral-500">{{ $booking->tenant?->nama_toko ?? 'Tenant' }} · {{ $booking->sub_order_eksternal }}</p>
                </div>
                <p class="whitespace-nowrap font-semibold text-black">Rp {{ number_format((float) $booking->total_harga, 0, ',', '.') }}</p>
            </div>
        </div>

        <div class="flex justify-between py-5 text-sm">
            <span class="font-bold text-black">Nominal Transaksi Midtrans</span>
            <span class="font-extrabold text-black">Rp {{ number_format($amount, 0, ',', '.') }}</span>
        </div>

        <div class="mt-3 flex gap-2 print:hidden">
            <a href="{{ route('pesanan.status', $booking->id_pemesanan) }}" class="flex-1 rounded-md border border-neutral-300 px-4 py-2.5 text-center text-xs font-semibold text-black">Kembali ke Pelacakan</a>
            <button type="button" onclick="window.print()" class="flex-1 rounded-md bg-black px-4 py-2.5 text-xs font-semibold text-white">Cetak Nota</button>
        </div>
    </article>
</div>
<style>@media print { header, footer { display: none !important; } body { background: #fff !important; } }</style>
@endsection
