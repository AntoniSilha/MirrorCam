@extends('layouts.landing')

@section('title', 'Pembayaran Berhasil — MirrorCam')

@section('content')
@php
    $tipeBayarLabel = $order['tipe_bayar'] === 'dp_50' ? 'DP 50%' : 'Bayar Lunas';
    $paidAt = ! empty($order['paid_at'])
        ? \Illuminate\Support\Carbon::parse($order['paid_at'])->locale('id')->translatedFormat('d F Y, H:i')
        : null;
@endphp

<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-10 font-['Montserrat']">
    <div class="bg-white border border-black/10 rounded-[4px] p-6 sm:p-8 text-center">
        <div class="w-14 h-14 mx-auto rounded-full bg-[#dcfce7] text-[#16a34a] flex items-center justify-center">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
            </svg>
        </div>

        <h1 class="text-2xl font-bold text-black mt-4">Pembayaran Berhasil</h1>
        <p class="text-xs text-black/55 mt-1.5">Terima kasih. Pembayaran QRIS kamu sudah kami terima.</p>

        <div class="mt-6 bg-[#f9f9f9] rounded-[4px] p-4 text-left space-y-3">
            <div class="flex items-center justify-between gap-4 text-xs">
                <span class="text-black/55">Kode Pesanan</span>
                <span class="font-bold text-black">{{ $order['order_id'] }}</span>
            </div>
            <div class="flex items-center justify-between gap-4 text-xs">
                <span class="text-black/55">Tipe Pembayaran</span>
                <span class="font-semibold text-black">{{ $tipeBayarLabel }}</span>
            </div>
            <div class="flex items-center justify-between gap-4 text-xs">
                <span class="text-black/55">Nominal Dibayar</span>
                <span class="font-extrabold text-black">Rp {{ number_format($order['gross_amount'], 0, ',', '.') }}</span>
            </div>
            @if($paidAt)
                <div class="flex items-center justify-between gap-4 text-xs">
                    <span class="text-black/55">Waktu Pembayaran</span>
                    <span class="font-semibold text-black">{{ $paidAt }}</span>
                </div>
            @endif
        </div>

        <div class="mt-5 text-left">
            <p class="text-[11px] font-bold uppercase tracking-wider text-black/45 mb-3">Item Pesanan</p>
            <div class="space-y-2.5">
                @foreach($order['items'] as $item)
                    <div class="flex items-start justify-between gap-3 text-xs">
                        <div>
                            <p class="font-semibold text-black">{{ $item['nama_produk'] }}</p>
                            <p class="text-black/45 mt-0.5">{{ $item['nama_toko'] ?? '-' }} &middot; {{ $item['durasi_label'] ?? '-' }}</p>
                        </div>
                        <span class="font-semibold text-black whitespace-nowrap">Rp {{ number_format((int) ($item['harga'] ?? 0), 0, ',', '.') }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="mt-7 flex flex-col sm:flex-row gap-2.5">
            <a href="{{ route('dashboard') }}" class="flex-1 py-3 px-4 rounded-[4px] bg-black text-white text-xs font-semibold hover:bg-black/85 transition text-center">
                Lihat Pesanan Saya
            </a>
            <a href="{{ route('katalog.index') }}" class="flex-1 py-3 px-4 rounded-[4px] bg-[#f0f0f0] text-black text-xs font-semibold hover:bg-[#e5e5e5] transition text-center">
                Kembali ke Katalog
            </a>
        </div>
    </div>
</div>
@endsection
