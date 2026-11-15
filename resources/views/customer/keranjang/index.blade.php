@extends('layouts.landing')

@section('title', 'Keranjang Sewa — MirrorCam')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 font-['Montserrat']">

    @if(session('success'))
        <div class="mb-5 px-4 py-3 rounded-[4px] bg-[#dcfce7] border border-[#16a34a]/30 text-[#16a34a] text-xs font-semibold">
            {{ session('success') }}
        </div>
    @endif

    <!-- Header Judul -->
    <div class="mb-6">
        <h1 class="text-2xl sm:text-3xl font-bold text-[#000000] tracking-tight">
            Keranjang Sewa ({{ $groupedByTenant->count() }} Tenant)
        </h1>
        <p class="text-xs sm:text-sm text-black/60 mt-1">
            Kode Transaksi Induk: <strong class="text-[#000000]">{{ $kodeTransaksiInduk }}</strong>
        </p>
    </div>

    @if($cartItems->isEmpty())
        <div class="bg-[#ffffff] border border-black/10 rounded-[4px] p-12 text-center">
            <p class="text-sm font-bold text-[#000000]">Keranjang sewa kamu masih kosong</p>
            <p class="text-xs text-black/50 mt-1">Silakan pilih kamera, lensa, atau studio dari katalog terlebih dahulu.</p>
            <a href="{{ route('katalog.index') }}" class="inline-block mt-4 px-5 py-2.5 rounded-[4px] bg-[#000000] text-[#ffffff] text-xs font-semibold">
                Jelajahi Katalog Produk
            </a>
        </div>
    @else
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

            <!-- Kolom Kiri: Daftar Item Dikelompokkan Per Tenant -->
            <div class="lg:col-span-8 space-y-4">
                @foreach($groupedByTenant as $tenantId => $items)
                    @php
                        $namaTenant =$items->first()['nama_toko'];
                        $subtotalTenant =$items->sum('harga');
                        $subRef = 'MC-' . substr(md5($kodeTransaksiInduk . $tenantId), 0, 4) . '-SUB' .$loop->iteration;
                    @endphp

                    <div class="bg-[#ffffff] border border-black/10 rounded-[4px] overflow-hidden">
                        <!-- Header Tenant -->
                        <div class="px-5 py-3 bg-[#f9f9f9] border-b border-black/10 flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <span class="w-3.5 h-3.5 rounded-full bg-[#000000] inline-block"></span>
                                <span class="text-xs font-bold uppercase tracking-wide text-[#000000]">{{ $namaTenant }}</span>
                            </div>
                            <span class="text-[11px] text-black/45 font-medium">Sub-Ref: {{ strtoupper($subRef) }}</span>
                        </div>

                        <!-- Daftar Produk di Tenant Ini -->
                        <div class="divide-y divide-black/10">
                            @foreach($items as $item)
                                <div class="p-5 flex items-center justify-between gap-4">
                                    <div class="flex items-center gap-4">
                                        <img src="{{ $item['foto'] }}" alt="{{ $item['nama_produk'] }}"
                                             class="w-16 h-16 rounded-[4px] object-cover bg-[#f9f9f9] border border-black/10 shrink-0">
                                        <div>
                                            <h3 class="text-sm font-bold text-[#000000]">{{ $item['nama_produk'] }}</h3>
                                            <p class="text-xs text-black/55 mt-0.5">{{ $item['durasi_label'] }}</p>
                                            <p class="text-[11px] text-black/45 mt-0.5 capitalize">Varian: {{ $item['warna'] }}</p>
                                        </div>
                                    </div>

                                    <div class="text-right">
                                        <p class="text-sm font-bold text-[#000000]">
                                            Rp {{ number_format($item['harga'], 0, ',', '.') }}
                                        </p>
                                        <form method="POST" action="{{ route('keranjang.remove') }}" class="mt-1.5">
                                            @csrf
                                            <input type="hidden" name="item_key" value="{{ $item['item_key'] }}">
                                            <button type="submit" class="text-[11px] text-[#ba1a1a] hover:underline font-medium">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Subtotal Per Tenant -->
                        <div class="px-5 py-3.5 bg-[#ffffff] border-t border-black/10 flex items-center justify-between text-xs">
                            <span class="text-black/60 font-medium">Subtotal {{ $namaTenant }}</span>
                            <span class="text-sm font-bold text-[#000000]">Rp {{ number_format($subtotalTenant, 0, ',', '.') }}</span>
                        </div>
                    </div>
                @endforeach

                <div class="pt-1">
                    <a href="{{ route('katalog.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#000000] hover:underline">
                        + Tambah Peralatan dari Tenant Lain
                    </a>
                </div>
            </div>

            <!-- Kolom Kanan: Enkripsi Verifikasi KYC & Ringkasan Pembayaran -->
            <form method="POST" action="{{ route('keranjang.checkout') }}" enctype="multipart/form-data"
                  x-data="{ fileName: '{{ session('mirrorcam_kyc_filename', 'KTP_Verified.jpg') }}' }"
                  class="lg:col-span-4 bg-[#ffffff] border border-black/10 rounded-[4px] p-5 space-y-5">
                @csrf
                <input type="hidden" name="total_booking" value="{{ $totalBooking }}">

                <div>
                    <h2 class="text-base font-bold text-[#000000]">Enkripsi Verifikasi KYC</h2>
                    <p class="text-xs text-black/55 mt-1.5 leading-relaxed">
                        Setiap tenant membutuhkan dokumen yang sah untuk menerbitkan formulir serah terima alat. Data Anda dijamin aman dengan standar privasi MirrorCam.
                    </p>
                </div>

                <!-- Unggahan Identitas -->
                <div>
                    <label class="block text-xs font-bold text-[#000000] mb-2">Unggahan Identitas</label>
                    <label class="block p-3.5 rounded-[4px] border border-[#000000] bg-[#ffffff] cursor-pointer hover:bg-[#f9f9f9] transition">
                        <input type="file" name="ktp_file" accept=".jpg,.jpeg,.png,.pdf" class="hidden"
                               @change="if($event.target.files.length) fileName =$event.target.files[0].name">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-bold text-[#000000]" x-text="fileName"></p>
                                <p class="text-[11px] font-semibold text-[#16a34a] mt-0.5">Success</p>
                            </div>
                            <span class="text-[10px] font-semibold px-2 py-1 rounded-[4px] bg-[#f9f9f9] border border-black/15 text-[#000000]">
                                Ganti File
                            </span>
                        </div>
                    </label>
                </div>

                <!-- Rincian Total -->
                <div class="pt-3 border-t border-black/10 space-y-2.5">
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-black/60">Subtotal</span>
                        <span class="font-bold text-[#000000]">Rp {{ number_format($totalBooking, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center justify-between pt-1">
                        <span class="text-xs font-bold text-[#000000]">Total Booking</span>
                        <span class="text-lg font-extrabold text-[#000000]">Rp {{ number_format($totalBooking, 0, ',', '.') }}</span>
                    </div>
                </div>

                <!-- Tombol DP 50% & Bayar Sekarang -->
                <div class="space-y-2.5 pt-1">
                    <button type="submit" name="tipe_bayar" value="dp_50"
                            class="w-full py-3 px-4 rounded-[4px] bg-[#000000] hover:bg-black/85 text-[#ffffff] text-xs font-semibold transition">
                        DP 50% (Rp {{ number_format($totalBooking * 0.5, 0, ',', '.') }})
                    </button>
                    <button type="submit" name="tipe_bayar" value="lunas"
                            class="w-full py-3 px-4 rounded-[4px] bg-[#000000] hover:bg-black/85 text-[#ffffff] text-xs font-semibold transition">
                        Bayar Sekarang
                    </button>
                </div>
            </form>
        </div>
    @endif
</div>
@endsection
