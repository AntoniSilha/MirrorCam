@php
    $isStudio = str_contains(strtolower($produk->nama_produk), 'studio');
    $namaToko = $tenant->nama_toko ?? 'MirrorCam Tenant';
    $tenantId = $tenant->id_tenant ?? $produk->id_tenant;
@endphp

<div class="bg-[#ffffff] border border-black/10 rounded-[4px] overflow-hidden flex flex-col hover:border-[#000000] transition font-['Montserrat']">
    <!-- Thumbnail Gambar & Badge Status -->
    <div class="relative aspect-4/3 bg-[#f9f9f9] overflow-hidden">
        <img src="{{ $produk->primary_image }}"
             alt="{{ $produk->nama_produk }}"
             class="w-full h-full object-cover hover:scale-105 transition duration-300">

        <div class="absolute top-2.5 left-2.5">
            @if(($produk->total_stock_unit ?? 0) > 2)
                <span class="px-2 py-0.5 rounded-[4px] bg-[#dcfce7] text-[#16a34a] text-[10px] font-bold">
                    Aktif • {{ $produk->total_stock_unit }} Unit
                </span>
            @elseif(($produk->total_stock_unit ?? 0) > 0)
                <span class="px-2 py-0.5 rounded-[4px] bg-[#fff700] text-[#000000] text-[10px] font-bold">
                    Sisa {{ $produk->total_stock_unit }} Unit
                </span>
            @else
                <span class="px-2 py-0.5 rounded-[4px] bg-[#ffdad6] text-[#ba1a1a] text-[10px] font-bold">
                    Stok Habis
                </span>
            @endif
        </div>
    </div>

    <!-- Detail Informasi Produk -->
    <div class="p-4 flex-1 flex flex-col justify-between">
        <div>
            <!-- Link ke Halaman Profil & Katalog Tenant -->
            <a href="{{ route('tenant.katalog', $tenantId) }}"
               class="inline-flex items-center gap-1.5 text-[11px] text-black/50 hover:text-[#000000] hover:underline mb-1">
                <svg class="w-3 h-3 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m2 7 4.41-4.41A2 2 0 0 1 7.83 2h8.34a2 2 0 0 1 1.42.59L22 7"/><path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"/><path d="M15 22v-4a2 2 0 0 0-2-2h-2a2 2 0 0 0-2 2v4"/><path d="M2 7h20"/></svg>
                <span class="line-clamp-1">{{ $namaToko }}</span>
            </a>

            <!-- Nama Alat -->
            <h3 class="text-sm font-bold text-[#000000] line-clamp-1" title="{{ $produk->nama_produk }}">
                {{ $produk->nama_produk }}
            </h3>

            <!-- Rating & Frekuensi Sewa -->
            <div class="flex items-center gap-1 text-[11px] text-black/60 mt-1">
                <svg class="w-3 h-3 fill-[#000000] text-[#000000] shrink-0" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                <span class="font-semibold text-[#000000]">4.9</span>
                <span>({{ 40 + ($produk->id_produk * 17) }} kali disewa)</span>
            </div>

            @if(($produk->total_stock_unit ?? 0) <= 2 && ($produk->total_stock_unit ?? 0) > 0)
                <p class="text-[10px] font-semibold text-[#B45309] mt-1.5 flex items-center gap-1">
                    <svg class="w-3 h-3 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    Unit lain kembali pukul 18:00 WIB
                </p>
            @endif
        </div>

        <!-- Harga & Tombol Aksi -->
        <div class="mt-4 pt-3 border-t border-black/10">
            <p class="text-[10px] text-black/50 font-medium">
                {{ $isStudio ? 'Tarif Reservasi' : 'Tarif Sewa' }}
            </p>
            <div class="flex items-baseline gap-1 mt-0.5">
                <span class="text-base font-bold text-[#000000]">
                    Rp {{ number_format($produk->harga_sewa, 0, ',', '.') }}
                </span>
                <span class="text-[11px] text-black/50">
                    {{ $isStudio ? '/ sesi (2 Jam)' : '/ hari' }}
                </span>
            </div>

            <div class="grid grid-cols-2 gap-2 mt-3.5">
    <button type="button"
            onclick='openDetailModal(@json($produk))'
            class="py-2.5 px-3 rounded-[4px] bg-[#f9f9f9] hover:bg-[#d9d9d9]/60 border border-black/10 text-[#000000] text-xs font-medium text-center whitespace-nowrap transition">
        {{ $isStudio ? 'Fasilitas Suite' : 'Detail Spek' }}
    </button>

    @if(($produk->total_stock_unit ?? 0) > 0)
        <a href="{{ route('produk.show', $produk->id_produk) }}"
           class="py-2.5 px-3 rounded-[4px] bg-[#000000] hover:bg-black/85 text-[#ffffff] text-xs font-medium inline-flex items-center justify-center gap-1.5 whitespace-nowrap transition">
            <!-- Ikon Keranjang dengan Tanda Plus -->
            <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="8" cy="21" r="1"/>
                <circle cx="19" cy="21" r="1"/>
                <path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/>
                <path d="M13 5v6"/>
                <path d="M10 8h6"/>
            </svg>
            <span>Sewa</span>
        </a>
    @else
        <button type="button" disabled
                class="py-2.5 px-3 rounded-[4px] bg-[#d9d9d9] text-black/40 cursor-not-allowed text-xs font-medium inline-flex items-center justify-center gap-1.5 whitespace-nowrap">
            <span>Sewa</span>
        </button>
    @endif
</div>
        </div>
    </div>
</div>
