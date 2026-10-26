@props(['tenants' => []])

<section id="mitra-tenant" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="mb-6">
        <h2 class="text-xl sm:text-2xl font-bold text-[#000000] tracking-tight">Mitra Resmi Tenant MirrorCam</h2>
        <p class="text-xs sm:text-sm text-gray-600 mt-1">Eksplor mitra persewaan terpercaya kami dengan jaminan gear original dan tersertifikasi MirrorCam.</p>
    </div>

    <!-- Tenant Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @foreach ($tenants as $tenant)
            <div class="bg-[#ffffff] border border-[#e5e5e5] rounded-[4px] p-6 flex flex-col justify-between shadow-xs hover:border-[#000000] transition group">
                <div>
                    <!-- Tenant Top Header -->
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-11 h-11 rounded-full bg-[#000000] text-white flex items-center justify-center text-sm font-extrabold shrink-0 shadow-xs">
                                {{ $tenant['initials'] ?? 'TN' }}
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-[#000000] group-hover:underline">{{ $tenant['name'] }}</h3>
                                <p class="text-[11px] text-gray-500 font-medium">{{ $tenant['subtitle'] ?? 'Rental & Service' }}</p>
                            </div>
                        </div>

                        @if (!empty($tenant['badge']))
                            <span class="inline-block px-2 py-0.5 bg-[#f0f0f0] border border-[#e0e0e0] text-[#000000] text-[10px] font-bold tracking-wider uppercase rounded-[4px]">
                                {{ $tenant['badge'] }}
                            </span>
                        @endif
                    </div>

                    <!-- Bio / Deskripsi -->
                    <p class="text-xs text-gray-600 leading-relaxed mt-4 line-clamp-3">
                        {{ $tenant['bio'] }}
                    </p>
                </div>

                <div class="mt-6 pt-4 border-t border-[#e5e5e5] space-y-4">
                    <!-- Stats Bar: Rating, Unit Ready, Lokasi -->
                    <div class="grid grid-cols-3 text-center divide-x divide-[#e5e5e5]">
                        <div class="px-2">
                            <span class="block text-sm font-extrabold text-[#000000] flex items-center justify-center gap-1">
                                <svg class="w-3.5 h-3.5 text-amber-500 fill-amber-500" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                                <span>{{ $tenant['rating'] }}</span>
                            </span>
                            <span class="block text-[10px] text-gray-500 mt-0.5">Nilai Rating</span>
                        </div>
                        <div class="px-2">
                            <span class="block text-sm font-extrabold text-[#000000]">{{ $tenant['unit_ready'] }}</span>
                            <span class="block text-[10px] text-gray-500 mt-0.5">Unit Ready</span>
                        </div>
                        <div class="px-2">
                            <span class="block text-sm font-extrabold text-[#000000] truncate">{{ $tenant['location'] }}</span>
                            <span class="block text-[10px] text-gray-500 mt-0.5">Kota / Area</span>
                        </div>
                    </div>

                    <!-- Visit Tenant Button -->
                    <a
                        href="{{ route('home', ['tenant_id' => $tenant['id']]) }}#katalog"
                        class="w-full py-2 bg-[#000000] hover:bg-[#222222] text-[#ffffff] text-xs font-bold rounded-[4px] flex items-center justify-center gap-1.5 transition shadow-xs"
                    >
                        <span>Kunjungi Tenant</span>
                        <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </a>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Bottom Outlined Action Button -->
    <div class="mt-8 text-center">
        <a
            href="{{ route('home') }}#mitra-tenant"
            class="inline-flex items-center gap-2 px-6 py-2.5 bg-[#ffffff] hover:bg-[#f2f2f2] text-[#000000] text-xs font-bold rounded-[4px] border border-[#000000] transition shadow-xs"
        >
            <span>Lihat Semua Mitra Tenant Rekanan</span>
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
            </svg>
        </a>
    </div>
</section>
