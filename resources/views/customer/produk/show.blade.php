@extends('layouts.landing')

@section('title', $produk->nama_produk . ' — MirrorCam')

@section('content')
@php
    $tenant = $produk->tenant;
    $namaToko = $tenant->nama_toko ?? 'Indonesia Gallery';

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
    $availableColors = array_values(array_filter(array_map('trim', explode(',', (string) $produk->color_produk))));
    $defaultColor = $availableColors[0] ?? 'Black';
    $colorHex = [
        'black' => '#111827', 'white' => '#ffffff', 'grey' => '#9ca3af', 'silver' => '#d1d5db',
        'red' => '#ef4444', 'maroon' => '#7f1d1d', 'orange' => '#f97316', 'yellow' => '#facc15',
        'gold' => '#d4af37', 'green' => '#22c55e', 'olive' => '#808000', 'teal' => '#14b8a6',
        'cyan' => '#06b6d4', 'blue' => '#3b82f6', 'navy' => '#1e3a8a', 'purple' => '#8b5cf6',
        'violet' => '#7c3aed', 'pink' => '#ec4899', 'brown' => '#92400e', 'beige' => '#d6c4a1',
        'cream' => '#fff7d6', 'rose gold' => '#b76e79',
    ];
    $colorOptions = collect($availableColors)->map(fn ($name) => [
        'name' => $name,
        'hex' => $colorHex[strtolower($name)] ?? '#9ca3af',
    ])->values();

    $durationCatalog = [
        '6_jam' => '6 Jam',
        '12_jam' => '12 Jam',
        '1_hari' => '1 Hari',
        '3_hari' => '3 Hari',
        '7_hari' => '7 Hari',
        '15_hari' => '15 Hari',
        '1_bulan' => '1 Bulan',
        '2_bulan' => '2 Bulan',
        '3_bulan' => '3 Bulan',
    ];

    $configuredDurations = $produk->durasi->keyBy('durasi');

    $durationOptions = collect($durationCatalog)->map(function ($label, $code) use ($configuredDurations) {
        $durasi = $configuredDurations->get($code);

        return [
            'code' => $code,
            'label' => $durasi->nama_durasi ?? $label,
            'harga' => $durasi ? (int) $durasi->harga : 0,
            'available' => $durasi !== null,
        ];
    })->values();

    $defaultDuration = $durationOptions->firstWhere('code', '12_jam');

    if (! ($defaultDuration['available'] ?? false)) {
        $defaultDuration = $durationOptions->firstWhere('available', true);
    }

    $defaultDurationCode = $defaultDuration['code'] ?? null;

    $productRating = (float) ($produk->avg_rating ?? $produk->reviews_avg_rating_nilai ?? 0);
    $productReviewCount = (int) ($produk->reviews_count ?? 0);
    $filledStars = (int) round($productRating);
@endphp

<div x-data="{
        activeImage: @js($photos[0]),
        selectedColor: @js($defaultColor),
        selectedDuration: @js($defaultDurationCode),
        colors: @js($colorOptions),
        durations: @js($durationOptions),
        get selectedDurationOption() {
            return this.durations.find(d => d.code === this.selectedDuration) || null;
        },
        get selectedDurationLabel() {
            return this.selectedDurationOption ? this.selectedDurationOption.label : 'Tidak tersedia';
        },
        get durasiKode() {
            return this.selectedDurationOption ? this.selectedDurationOption.code : '';
        },
        get totalHarga() {
            return this.selectedDurationOption ? this.selectedDurationOption.harga : 0;
        },
        get durasiLabel() {
            const daysByCode = { '1_hari': 1, '3_hari': 3, '7_hari': 7, '15_hari': 15, '1_bulan': 30, '2_bulan': 60, '3_bulan': 90 };
            if (! this.selectedDurationOption) return '';
            const start = new Date();
            const fmt = (d) => d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
            const days = daysByCode[this.selectedDuration];
            if (days) {
                const end = new Date(start);
                end.setDate(start.getDate() + days);
                return `Durasi sewa: ${fmt(start)} - ${fmt(end)} (${this.selectedDurationLabel})`;
            }
            return `Durasi sewa: ${this.selectedDurationLabel} (Tanggal ${fmt(start)})`;
        }
     }"
     class="max-w-[1360px] mx-auto px-4 sm:px-6 py-6 lg:py-8 font-['Montserrat']">

    <div class="grid grid-cols-1 lg:grid-cols-[1.2fr_1fr] gap-8 lg:gap-10">

        <!-- KOLOM KIRI: Galeri Foto Produk -->
        <div class="min-w-0 flex flex-col">
            <div class="relative w-full aspect-4/3 lg:aspect-auto lg:flex-1 lg:min-h-0 bg-[#f9f9f9] border border-black/10 rounded-[4px] overflow-hidden">
                <img :src="activeImage"
                     alt="{{ $produk->nama_produk }}"
                     class="absolute inset-0 w-full h-full object-cover transition duration-300">
            </div>

            <div class="grid grid-cols-5 gap-3 mt-3.5 shrink-0">
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
        <div class="min-w-0">
            <a href="{{ route('tenant.katalog', $produk->id_tenant) }}"
               class="inline-flex items-center gap-2.5 group mb-3.5">
                <div class="w-8 h-8 rounded-full bg-[#000000] text-[#ffffff] flex items-center justify-center shrink-0 overflow-hidden">
                    @if (!empty($tenant->url_logo))
                        <img src="{{ $tenant->url_logo }}" alt="{{ $namaToko }}" class="w-full h-full object-cover">
                    @else
                        <svg class="w-4 h-4 text-[#ffffff]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    @endif
                </div>
                <span class="text-[13px] font-semibold text-[#000000] group-hover:underline">
                    {{ $namaToko }}
                </span>
            </a>

            <h1 class="text-3xl sm:text-4xl font-bold text-[#000000] tracking-tight leading-tight">
                {{ $produk->nama_produk }}
            </h1>

            <div class="flex items-center gap-2 mt-2.5">
                <div class="flex items-center gap-0.5">
                    @for($s = 1; $s <= 5; $s++)
                        <svg class="w-[18px] h-[18px] {{ $s <= $filledStars ? 'fill-amber-400 text-amber-400' : 'fill-gray-200 text-gray-200' }}" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                    @endfor
                </div>
                <span class="text-xs text-black/50 font-medium">
                    @if ($productReviewCount > 0)
                        {{ $produk->formatted_rating }} &middot; {{ $productReviewCount }} ulasan
                    @else
                        Belum ada ulasan
                    @endif
                </span>
            </div>

            <p class="text-sm text-black/75 leading-relaxed mt-4">
                {{ $produk->deskripsi_lengkap ?? 'Peralatan produksi profesional terverifikasi inspeksi QC digital MirrorCam, siap digunakan untuk kebutuhan produksi komersial maupun sinematografi.' }}
            </p>

            <div class="mt-7">
                <span class="text-3xl sm:text-4xl font-bold text-[#000000]"
                      x-text="selectedDurationOption ? 'Rp ' + new Intl.NumberFormat('id-ID').format(totalHarga) + ' / ' + selectedDurationLabel : 'Harga tidak tersedia'"></span>
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
                                class="w-10 h-10 rounded-[4px] transition cursor-pointer"
                                :title="c.name">
                        </button>
                    </template>
                </div>
            </div>

            <!-- Pilihan Durasi Sewa -->
            <div class="mt-6">
                <div class="text-xs mb-2.5">
                    <span class="font-bold text-[#000000]">Booking</span>
                    <span class="text-black/45 ml-1">- <span x-text="selectedDurationLabel"></span></span>
                </div>
                <div class="grid grid-cols-3 sm:grid-cols-6 gap-2">
                    <template x-for="dur in durations" :key="dur.code">
                        <button type="button"
                                @click="dur.available && (selectedDuration = dur.code)"
                                :disabled="! dur.available"
                                :title="dur.available ? dur.label : 'Mitra tidak menyediakan durasi ini'"
                                :class="! dur.available
                                    ? 'bg-[#f9f9f9] text-black/30 border-black/10 cursor-not-allowed'
                                    : (selectedDuration === dur.code
                                        ? 'bg-[#000000] text-[#ffffff] border-[#000000]'
                                        : 'bg-[#ffffff] text-[#000000] border-black/25 hover:border-[#000000]')"
                                class="py-3 px-2 rounded-[4px] border text-[13px] font-medium text-center transition"
                                x-text="dur.label">
                        </button>
                    </template>
                </div>
                @if($durationOptions->where('available', true)->isEmpty())
                    <p class="text-[11px] text-red-600 mt-2">Mitra belum menyediakan opsi durasi sewa untuk produk ini.</p>
                @endif
                @error('durasi_kode')
                    <p class="text-[11px] text-red-600 mt-2">{{ $message }}</p>
                @enderror
            </div>

            @if ($tenant?->mode_libur)
                <div class="mt-5 rounded-[4px] border border-amber-300 bg-amber-50 px-4 py-3 text-xs font-semibold text-amber-800">
                    {{ $tenant->nama_toko }} sedang libur sementara. Booking baru belum dapat diproses.
                </div>
            @endif

            <!-- Tombol Booking Sekarang -->
            <form method="POST" action="{{ route('keranjang.add') }}" class="mt-7">
                @csrf
                <input type="hidden" name="id_produk" value="{{ $produk->id_produk }}">
                <input type="hidden" name="warna" :value="selectedColor">
                <input type="hidden" name="durasi_kode" :value="durasiKode">
                <input type="hidden" name="durasi_label" :value="durasiLabel">

                <button type="submit"
                        :disabled="! selectedDuration || {{ $tenant?->mode_libur ? 'true' : 'false' }}"
                        :class="! selectedDuration || {{ $tenant?->mode_libur ? 'true' : 'false' }}
                            ? 'bg-black/30 cursor-not-allowed'
                            : 'bg-[#000000] hover:bg-black/85 cursor-pointer'"
                        class="w-full py-4 px-6 rounded-[4px] text-[#ffffff] text-sm font-semibold inline-flex items-center justify-center gap-2.5 transition">
                    <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/>
                        <path d="M3 6h18"/>
                        <path d="M16 10a4 4 0 0 1-8 0"/>
                    </svg>
                    <span x-text="{{ $tenant?->mode_libur ? "'Toko Sedang Libur'" : "selectedDuration ? 'Booking Sekarang' : 'Durasi Tidak Tersedia'" }}"></span>
                </button>
            </form>
        </div>

    </div>
</div>
@endsection
