@props(['tenant'])

<article class="bg-[#ffffff] rounded-[4px] border border-[#e5e5e5] p-5 sm:p-6 flex flex-col justify-between hover:shadow-sm transition group">
    <div>
        <!-- 1. Header Kartu: Logo/Inisial, Nama & Subtitle, Badge Partner -->
        <div class="flex items-start justify-between gap-3">
            <div class="flex items-center gap-3 min-w-0">
                <!-- Kotak inisial 2 huruf atau Logo -->
                <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-[4px] bg-[#000000] text-[#ffffff] flex items-center justify-center font-bold text-sm sm:text-base shrink-0 overflow-hidden shadow-xs">
                    @if (!empty($tenant->url_logo))
                        <img src="{{ $tenant->url_logo }}" alt="{{ $tenant->nama_toko }}" class="w-full h-full object-cover">
                    @else
                        <span>{{ $tenant->initials }}</span>
                    @endif
                </div>

                <div class="flex flex-col min-w-0">
                    <h3 class="text-sm sm:text-base font-bold text-[#000000] truncate leading-tight group-hover:text-black">
                        {{ $tenant->nama_toko }}
                    </h3>
                    <span class="text-[11px] text-gray-500 font-medium leading-tight mt-1 truncate">
                        {{ $tenant->service_category }}
                    </span>
                </div>
            </div>

            @if ($tenant->is_official)
                <span class="bg-[#f0f0f0] text-[#000000] text-[9px] sm:text-[10px] font-bold tracking-wider px-2 py-1 rounded-[4px] uppercase shrink-0 whitespace-nowrap">
                    OFFICIAL PARTNER
                </span>
            @else
                <span class="bg-amber-50 text-amber-800 text-[9px] sm:text-[10px] font-bold tracking-wider px-2 py-1 rounded-[4px] uppercase shrink-0 whitespace-nowrap">
                    BELUM TERVERIFIKASI
                </span>
            @endif
        </div>

        <!-- 2. Deskripsi Singkat Tenant (maksimal 3-4 baris agar tinggi kartu seragam) -->
        <p class="text-xs text-gray-500 leading-relaxed mt-4 mb-5 line-clamp-3 sm:line-clamp-4 min-h-[3.6rem]">
            {{ $tenant->bio_deskripsi ?: 'Penyedia peralatan multimedia dan kamera bioskop dengan pemeliharaan perangkat terstandarisasi.' }}
        </p>

        <!-- 3. Kotak 3 Metrik Ringkasan (Rating, Unit Siap Pakai, Lokasi) -->
        <div class="bg-[#f9f9f9] rounded-[4px] py-3 px-2 grid grid-cols-3 divide-x divide-gray-200 text-center mb-5">
            <!-- Kolom 1: Rating -->
            <div class="px-1 flex flex-col items-center justify-center">
                <span class="text-sm sm:text-base font-bold text-[#000000] leading-none">
                    {{ $tenant->formatted_rating }}
                </span>
                <span class="text-[10px] text-gray-500 font-medium leading-tight mt-1 truncate max-w-full">
                    {{ $tenant->review_count_text }}
                </span>
            </div>

            <!-- Kolom 2: Ketersediaan Unit -->
            <div class="px-1 flex flex-col items-center justify-center">
                <span class="text-sm sm:text-base font-bold text-[#000000] leading-none">
                    {{ $tenant->unit_siap_pakai }}
                </span>
                <span class="text-[10px] text-gray-500 font-medium leading-tight mt-1 truncate max-w-full">
                    Unit Siap Pakai
                </span>
            </div>

            <!-- Kolom 3: Lokasi -->
            <div class="px-1 flex flex-col items-center justify-center">
                <span class="text-sm sm:text-base font-bold text-[#000000] leading-none truncate max-w-full" title="{{ $tenant->kota }}">
                    {{ $tenant->kota }}
                </span>
                <span class="text-[10px] text-gray-500 font-medium leading-tight mt-1">
                    Lokasi
                </span>
            </div>
        </div>
    </div>

    <!-- 4. Tombol CTA Bawah: Kunjungi Tenant → -->
    <a href="{{ route('tenant.show', $tenant->slug) }}" class="w-full bg-[#000000] text-[#ffffff] text-xs font-semibold py-2.5 sm:py-3 rounded-[4px] hover:bg-neutral-800 transition flex items-center justify-center gap-1.5 shadow-xs">
        <span>Kunjungi Tenant</span>
        <span aria-hidden="true">&rarr;</span>
    </a>
</article>
