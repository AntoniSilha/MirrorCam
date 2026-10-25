<header class="w-full bg-[#f9f9f9] px-6 md:px-12 py-4 flex items-center justify-between border-b border-neutral-200/50">
    <!-- Mobile Brand / Left Header -->
    <div class="flex items-center gap-2">
        <a href="{{ route('home') }}" class="flex items-center gap-2">
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
    <div class="flex items-center">
        <a href="{{ route('home') }}" class="flex items-center gap-2 text-xs md:text-sm font-semibold text-neutral-600 hover:text-black transition">
            <span>Kembali ke Beranda &rarr;</span>
        </a>
    </div>
</header>
