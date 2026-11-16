@extends('layouts.landing')

@section('title', ($tenant->nama_toko ?? 'Tenant') . ' — Detail Katalog Tenant MirrorCam')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10">
    <!-- Breadcrumb / Tombol Kembali -->
    <div class="mb-6">
        <a href="{{ route('tenant.index') }}" class="inline-flex items-center gap-2 text-xs font-semibold text-gray-600 hover:text-black transition">
            <span aria-hidden="true">&larr;</span>
            <span>Kembali ke Eksplorasi Tenant</span>
        </a>
    </div>

    <!-- Header Profil Tenant -->
    <div class="bg-[#ffffff] rounded-[4px] border border-[#e5e5e5] p-6 sm:p-8 shadow-xs">
        <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-6">
            <!-- Info Kiri: Logo + Nama + Bio -->
            <div class="flex items-start gap-4">
                <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-[4px] bg-[#000000] text-[#ffffff] flex items-center justify-center font-bold text-xl sm:text-2xl shrink-0 overflow-hidden shadow-xs">
                    @if (!empty($tenant->url_logo))
                        <img src="{{ $tenant->url_logo }}" alt="{{ $tenant->nama_toko }}" class="w-full h-full object-cover">
                    @else
                        <span>{{ $tenant->initials }}</span>
                    @endif
                </div>

                <div class="space-y-1.5">
                    <div class="flex flex-wrap items-center gap-2.5">
                        <h1 class="text-xl sm:text-2xl font-bold text-[#000000]">
                            {{ $tenant->nama_toko }}
                        </h1>
                        @if ($tenant->is_official)
                            <span class="bg-[#f0f0f0] text-[#000000] text-[9.5px] font-bold tracking-wider px-2.5 py-1 rounded-[4px] uppercase">
                                OFFICIAL PARTNER
                            </span>
                            <span class="bg-[#dcfce7] text-[#16a34a] text-[9.5px] font-bold tracking-wider px-2.5 py-1 rounded-[4px] uppercase">
                                TERVERIFIKASI
                            </span>
                        @else
                            <span class="bg-amber-50 text-amber-800 text-[9.5px] font-bold tracking-wider px-2.5 py-1 rounded-[4px] uppercase">
                                BELUM TERVERIFIKASI
                            </span>
                        @endif
                    </div>

                    <p class="text-xs text-gray-500 font-medium">
                        {{ $tenant->service_category }} &bull; {{ $tenant->kota }}
                    </p>

                    <p class="text-xs text-gray-600 leading-relaxed max-w-2xl pt-1">
                        {{ $tenant->bio_deskripsi ?: 'Penyedia gear bioskop dan multimedia terverifikasi dengan standar operasional ketat.' }}
                    </p>
                </div>
            </div>

            <!-- Kontak & WhatsApp WhatsApp Resmi (Kanan) -->
            <div class="flex flex-col sm:flex-row md:flex-col gap-2.5 shrink-0">
                @if (!empty($tenant->whatsapp_resmi))
                    @php
                        $cleanPhone = preg_replace('/[^0-9]/', '', $tenant->whatsapp_resmi);
                        if (str_starts_with($cleanPhone, '0')) {
                            $cleanPhone = '62' . substr($cleanPhone, 1);
                        }
                    @endphp
                    <a
                        href="https://wa.me/{{ $cleanPhone }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center justify-center gap-2 bg-[#000000] text-[#ffffff] hover:bg-neutral-800 px-5 py-2.5 rounded-[4px] text-xs font-semibold shadow-xs transition"
                    >
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86.174.086.275.072.376-.044.101-.116.433-.506.549-.68.116-.173.231-.145.39-.086.159.058 1.011.477 1.184.564.173.087.289.13.332.203.043.072.043.419-.101.824z"/>
                        </svg>
                        <span>Hubungi via WhatsApp</span>
                    </a>
                @endif

                @if ($tenant->detailOperasional)
                    <div class="text-[11px] text-gray-500 bg-[#f9f9f9] border border-gray-200 p-2.5 rounded-[4px] leading-tight space-y-1">
                        <div class="font-bold text-black">Jam Operasional:</div>
                        <div>{{ substr($tenant->detailOperasional->jam_buka, 0, 5) }} - {{ substr($tenant->detailOperasional->jam_tutup, 0, 5) }} WIB</div>
                        <div class="text-[10px] text-gray-400">{{ $tenant->detailOperasional->hari_operasional }}</div>
                    </div>
                @endif
            </div>
        </div>

        <!-- 3 Metrik Ringkasan Tenant -->
        <div class="mt-6 pt-6 border-t border-gray-200 grid grid-cols-1 sm:grid-cols-3 divide-y sm:divide-y-0 sm:divide-x divide-gray-200 gap-4 sm:gap-0 bg-[#f9f9f9] rounded-[4px] p-4 text-center">
            <div class="py-1">
                <span class="block text-xl font-bold text-[#000000]">{{ $tenant->formatted_rating }}</span>
                <span class="text-xs text-gray-500 font-medium">{{ $tenant->review_count_text }}</span>
            </div>
            <div class="py-1">
                <span class="block text-xl font-bold text-[#000000]">{{ $tenant->unit_siap_pakai }}</span>
                <span class="text-xs text-gray-500 font-medium">Unit Siap Pakai</span>
            </div>
            <div class="py-1">
                <span class="block text-xl font-bold text-[#000000]">{{ $tenant->kota }}</span>
                <span class="text-xs text-gray-500 font-medium">Lokasi Toko</span>
            </div>
        </div>
    </div>

    <!-- Katalog Produk Tenant -->
    <div class="mt-10">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-lg sm:text-xl font-bold text-[#000000]">
                    Katalog Gear Tersedia
                </h2>
                <p class="text-xs text-gray-500 mt-0.5">
                    Pilihan perlengkapan kamera bioskop, lensa, dan aksesoris siap sewa dari {{ $tenant->nama_toko }}.
                </p>
            </div>
            <span class="text-xs font-semibold text-gray-500 bg-white border border-gray-200 px-3 py-1 rounded-[4px]">
                {{ $tenant->produks->count() }} Item
            </span>
        </div>

        @if ($tenant->produks->isNotEmpty())
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($tenant->produks as $produk)
                    <div class="bg-[#ffffff] rounded-[4px] border border-[#e5e5e5] p-4 flex flex-col justify-between hover:shadow-xs transition">
                        <div>
                            <!-- Foto Produk -->
                            <div class="w-full h-44 bg-gray-100 rounded-[4px] overflow-hidden mb-3 relative">
                                <img
                                    src="{{ $produk->primary_image }}"
                                    alt="{{ $produk->nama_produk }}"
                                    class="w-full h-full object-cover"
                                >
                                @if ($produk->total_stok_unit > 0)
                                    <span class="absolute top-2 right-2 bg-[#dcfce7] text-[#16a34a] text-[9.5px] font-bold px-2 py-0.5 rounded-[4px]">
                                        Ready ({{ $produk->total_stok_unit }})
                                    </span>
                                @else
                                    <span class="absolute top-2 right-2 bg-[#ffdad6] text-[#ba1a1a] text-[9.5px] font-bold px-2 py-0.5 rounded-[4px]">
                                        Habis
                                    </span>
                                @endif
                            </div>

                            <span class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block">
                                {{ $produk->kategori->nama_kategori ?? 'Peralatan' }}
                            </span>
                            <h3 class="text-sm font-bold text-[#000000] mt-1 leading-tight line-clamp-2">
                                {{ $produk->nama_produk }}
                            </h3>
                            <p class="text-xs text-gray-500 mt-1 line-clamp-2 leading-relaxed">
                                {{ $produk->deskripsi_lengkap }}
                            </p>
                        </div>

                        <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between">
                            <div>
                                <span class="text-[10px] text-gray-400 block">Tarif Sewa</span>
                                <span class="text-xs font-bold text-[#000000]">
                                    Rp {{ number_format($produk->harga_sewa, 0, ',', '.') }} <span class="text-[10px] text-gray-500 font-normal">/ hari</span>
                                </span>
                            </div>

                            <a href="{{ route('produk.show', $produk->id_produk) }}" class="bg-[#000000] text-[#ffffff] text-xs font-semibold px-3 py-1.5 rounded-[4px] hover:bg-neutral-800 transition">
                                Detail
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="bg-[#ffffff] rounded-[4px] border border-[#e5e5e5] p-10 text-center shadow-xs">
                <p class="text-xs text-gray-500">
                    Tenant ini belum memiliki unit gear yang terdaftar aktif dalam katalog.
                </p>
            </div>
        @endif
    </div>

    <!-- Review & Ulasan Pelanggan -->
    <div class="mt-12">
        <h2 class="text-lg sm:text-xl font-bold text-[#000000] mb-4">
            Ulasan Pelanggan ({{ $tenant->reviews->count() }})
        </h2>

        @if ($tenant->reviews->isNotEmpty())
            <div class="space-y-3">
                @foreach ($tenant->reviews as $review)
                    <div class="bg-[#ffffff] rounded-[4px] border border-[#e5e5e5] p-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-xs text-black">
                                    {{ $review->penyewa->nama_lengkap_ktp ?? $review->penyewa->name ?? 'Penyewa Terverifikasi' }}
                                </span>
                                <span class="text-[10px] text-gray-400">
                                    &bull; {{ $review->created_at?->diffForHumans() }}
                                </span>
                            </div>
                            <div class="flex items-center gap-1 text-[#000000] text-xs font-bold">
                                <span>&#9733;</span>
                                <span>{{ number_format($review->rating_nilai, 1) }}</span>
                            </div>
                        </div>
                        @if ($review->komentar_text)
                            <p class="text-xs text-gray-600 mt-2 leading-relaxed">
                                {{ $review->komentar_text }}
                            </p>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <div class="bg-[#ffffff] rounded-[4px] border border-[#e5e5e5] p-8 text-center shadow-xs">
                <p class="text-xs text-gray-500">
                    Belum ada ulasan untuk tenant ini.
                </p>
            </div>
        @endif
    </div>
</div>
@endsection
