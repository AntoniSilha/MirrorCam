@props([
    'activeFilter' => 'all',
    'counts' => ['all' => 3, 'renting' => 1, 'completed' => 1],
    'activeSort' => 'terbaru',
])

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <!-- Filter Tabs -->
    <div class="flex items-center gap-2 overflow-x-auto pb-1 sm:pb-0">
        <!-- Semua Pesanan -->
        <a href="{{ route('dashboard', ['filter' => 'all', 'sort' => $activeSort]) }}"
           class="inline-flex items-center px-4 py-1.5 text-xs font-semibold rounded-full transition-all duration-150 {{ in_array($activeFilter, ['all', 'semua', '']) ? 'bg-[#000000] text-[#ffffff] shadow-xs' : 'text-neutral-500 hover:text-black hover:bg-neutral-200/50' }}">
            Semua Pesanan ({{ $counts['all'] ?? 0 }})
        </a>

        <!-- Sedang Disewa -->
        <a href="{{ route('dashboard', ['filter' => 'sedang-disewa', 'sort' => $activeSort]) }}"
           class="inline-flex items-center px-4 py-1.5 text-xs font-semibold rounded-full transition-all duration-150 {{ in_array($activeFilter, ['sedang-disewa', 'renting']) ? 'bg-[#000000] text-[#ffffff] shadow-xs' : 'text-neutral-500 hover:text-black hover:bg-neutral-200/50' }}">
            Sedang Disewa ({{ $counts['renting'] ?? 0 }})
        </a>

        <!-- Selesai -->
        <a href="{{ route('dashboard', ['filter' => 'selesai', 'sort' => $activeSort]) }}"
           class="inline-flex items-center px-4 py-1.5 text-xs font-semibold rounded-full transition-all duration-150 {{ in_array($activeFilter, ['selesai', 'completed']) ? 'bg-[#000000] text-[#ffffff] shadow-xs' : 'text-neutral-500 hover:text-black hover:bg-neutral-200/50' }}">
            Selesai ({{ $counts['completed'] ?? 0 }})
        </a>
    </div>

    <!-- Sort Dropdown -->
    <div class="flex items-center gap-1.5 text-xs font-medium text-neutral-600 self-end sm:self-auto">
        <label for="sort-select" class="text-neutral-500 whitespace-nowrap">Urutan:</label>
        <div class="relative">
            <select id="sort-select"
                    onchange="window.location.href = this.value"
                    class="appearance-none bg-transparent pr-6 pl-1 py-1 font-bold text-black text-xs cursor-pointer focus:outline-hidden">
                <option value="{{ route('dashboard', ['filter' => $activeFilter, 'sort' => 'terbaru']) }}" {{ $activeSort === 'terbaru' ? 'selected' : '' }}>
                    Terbaru
                </option>
                <option value="{{ route('dashboard', ['filter' => $activeFilter, 'sort' => 'terlama']) }}" {{ $activeSort === 'terlama' ? 'selected' : '' }}>
                    Terlama
                </option>
            </select>
            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center text-neutral-500">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                </svg>
            </div>
        </div>
    </div>
</div>
