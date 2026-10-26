@props(['categories' => []])

<section id="kategori" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-5">
        <span class="text-[11px] font-bold text-gray-500 uppercase tracking-widest block mb-1">Eksplorasi Kategori</span>
        <h2 class="text-xl sm:text-2xl font-bold text-[#000000] tracking-tight">Kategori Peralatan Populer</h2>
    </div>

    <!-- Category Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 sm:gap-4">
        @foreach ($categories as $category)
            <a
                href="{{ route('home', ['category_id' => $category['id']]) }}#katalog"
                class="bg-[#ffffff] border border-[#e5e5e5] hover:border-[#000000] rounded-[4px] p-4 flex flex-col items-center justify-center text-center transition group hover:shadow-xs"
            >
                <!-- Icon container -->
                <div class="w-12 h-12 rounded-[4px] bg-[#f9f9f9] group-hover:bg-[#000000] text-[#000000] group-hover:text-white flex items-center justify-center mb-3 transition">
                    @if (str_contains(strtolower($category['icon']), 'lens') || str_contains(strtolower($category['name']), 'lensa'))
                        <!-- Aperture / Lens Icon -->
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10" stroke-width="2"/>
                            <line x1="14.31" y1="8" x2="20.05" y2="17.94" stroke-width="2"/>
                            <line x1="9.69" y1="8" x2="21.17" y2="8" stroke-width="2"/>
                            <line x1="7.38" y1="12" x2="13.12" y2="2.06" stroke-width="2"/>
                            <line x1="9.69" y1="16" x2="3.95" y2="6.06" stroke-width="2"/>
                            <line x1="14.31" y1="16" x2="2.83" y2="16" stroke-width="2"/>
                            <line x1="16.62" y1="12" x2="10.88" y2="21.94" stroke-width="2"/>
                        </svg>
                    @elseif (str_contains(strtolower($category['icon']), 'light') || str_contains(strtolower($category['name']), 'lighting'))
                        <!-- Light / Flash Icon -->
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    @elseif (str_contains(strtolower($category['icon']), 'audio') || str_contains(strtolower($category['name']), 'audio'))
                        <!-- Audio / Mic Icon -->
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/>
                        </svg>
                    @elseif (str_contains(strtolower($category['icon']), 'drone') || str_contains(strtolower($category['name']), 'drone') || str_contains(strtolower($category['name']), 'gimbal'))
                        <!-- Drone / Gimbal Icon -->
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="3" stroke-width="2"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9l4 4M21 9l-4 4M3 15l4-4M21 15l-4-4"/>
                            <circle cx="5" cy="7" r="2" stroke-width="2"/>
                            <circle cx="19" cy="7" r="2" stroke-width="2"/>
                            <circle cx="5" cy="17" r="2" stroke-width="2"/>
                            <circle cx="19" cy="17" r="2" stroke-width="2"/>
                        </svg>
                    @elseif (str_contains(strtolower($category['icon']), 'studio') || str_contains(strtolower($category['name']), 'studio'))
                        <!-- Studio Icon -->
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    @else
                        <!-- Camera Cinema Icon -->
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    @endif
                </div>

                <!-- Title -->
                <span class="text-xs font-bold text-[#000000] leading-tight line-clamp-2">
                    {{ $category['name'] }}
                </span>
            </a>
        @endforeach
    </div>
</section>
