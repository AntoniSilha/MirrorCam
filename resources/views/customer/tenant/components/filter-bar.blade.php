<form method="GET" action="{{ url()->current() }}" class="bg-[#ffffff] border border-black/10 rounded-[4px] p-4 mb-6 font-['Montserrat']">
    <!-- Tab Kategori -->
    <div class="flex items-center gap-2 overflow-x-auto pb-3.5 border-b border-black/10">
        <a href="{{ url()->current() }}"
           class="inline-flex items-center gap-2 px-3.5 py-2 rounded-[4px] text-xs font-semibold whitespace-nowrap transition {{ !request('kategori') ? 'bg-[#000000] text-[#ffffff]' : 'bg-[#f9f9f9] text-[#000000] hover:bg-[#d9d9d9]' }}">
            Semua Alat
            <span class="px-1.5 py-0.5 rounded-[4px] text-[10px] {{ !request('kategori') ? 'bg-white/20 text-[#ffffff]' : 'bg-[#d9d9d9] text-[#000000]' }}">
                {{ $totalSemuaAlat }}
            </span>
        </a>

        @foreach($kategoriTabs as $tab)
            <a href="{{ request()->fullUrlWithQuery(['kategori' => $tab->id_kategori, 'page' => 1]) }}"
               class="inline-flex items-center gap-2 px-3.5 py-2 rounded-[4px] text-xs font-semibold whitespace-nowrap transition {{ request('kategori') == $tab->id_kategori ? 'bg-[#000000] text-[#ffffff]' : 'bg-[#f9f9f9] text-[#000000] hover:bg-[#d9d9d9]' }}">
                {{ $tab->nama_kategori }}
                <span class="px-1.5 py-0.5 rounded-[4px] text-[10px] {{ request('kategori') == $tab->id_kategori ? 'bg-white/20 text-[#ffffff]' : 'bg-[#d9d9d9] text-[#000000]' }}">
                    {{ $tab->total }}
                </span>
            </a>
        @endforeach
    </div>

    <!-- Bar Pencarian, Tanggal, Sort, & Siap Pakai -->
    <div class="flex flex-col md:flex-row items-stretch md:items-center gap-3 pt-3.5">
        @if(request('kategori'))
            <input type="hidden" name="kategori" value="{{ request('kategori') }}">
        @endif

        <div class="relative flex-1">
            <svg class="w-4 h-4 text-black/40 absolute left-3.5 top-1/2 -translate-y-1/2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Cari kamera, lensa, atau aksesoris di {{ $tenant->nama_toko ?? 'Indonesia Gallery' }}..."
                   class="w-full pl-10 pr-4 py-2 bg-[#f9f9f9] border border-black/15 rounded-[4px] text-xs text-[#000000] placeholder:text-black/40 focus:outline-none focus:border-[#000000]">
        </div>

        <div class="inline-flex items-center justify-between gap-2 px-3.5 py-2 border border-black/15 rounded-[4px] text-xs text-[#000000] bg-[#ffffff]">
            <span class="flex items-center gap-2 font-medium">
                <svg class="w-3.5 h-3.5 text-black/50" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
                18 Apr - 20 Apr 2025
            </span>
            <svg class="w-3.5 h-3.5 text-black/50" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
        </div>

        <div class="relative">
    {{-- ikon sort (kiri) --}}
    <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-black/60 pointer-events-none"
         viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M3 6h18"/><path d="M7 12h10"/><path d="M10 18h4"/>
    </svg>

    <select name="sort" onchange="this.form.submit()"
            class="appearance-none pl-9 pr-9 py-2 border border-black/15 rounded-[4px] text-xs font-medium text-[#000000] bg-[#ffffff] focus:outline-none focus:border-[#000000] cursor-pointer">
        <option value="terpopuler" {{ request('sort') == 'terpopuler' ? 'selected' : '' }}>Terpopuler</option>
        <option value="termurah" {{ request('sort') == 'termurah' ? 'selected' : '' }}>Harga Terendah</option>
        <option value="termahal" {{ request('sort') == 'termahal' ? 'selected' : '' }}>Harga Tertinggi</option>
    </select>

    {{-- chevron-down (kanan) --}}
    <svg class="w-4 h-4 absolute right-3 top-1/2 -translate-y-1/2 text-black/60 pointer-events-none"
         viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="m6 9 6 6 6-6"/>
    </svg>
</div>

        <label class="inline-flex items-center gap-2 px-3 py-2 text-xs font-semibold text-[#000000] cursor-pointer select-none">
            <input type="checkbox" name="siap_pakai" value="1" onchange="this.form.submit()"
                   {{ request('siap_pakai') ? 'checked' : '' }}
                   class="w-4 h-4 rounded-[4px] border-black/30 accent-[#000000]">
            Siap Pakai
        </label>
    </div>
</form>
