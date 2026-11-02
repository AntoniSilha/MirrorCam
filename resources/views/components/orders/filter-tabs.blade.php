@props([
    'activeFilter' => 'semua',
    'counts' => ['semua' => 0, 'sedang_disewa' => 0, 'selesai' => 0],
    'activeSort' => 'terbaru',
])

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <!-- Filter Tabs -->
    <div class="flex items-center gap-2 overflow-x-auto pb-1 sm:pb-0">
        <!-- Semua Pesanan -->
        <button type="button"
                wire:click="setFilter('semua')"
                class="inline-flex items-center px-4 py-1.5 text-xs font-semibold rounded-full transition-all duration-150 {{ $activeFilter === 'semua' ? 'bg-[#000000] text-[#ffffff] shadow-xs' : 'text-neutral-500 hover:text-black hover:bg-neutral-200/50' }}">
            Semua Pesanan ({{ $counts['semua'] ?? 0 }})
        </button>

        <!-- Sedang Disewa -->
        <button type="button"
                wire:click="setFilter('sedang_disewa')"
                class="inline-flex items-center px-4 py-1.5 text-xs font-semibold rounded-full transition-all duration-150 {{ $activeFilter === 'sedang_disewa' ? 'bg-[#000000] text-[#ffffff] shadow-xs' : 'text-neutral-500 hover:text-black hover:bg-neutral-200/50' }}">
            Sedang Disewa ({{ $counts['sedang_disewa'] ?? 0 }})
        </button>

        <!-- Selesai -->
        <button type="button"
                wire:click="setFilter('selesai')"
                class="inline-flex items-center px-4 py-1.5 text-xs font-semibold rounded-full transition-all duration-150 {{ $activeFilter === 'selesai' ? 'bg-[#000000] text-[#ffffff] shadow-xs' : 'text-neutral-500 hover:text-black hover:bg-neutral-200/50' }}">
            Selesai ({{ $counts['selesai'] ?? 0 }})
        </button>
    </div>

    <!-- Sort Dropdown -->
    <div class="flex items-center gap-1.5 text-xs font-medium text-neutral-600 self-end sm:self-auto">
        <label for="sort-select" class="text-neutral-500 whitespace-nowrap">Urutan:</label>
        <div class="relative">
            <select id="sort-select"
                    wire:model.live="activeSort"
                    class="appearance-none bg-transparent pr-6 pl-1 py-1 font-bold text-black text-xs cursor-pointer focus:outline-hidden">
                <option value="terbaru">Terbaru</option>
                <option value="terlama">Terlama</option>
            </select>
            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center text-neutral-500">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                </svg>
            </div>
        </div>
    </div>
</div>
