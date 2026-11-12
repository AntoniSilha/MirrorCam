@props([
    'activeRating' => 'all',
    'searchKeyword' => '',
])

@php
    $ratings = [
        ['value' => 'all', 'label' => 'All'],
        ['value' => '5.0', 'label' => 'Rating 5.0'],
        ['value' => '4.0', 'label' => 'Rating 4.0'],
        ['value' => '3.0', 'label' => 'Rating 3.0'],
        ['value' => '2.0', 'label' => 'Rating 2.0'],
        ['value' => '1.0', 'label' => 'Rating 1.0'],
    ];

    $currentRating = strtolower($activeRating ?: 'all');
@endphp

<div class="space-y-5">
    <!-- Baris Atas: Judul & Subjudul (Kiri) dan Search Bar (Kanan) -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-[#000000] tracking-tight">
                Eksplorasi Tenant
            </h1>
            <p class="text-xs sm:text-sm text-gray-500 mt-1 font-medium">
                Seluruh tenant melewati uji kelayakan hardware ketat dan garansi operasional 100%.
            </p>
        </div>

        <!-- Search Bar -->
        <div class="w-full md:w-auto">
            <form method="GET" action="{{ route('tenant.index') }}" class="relative w-full md:w-72">
                @if ($currentRating !== 'all')
                    <input type="hidden" name="rating" value="{{ $currentRating }}">
                @endif

                <div class="relative flex items-center">
                    <input
                        type="text"
                        name="search"
                        value="{{ $searchKeyword }}"
                        placeholder="Search"
                        class="w-full bg-[#ffffff] text-xs text-[#000000] placeholder-gray-400 pl-3.5 pr-9 py-2 border border-gray-300 rounded-[4px] focus:outline-hidden focus:border-black transition"
                        aria-label="Cari nama tenant, spesialisasi, atau lokasi"
                    >
                    <button type="submit" class="absolute right-2 text-gray-400 hover:text-black transition" aria-label="Lakukan pencarian">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Baris Filter Rating Pills -->
    <div class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-none">
        @foreach ($ratings as $item)
            @php
                $isActive = ($currentRating === $item['value']);
                $queryParams = [];
                if (!empty($searchKeyword)) {
                    $queryParams['search'] = $searchKeyword;
                }
                if ($item['value'] !== 'all') {
                    $queryParams['rating'] = $item['value'];
                }
            @endphp

            <a
                href="{{ route('tenant.index', $queryParams) }}"
                class="px-4 py-1.5 rounded-[4px] text-xs font-semibold whitespace-nowrap transition {{ $isActive ? 'bg-[#000000] text-[#ffffff] shadow-xs' : 'bg-[#ffffff] text-[#000000] border border-gray-300 hover:bg-gray-50' }}"
            >
                {{ $item['label'] }}
            </a>
        @endforeach
    </div>
</div>
