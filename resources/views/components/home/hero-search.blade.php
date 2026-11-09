@props([
    'tenants' => [],
    'categories' => [],
    'searchParams' => [],
])

<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 pb-8" x-data="{ currentTab: '{{ $searchParams['type'] ?? 'camera' }}' }">
    <!-- Hero Banner Card -->
    <div class="bg-[#ffffff] border border-[#e5e5e5] rounded-[4px] p-6 sm:p-10 lg:p-12 relative overflow-hidden flex flex-col md:flex-row items-center justify-between gap-8 shadow-xs">
        <!-- Left Content -->
        <div class="max-w-xl z-10 space-y-4">
            <h1 class="text-3xl sm:text-4xl lg:text-[42px] font-extrabold text-[#000000] tracking-tight leading-tight">
                Yuk, abadikan momen<br>
                dengan <span class="underline decoration-2 underline-offset-4">MirrorCam</span>
            </h1>
            <p class="text-sm sm:text-base text-gray-600 font-medium leading-relaxed">
                Jelajahi koleksi kamera & lensa B&W kami dari mitra tenant terverifikasi se-Indonesia dalam satu keranjang terpadu.
            </p>
            <div class="pt-2">
                <a
                    href="#katalog"
                    class="inline-flex items-center justify-center px-6 py-2.5 bg-[#d9d9d9] hover:bg-[#c4c4c4] text-[#000000] text-sm font-bold rounded-[4px] border border-[#bfbfbf] transition shadow-xs"
                >
                    Cek Sekarang
                </a>
            </div>
        </div>

        <!-- Right Visual / Camera Gear Illustration -->
        <div class="relative shrink-0 w-full md:w-[480px] flex justify-center items-center">
            <svg class="w-full max-w-[420px] h-auto drop-shadow-xs" viewBox="0 0 520 280" fill="none" xmlns="http://www.w3.org/2000/svg">
                <!-- Camera Body -->
                <rect x="220" y="80" width="220" height="150" rx="16" fill="#1f2937" stroke="#111827" stroke-width="4"/>
                <rect x="235" y="95" width="60" height="20" rx="4" fill="#374151"/>
                <circle x="400" y="105" r="14" fill="#ef4444"/>
                <circle x="330" y="155" r="55" fill="#111827" stroke="#4b5563" stroke-width="6"/>
                <circle x="330" y="155" r="40" fill="#1f2937" stroke="#3b82f6" stroke-width="3"/>
                <circle x="330" y="155" r="25" fill="#0f172a"/>
                <circle x="338" y="148" r="8" fill="#ffffff" fill-opacity="0.4"/>
                <!-- Flash Hotshoe -->
                <rect x="305" y="62" width="50" height="18" rx="3" fill="#374151" stroke="#111827" stroke-width="3"/>
                <!-- Cinema Lens next to body -->
                <rect x="70" y="110" width="120" height="120" rx="8" fill="#111827" stroke="#374151" stroke-width="4"/>
                <line x1="90" y1="110" x2="90" y2="230" stroke="#4b5563" stroke-width="3"/>
                <line x1="110" y1="110" x2="110" y2="230" stroke="#4b5563" stroke-width="3"/>
                <line x1="150" y1="110" x2="150" y2="230" stroke="#eab308" stroke-width="2"/>
                <circle x="70" y="170" r="30" fill="#1f2937" stroke="#6b7280" stroke-width="3"/>
                <!-- Studio Light & Stand -->
                <line x1="480" y1="50" x2="480" y2="250" stroke="#111827" stroke-width="5" stroke-linecap="round"/>
                <polygon points="440,50 490,20 510,70 460,90" fill="#374151" stroke="#111827" stroke-width="3"/>
                <line x1="450" y1="230" x2="480" y2="250" stroke="#111827" stroke-width="4"/>
                <line x1="510" y1="230" x2="480" y2="250" stroke="#111827" stroke-width="4"/>
                <!-- Gear Bag / Backpack -->
                <rect x="130" y="140" width="100" height="90" rx="12" fill="#4b5563" stroke="#1f2937" stroke-width="4"/>
                <path d="M 150 140 Q 180 110 210 140" fill="none" stroke="#1f2937" stroke-width="4"/>
                <rect x="145" y="165" width="70" height="40" rx="6" fill="#374151"/>
            </svg>
        </div>
    </div>

    <!-- Floating Interactive Filter Search Card -->
    <div class="mt-4 bg-[#ffffff] border border-[#e5e5e5] rounded-[4px] p-4 sm:p-6 shadow-sm">
        <form action="{{ route('home') }}" method="GET" class="space-y-4">
            <!-- Tabs & Info Row -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 border-b border-[#e5e5e5] pb-3">
                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        @click="currentTab = 'camera'"
                        :class="currentTab === 'camera' ? 'bg-[#000000] text-white font-bold' : 'bg-[#f0f0f0] text-gray-700 font-semibold hover:bg-[#e4e4e4]'"
                        class="px-4 py-2 text-xs sm:text-sm rounded-[4px] transition flex items-center gap-2"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                        </svg>
                        <span>Sewa Peralatan Kamera</span>
                    </button>
                    <button
                        type="button"
                        @click="currentTab = 'studio'"
                        :class="currentTab === 'studio' ? 'bg-[#000000] text-white font-bold' : 'bg-[#f0f0f0] text-gray-700 font-semibold hover:bg-[#e4e4e4]'"
                        class="px-4 py-2 text-xs sm:text-sm rounded-[4px] transition flex items-center gap-2"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        <span>Sewa Studio & Ruang Syuting</span>
                    </button>
                    <input type="hidden" name="type" :value="currentTab" />
                </div>

                <div class="flex items-center gap-1.5 text-xs text-gray-500 font-medium">
                    <svg class="w-4 h-4 text-[#B45309]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Bebas atur tanggal sewa lintas tenant</span>
                </div>
            </div>

            <!-- Filter Fields Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 pt-1">
                <!-- Field 1: Pilih Tenant / Lokasi -->
                <div class="space-y-1">
                    <label class="block text-[11px] font-bold text-gray-700 uppercase tracking-wider">Pilih Tenant / Lokasi</label>
                    <div class="relative">
                        <select
                            name="tenant_id"
                            class="w-full pl-8 pr-3 py-2 text-xs bg-[#f9f9f9] border border-[#e5e5e5] rounded-[4px] text-[#000000] focus:ring-1 focus:ring-[#000000] focus:border-[#000000] transition"
                        >
                            <option value="all">Semua Tenant & Wilayah</option>
                            @foreach ($tenants as $tenant)
                                <option value="{{ $tenant['id'] }}" {{ ($searchParams['tenant_id'] ?? '') == $tenant['id'] ? 'selected' : '' }}>
                                    {{ $tenant['name'] }} ({{ $tenant['location'] }})
                                </option>
                            @endforeach
                        </select>
                        <svg class="w-4 h-4 text-gray-500 absolute left-2.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                </div>

                <!-- Field 2: Cari Peralatan / Gear -->
                <div class="space-y-1">
                    <label class="block text-[11px] font-bold text-gray-700 uppercase tracking-wider">Cari Peralatan / Gear</label>
                    <div class="relative">
                        <input
                            type="text"
                            name="keyword"
                            value="{{ $searchParams['keyword'] ?? '' }}"
                            placeholder="Kamera, Lensa, Rigging..."
                            class="w-full pl-8 pr-3 py-2 text-xs bg-[#f9f9f9] border border-[#e5e5e5] rounded-[4px] text-[#000000] placeholder-gray-400 focus:ring-1 focus:ring-[#000000] focus:border-[#000000] transition"
                        />
                        <svg class="w-4 h-4 text-gray-500 absolute left-2.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </div>

                <!-- Field 3: Tanggal Sewa Ambil -->
                <div class="space-y-1">
                    <label class="block text-[11px] font-bold text-gray-700 uppercase tracking-wider">Tanggal Ambil</label>
                    <div class="relative">
                        <input
                            type="date"
                            name="start_date"
                            value="{{ $searchParams['start_date'] ?? '' }}"
                            class="w-full pl-8 pr-3 py-2 text-xs bg-[#f9f9f9] border border-[#e5e5e5] rounded-[4px] text-[#000000] focus:ring-1 focus:ring-[#000000] focus:border-[#000000] transition"
                        />
                        <svg class="w-4 h-4 text-gray-500 absolute left-2.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                </div>

                <!-- Field 4: Tanggal Sewa Kembali -->
                <div class="space-y-1">
                    <label class="block text-[11px] font-bold text-gray-700 uppercase tracking-wider">Tanggal Kembali</label>
                    <div class="relative">
                        <input
                            type="date"
                            name="end_date"
                            value="{{ $searchParams['end_date'] ?? '' }}"
                            class="w-full pl-8 pr-3 py-2 text-xs bg-[#f9f9f9] border border-[#e5e5e5] rounded-[4px] text-[#000000] focus:ring-1 focus:ring-[#000000] focus:border-[#000000] transition"
                        />
                        <svg class="w-4 h-4 text-gray-500 absolute left-2.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                </div>

                <!-- Action Button: Cari Peralatan -->
                <div class="space-y-1 flex flex-col justify-end">
                    <label class="hidden lg:block text-[11px] font-bold text-transparent select-none">Action</label>
                    <button
                        type="submit"
                        class="w-full py-2 px-4 bg-[#000000] hover:bg-[#222222] text-[#ffffff] text-xs sm:text-sm font-bold rounded-[4px] flex items-center justify-center gap-2 shadow-xs transition cursor-pointer"
                    >
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <span>Cari Peralatan</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</section>
