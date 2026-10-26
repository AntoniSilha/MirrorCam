@props([
    'featuredProducts' => [],
    'hasSearch' => false,
])

<section id="katalog" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <!-- Section Header -->
    <div class="mb-4">
        <span class="text-[11px] font-bold text-gray-500 uppercase tracking-widest block mb-1">Pilihan Terbaik Minggu Ini</span>
        <h2 class="text-xl sm:text-2xl font-bold text-[#000000] tracking-tight">Rekomendasi Gear Terverifikasi</h2>

        <!-- Inspection Guarantee Info Callout -->
        <div class="mt-3 flex items-center gap-2.5 bg-[#ffffff] border border-[#e5e5e5] rounded-[4px] px-3.5 py-2.5 text-xs text-gray-700 shadow-2xs">
            <svg class="w-4 h-4 text-[#16a34a] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
            </svg>
            <span class="font-medium">Gear telah lolos inspeksi fisik & sensor sebelum disewakan kepada penyewa.</span>
        </div>

        @if ($hasSearch)
            <div class="mt-3 flex items-center justify-between bg-[#fffbeb] border border-[#fde68a] rounded-[4px] px-3.5 py-2 text-xs text-[#B45309]">
                <span>Menampilkan hasil pencarian khusus.</span>
                <a href="{{ route('home') }}#katalog" class="font-bold underline ml-2">Reset Filter</a>
            </div>
        @endif
    </div>

    <!-- Product Grid (4 columns on lg) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mt-6">
        @forelse ($featuredProducts as $product)
            <x-home.product-card :product="$product" />
        @empty
            <div class="col-span-full py-12 text-center bg-[#ffffff] border border-[#e5e5e5] rounded-[4px] p-8">
                <svg class="w-12 h-12 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <h3 class="text-sm font-bold text-[#000000]">Tidak ada gear yang cocok dengan pencarian</h3>
                <p class="text-xs text-gray-500 mt-1">Coba gunakan kata kunci lain atau reset pilihan filter tanggal dan tenant.</p>
                <div class="mt-4">
                    <a href="{{ route('home') }}#katalog" class="inline-block px-4 py-2 bg-[#000000] text-white text-xs font-bold rounded-[4px]">
                        Tampilkan Semua Gear
                    </a>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Bottom Outlined Action Button -->
    <div class="mt-8 text-center">
        <a
            href="{{ route('home') }}#katalog"
            class="inline-flex items-center gap-2 px-6 py-2.5 bg-[#ffffff] hover:bg-[#f2f2f2] text-[#000000] text-xs font-bold rounded-[4px] border border-[#000000] transition shadow-xs"
        >
            <span>Tampilkan Lebih Banyak Rekomendasi Gear</span>
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
            </svg>
        </a>
    </div>
</section>
