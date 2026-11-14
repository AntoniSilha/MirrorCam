@extends('layouts.landing')

@section('title', $produk->nama_produk . ' — MirrorCam')

@section('content')
@php
    $tenant = $produk->tenant;
    $namaToko = $tenant->nama_toko ?? 'Indonesia Gallery';
    $initials = collect(explode(' ', $namaToko))
        ->map(fn($w) => strtoupper(substr($w, 0, 1)))
        ->take(2)
        ->implode('');

    $photos = $produk->galeri->pluck('url_foto_produk')->map(function ($url) {
        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return $url;
        }
        return asset('storage/' . ltrim($url, '/'));
    })->values()->all();

    if (empty($photos)) {
        $photos = [$produk->primary_image];
    }

    $thumbPhotos = array_slice($photos, 0, 4);
    $defaultColor = strtolower($produk->color_produk ?? 'black');
@endphp

<div x-data="{
        activeImage: '{{ $photos[0] }}',
        selectedColor: '{{ $defaultColor }}',
        selectedDuration: '1 hari',
        basePrice: {{ (int) $produk->harga_sewa }},
        colors: [
            { name: 'black', hex: '#000000' },
            { name: 'silver', hex: '#d9d9d9' },
            { name: 'red', hex: '#ff0000' }
        ],
        durations: ['6 jam', '12 jam', '1 hari', '3 hari', '7 hari', '15 hari', '1 bln', '2 bln', '3 bln'],
        get durasiKode() {
            return this.selectedDuration.replace(/\s+/g, '_');
        },
        get totalHarga() {
            const multiplier = {
                '6 jam': 0.5,
                '12 jam': 0.75,
                '1 hari': 1,
                '3 hari': 3,
                '7 hari': 7,
                '15 hari': 15,
                '1 bln': 30,
                '2 bln': 60,
                '3 bln': 90
            };
            return Math.round(this.basePrice * (multiplier[this.selectedDuration] || 1));
        },
        get durasiLabel() {
            const daysMap = { '1 hari': 1, '3 hari': 3, '7 hari': 7, '15 hari': 15, '1 bln': 30, '2 bln': 60, '3 bln': 90 };
            const start = new Date();
            const fmt = (d) => d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
            if (daysMap[this.selectedDuration]) {
                const end = new Date(start);
                end.setDate(start.getDate() + daysMap[this.selectedDuration]);
                return `Durasi sewa: ${fmt(start)} - ${fmt(end)} (${this.selectedDuration})`;
            }
            return `Durasi sewa: ${this.selectedDuration} (Tanggal ${fmt(start)})`;
        }
     }"
     class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 font-['Montserrat']">

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-10 items-start">

        <!-- KOLOM KIRI: Galeri Foto Produk -->
        <div class="lg:col-span-6">
            <div class="aspect-4/3 w-full bg-[#f9f9f9] border border-black/10 rounded-[4px] overflow-hidden">
                <img :src="activeImage"
                     alt="{{ $produk->nama_produk }}"
                     class="w-full h-full object-cover transition duration-300">
            </div>

            <div class="grid grid-cols-5 gap-3 mt-3.5">
                @foreach($thumbPhotos as $photoUrl)
                    <button type="button"
                            @click="activeImage = '{{ $photoUrl }}'"
                            :class="activeImage === '{{ $photoUrl }}' ? 'border-[#000000] ring-1 ring-[#000000]' : 'border-black/15 opacity-80 hover:opacity-100'"
                            class="aspect-square rounded-[4px] border overflow-hidden bg-[#f9f9f9] transition">
                        <img src="{{ $photoUrl }}" alt="Thumbnail" class="w-full h-full object-cover">
                    </button>
                @endforeach

                @for($i = count($thumbPhotos); $i < 4; $i++)
                    <button type="button"
                            @click="activeImage = '{{ $photos[0] }}'"
                            class="aspect-square rounded-[4px] border border-black/15 overflow-hidden bg-[#f9f9f9] opacity-75 hover:opacity-100 transition">
                        <img src="{{ $photos[0] }}" alt="Thumbnail" class="w-full h-full object-cover">
                    </button>
                @endfor

                <button type="button"
                        @click="activeImage = '{{ $photos[0] }}'"
                        class="aspect-square rounded-[4px] border border-black/15 bg-[#ffffff] hover:bg-[#f9f9f9] flex items-center justify-center text-xs font-semibold text-[#000000] transition">
                    + {{ max(4, count($photos)) }} More
                </button>
            </div>
        </div>

        <!-- KOLOM KANAN: Detail Produk & Opsi Booking -->
        <div class="lg:col-span-6">
            <a href="{{ route('tenant.katalog', $produk->id_tenant) }}"
               class="inline-flex items-center gap-2.5 group mb-3">
                <div class="w-8 h-8 rounded-[4px] bg-[#000000] text-[#ffffff] flex items-center justify-center text-xs font-bold shrink-0">
                    {{ $initials }}
                </div>
                <span class="text-xs font-bold text-[#000000] group-hover:underline">
                    {{ $namaToko }}
                </span>
            </a>

            <h1 class="text-2xl sm:text-3xl font-bold text-[#000000] tracking-tight leading-snug">
                {{ $produk->nama_produk }}
            </h1>

            <div class="flex items-center gap-2 mt-2">
                <div class="flex items-center gap-0.5">
                    @for($s = 1; $s <= 4; $s++)
                        <svg class="w-4 h-4 fill-[#000000] text-[#000000]" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                    @endfor
                    <svg class="w-4 h-4 fill-[#d9d9d9] text-[#d9d9d9]" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                </div>
                <span class="text-xs text-black/50 font-medium">
                    {{ 42 + ($produk->id_produk * 3) }} ulasan
                </span>
            </div>

            <p class="text-xs sm:text-[13px] text-black/75 leading-relaxed mt-4">
                {{ $produk->deskripsi_lengkap ?? 'Peralatan produksi profesional terverifikasi inspeksi QC digital MirrorCam, siap digunakan untuk kebutuhan produksi komersial maupun sinematografi.' }}
            </p>

            <div class="mt-6">
                <span class="text-2xl sm:text-3xl font-bold text-[#000000]">
                    Rp {{ number_format($produk->harga_sewa, 0, ',', '.') }} / Hari
                </span>
            </div>

            <!-- Pilihan Warna -->
            <div class="mt-6">
                <div class="text-xs mb-2.5">
                    <span class="font-bold text-[#000000]">Color</span>
                    <span class="text-black/45 ml-1">- <span x-text="selectedColor"></span></span>
                </div>
                <div class="flex items-center gap-2.5">
                    <template x-for="c in colors" :key="c.name">
                        <button type="button"
                                @click="selectedColor = c.name"
                                :style="`background-color: ${c.hex}`"
                                :class="selectedColor === c.name ? 'ring-2 ring-offset-2 ring-[#000000]' : 'border border-black/15'"
                                class="w-9 h-9 rounded-[4px] transition cursor-pointer"
                                :title="c.name">
                        </button>
                    </template>
                </div>
            </div>

            <!-- Pilihan Durasi Sewa -->
            <div class="mt-6">
                <div class="text-xs mb-2.5">
                    <span class="font-bold text-[#000000]">Booking</span>
                    <span class="text-black/45 ml-1">- <span x-text="selectedDuration"></span></span>
                </div>
                <div class="grid grid-cols-3 sm:grid-cols-6 gap-2">
                    <template x-for="dur in durations" :key="dur">
                        <button type="button"
                                @click="selectedDuration = dur"
                                :class="selectedDuration === dur
                                    ? 'bg-[#000000] text-[#ffffff] border-[#000000]'
                                    : 'bg-[#ffffff] text-[#000000] border-black/25 hover:border-[#000000]'"
                                class="py-2.5 px-2 rounded-[4px] border text-xs font-medium text-center transition cursor-pointer"
                                x-text="dur">
                        </button>
                    </template>
                </div>
            </div>

            <!-- Tombol Booking Sekarang -->
<form method="POST" action="{{ route('keranjang.add') }}" class="mt-7">
    @csrf
    <input type="hidden" name="id_produk" value="{{ $produk->id_produk }}">
    <input type="hidden" name="warna" :value="selectedColor">
    <input type="hidden" name="durasi_kode" :value="durasiKode">
    <input type="hidden" name="durasi_label" :value="durasiLabel">
    <input type="hidden" name="total_harga" :value="totalHarga">

    <button type="submit"
            class="w-full py-3.5 px-6 rounded-[4px] bg-[#000000] hover:bg-black/85 text-[#ffffff] text-xs sm:text-sm font-semibold inline-flex items-center justify-center gap-2.5 transition cursor-pointer">
        <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/>
            <path d="M3 6h18"/>
            <path d="M16 10a4 4 0 0 1-8 0"/>
        </svg>
        <span>Booking Sekarang</span>
    </button>
</form>
        </div>

    </div>
</div>
@endsection
