@extends('layouts.landing')

@section('title', 'Pembayaran QRIS — MirrorCam')

@section('content')
<style>
    [x-cloak] { display: none !important; }
</style>
@php
    $tipeBayarLabel = $order['tipe_bayar'] === 'dp_50' ? 'DP 50%' : 'Bayar Lunas';
    $status = $order['status'] ?? 'pending';
@endphp

<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8 font-['Montserrat']"
     x-data="paymentModal({
        statusUrl: @js(route('pembayaran.status', $order['token'])),
        initialStatus: @js($status),
        initialPaid: @js($status === 'paid'),
        expiresAt: @js($order['expires_at']),
     })">

    @if(session('success'))
        <div class="mb-5 px-4 py-3 rounded-[4px] bg-[#dcfce7] border border-[#16a34a]/30 text-[#16a34a] text-xs font-semibold">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-5 px-4 py-3 rounded-[4px] bg-[#ffdad6] border border-[#ba1a1a]/30 text-[#ba1a1a] text-xs font-semibold">
            {{ session('error') }}
        </div>
    @endif

    <a href="{{ route('keranjang.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-black/60 hover:text-black transition mb-5">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Kembali ke Keranjang
    </a>

    <div class="bg-white border border-black/10 rounded-[4px] p-5 sm:p-7">
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-black/45">Kode Pesanan</p>
                <h1 class="text-xl sm:text-2xl font-bold text-black mt-1">{{ $order['order_id'] }}</h1>
                <p class="text-xs text-black/55 mt-1.5">{{ $tipeBayarLabel }} &middot; {{ count($order['items'] ?? []) }} item</p>
            </div>
            <div class="text-left sm:text-right">
                <p class="text-[11px] font-bold uppercase tracking-wider text-black/45">Total Dibayar</p>
                <p class="text-2xl sm:text-3xl font-extrabold text-black mt-1">
                    Rp {{ number_format($order['gross_amount'], 0, ',', '.') }}
                </p>
                @if($order['tipe_bayar'] === 'dp_50')
                    <p class="text-[11px] text-black/45 mt-1">dari total Rp {{ number_format($order['total_booking'], 0, ',', '.') }}</p>
                @endif
            </div>
        </div>

        <div class="mt-6 pt-5 border-t border-black/10">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-sm font-bold text-black">Pembayaran via QRIS</p>
                    <p class="text-xs text-black/55 mt-1 max-w-md leading-relaxed">
                        Scan QRIS menggunakan aplikasi bank atau dompet digital. Nominal pembayaran sudah terisi otomatis, cukup konfirmasi pembayaran.
                    </p>
                </div>
                <button type="button" @click="open = true"
                        class="shrink-0 px-4 py-2.5 rounded-[4px] bg-black text-white text-xs font-semibold hover:bg-black/85 transition">
                    Tampilkan QRIS
                </button>
            </div>
        </div>

        <div class="mt-5 bg-[#f9f9f9] rounded-[4px] p-4">
            <p class="text-[11px] font-bold uppercase tracking-wider text-black/45 mb-3">Rincian Item</p>
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
    </div>

    <!-- Popup QRIS -->
    <div x-show="open" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-label="Pembayaran QRIS">
        <div class="absolute inset-0 bg-black/60" @click="open = false"></div>

        <div class="relative w-full max-w-md bg-white rounded-[4px] shadow-xl">
            <div class="flex items-start justify-between gap-4 px-5 pt-5">
                <div>
                    <h2 class="text-base font-bold text-black">Pembayaran QRIS</h2>
                    <p class="text-[11px] text-black/50 mt-0.5">Kode: {{ $order['order_id'] }}</p>
                </div>
                <button type="button" @click="open = false" class="p-1 text-black/50 hover:text-black transition" aria-label="Tutup popup pembayaran">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="px-5 pb-5">
                <div class="mt-4 flex items-center justify-between gap-3">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wider text-black/45">Nominal</p>
                        <p class="text-2xl font-extrabold text-black mt-0.5">Rp {{ number_format($order['gross_amount'], 0, ',', '.') }}</p>
                    </div>
                    <span class="text-[10px] font-bold px-2.5 py-1 rounded-[4px] uppercase tracking-wider"
                          :class="paid ? 'bg-[#dcfce7] text-[#16a34a]' : (status === 'failed' ? 'bg-[#ffdad6] text-[#ba1a1a]' : 'bg-[#ffedd5] text-[#c2410c]')"
                          x-text="statusLabel"></span>
                </div>

                <div class="mt-5 flex justify-center">
                    @if(! empty($order['qr_url']))
                        <img src="{{ $order['qr_url'] }}" alt="QRIS {{ $order['order_id'] }}" class="w-64 h-64 object-contain bg-white p-2 border border-black/10 rounded-[4px]">
                    @elseif(! empty($order['qr_data_uri']))
                        <img src="{{ $order['qr_data_uri'] }}" alt="QRIS {{ $order['order_id'] }}" class="w-64 h-64 bg-white p-2 border border-black/10 rounded-[4px]">
                    @else
                        <div class="w-64 h-64 bg-[#f9f9f9] border border-dashed border-black/20 rounded-[4px] flex flex-col items-center justify-center text-center px-6">
                            <svg class="w-9 h-9 text-black/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v1m6.364 1.636l-.707.707M20 12h-1M17.657 17.657l-.707-.707M12 20v-1M6.343 17.657l.707-.707M4 12H5M6.343 6.343l.707.707M9 9h6v6H9V9z"/>
                            </svg>
                            <p class="text-xs font-semibold text-black/60 mt-3">QRIS belum tersedia</p>
                            <p class="text-[11px] text-black/40 mt-1">Coba muat ulang halaman atau buat ulang QRIS.</p>
                        </div>
                    @endif
                </div>

                @if(! empty($order['qr_url']))
                    <p class="mt-3 text-[11px] leading-relaxed text-black/50 text-center">
                        Untuk simulator Sandbox: klik kanan QR di atas &rarr; <strong>Copy image address</strong>,
                        lalu tempel di
                        <a href="https://simulator.sandbox.midtrans.com/openapi/qris/index" target="_blank" rel="noopener" class="underline hover:text-black">
                            Midtrans QRIS Simulator
                        </a>.
                    </p>
                @endif

                @if($order['mode'] === 'demo')
                    <div class="mt-4 px-3 py-2.5 rounded-[4px] bg-[#ffedd5] border border-[#facc15]/40 text-[11px] leading-relaxed text-[#92400e]">
                        Mode demo: Midtrans belum dikonfigurasi. Isi <strong>MIDTRANS_SERVER_KEY</strong> di <code>.env</code> untuk QRIS asli.
                    </div>
                @endif

                @if(! empty($order['error']))
                    <div class="mt-4 px-3 py-2.5 rounded-[4px] bg-[#ffdad6] border border-[#ba1a1a]/30 text-[11px] leading-relaxed text-[#ba1a1a]">
                        {{ $order['error'] }}
                    </div>
                @endif

                @if($order['mode'] === 'midtrans' || ! empty($order['error']) || (empty($order['qr_data_uri']) && empty($order['qr_url'])))
                    <form method="POST" action="{{ route('pembayaran.qris', $order['token']) }}" class="mt-2">
                        @csrf
                        <button type="submit" class="w-full py-2.5 rounded-[4px] bg-black text-white text-xs font-semibold hover:bg-black/85 transition">
                            Buat Ulang QRIS
                        </button>
                    </form>
                @endif

                <p class="mt-4 text-[11px] leading-relaxed text-black/50 text-center">
                    Nominal otomatis terisi saat QRIS dipindai. Selesaikan pembayaran di aplikasi bank atau e-wallet, lalu kembali ke halaman ini.
                </p>

                <div class="mt-4 flex items-center justify-between gap-2">
                    <span class="text-[11px] font-medium text-black/50" x-text="countdown"></span>
                    <button type="button" @click="checkStatus()" :disabled="checking || paid"
                            class="px-4 py-2.5 rounded-[4px] bg-black text-white text-xs font-semibold hover:bg-black/85 transition disabled:opacity-50">
                        <span x-show="!checking">Cek Status Pembayaran</span>
                        <span x-show="checking" x-cloak>Memeriksa...</span>
                    </button>
                </div>

                <p class="mt-2 text-center text-[11px] font-medium min-h-4" x-text="message"></p>
            </div>
        </div>
    </div>
</div>

<script>
    function paymentModal(config) {
        return {
            open: true,
            status: config.initialStatus || 'pending',
            paid: Boolean(config.initialPaid),
            expiresAt: config.expiresAt,
            checking: false,
            message: '',
            countdown: '',

            get statusLabel() {
                if (this.paid) return 'Berhasil';
                if (this.status === 'failed') return 'Gagal';
                return 'Menunggu';
            },

            init() {
                this.tick();
                setInterval(() => {
                    this.tick();
                    if (!this.paid) this.checkStatus(true);
                }, 5000);
            },

            tick() {
                if (!this.expiresAt) {
                    this.countdown = 'Selesaikan pembayaran sebelum QRIS kedaluwarsa.';
                    return;
                }

                const expiry = new Date(String(this.expiresAt).replace(' ', 'T')).getTime();
                const remaining = Math.max(0, expiry - Date.now());

                if (remaining <= 0) {
                    this.countdown = 'QRIS telah kedaluwarsa.';
                    return;
                }

                const minutes = String(Math.floor(remaining / 60000)).padStart(2, '0');
                const seconds = String(Math.floor((remaining % 60000) / 1000)).padStart(2, '0');
                this.countdown = 'Sisa waktu ' + minutes + ':' + seconds;
            },

            async checkStatus(silent = false) {
                if (this.checking || this.paid) return;

                this.checking = true;

                try {
                    const response = await fetch(config.statusUrl, {
                        headers: { 'Accept': 'application/json' },
                    });
                    const data = await response.json();

                    this.status = data.status || this.status;

                    if (data.paid) {
                        this.paid = true;
                        this.message = data.message || 'Pembayaran berhasil diterima.';
                        setTimeout(() => {
                            window.location.href = data.redirect;
                        }, 1200);
                    } else if (!silent) {
                        this.message = data.message || 'Menunggu pembayaran.';
                    }
                } catch (error) {
                    if (!silent) this.message = 'Gagal memeriksa status pembayaran.';
                } finally {
                    this.checking = false;
                }
            },
        };
    }
</script>
@endsection
