        <header class="sticky top-0 z-30 flex h-16 items-center justify-between border-b border-neutral-200 bg-[#f9f9f9]/95 px-5 backdrop-blur md:justify-end md:px-8">
            <button type="button" wire:click="toggleSidebar" class="rounded-md p-1.5 text-neutral-600 hover:bg-neutral-200 md:hidden">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>

            <a href="{{ route('home') }}" class="flex items-center gap-2 text-xs font-semibold text-neutral-700 transition hover:text-black md:text-sm">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 21h18M5 21V7l7-4 7 4v14M9 9h.01M15 9h.01M9 13h.01M15 13h.01M9 17h.01M15 17h.01" />
                </svg>
                <span>Kembali ke Beranda</span>
            </a>
        </header>
