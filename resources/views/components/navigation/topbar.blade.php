@props(['sidebarToggle' => false])

<header class="flex w-full items-center justify-between border-b border-neutral-200/50 bg-[#f9f9f9] px-4 py-3 sm:px-6 md:px-12 md:py-4">
    <!-- Mobile Brand / Left Header -->
    <div class="flex min-w-0 items-center gap-2">
        @if ($sidebarToggle)
            <label for="user-sidebar-toggle" class="rounded-md p-1.5 text-neutral-600 transition hover:bg-neutral-200 hover:text-black md:hidden" aria-label="Buka menu">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </label>
        @endif

        <a href="{{ route('home') }}" class="flex min-w-0 items-center gap-2">
            <div class="w-7 h-7 rounded-sm bg-[#000000] flex items-center justify-center text-white">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                    <circle cx="12" cy="13" r="3" stroke-width="2" />
                </svg>
            </div>
            <span class="text-xs font-bold text-black">MIRRORCAM</span>
        </a>
    </div>


    <!-- Spacer for desktop alignment -->
    <div class="hidden md:block"></div>

    <!-- Right Header Action: Kembali ke Beranda -->
    <div class="flex shrink-0 items-center">
        <a href="{{ route('home') }}" class="flex items-center gap-2 text-xs font-semibold text-neutral-600 transition hover:text-black md:text-sm">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 12l9-9 9 9M5 10v10h5v-6h4v6h5V10" />
            </svg>
            <span class="hidden sm:inline">Kembali ke Beranda</span>
            <span class="sm:hidden">Beranda</span>
        </a>
    </div>
</header>
