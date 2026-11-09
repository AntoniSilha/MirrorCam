@props(['user' => null])

<header class="sticky top-0 z-50 bg-[#ffffff] border-b border-[#e5e5e5] shadow-xs">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16 gap-4">
            <!-- Brand Logo & Desktop Nav Links -->
            <div class="flex items-center gap-6 lg:gap-8 min-w-0">
                <a href="{{ route('home') }}" class="flex items-center gap-2 group shrink-0">
                    <div class="w-8 h-8 bg-[#000000] text-white rounded-[4px] flex items-center justify-center shadow-xs transition group-hover:scale-105">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <span class="text-base sm:text-lg font-extrabold tracking-tight text-[#000000] whitespace-nowrap">MIRRORCAM</span>
                </a>

                <!-- Nav Menu Links (Desktop) -->
                <nav class="hidden md:flex items-center gap-4 lg:gap-6 text-xs lg:text-sm font-semibold text-[#000000]/80">
                    <a href="#kategori" class="whitespace-nowrap hover:text-[#000000] transition">Kategori</a>
                    <a href="#mitra-tenant" class="whitespace-nowrap hover:text-[#000000] transition">Mitra Tenant</a>
                    <a href="#katalog" class="whitespace-nowrap hover:text-[#000000] transition">Rekomendasi Gear</a>
                    <a href="#cara-kerja" class="whitespace-nowrap hover:text-[#000000] transition">Cara Sewa</a>
                    <a href="#keamanan" class="whitespace-nowrap hover:text-[#000000] transition">Keamanan</a>
                    <a href="#faq" class="whitespace-nowrap hover:text-[#000000] transition">FAQ</a>
                </nav>
            </div>

            <!-- Header Actions / Auth (Right side) -->
            <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                @auth
                    <!-- User is logged in -->
                    <div class="flex items-center gap-2 sm:gap-3">
                        <a href="{{ route('dashboard') }}" class="flex items-center gap-2 sm:gap-2.5 px-2.5 sm:px-3 py-1.5 rounded-[4px] hover:bg-[#f9f9f9] border border-transparent hover:border-[#e5e5e5] transition max-w-[200px] sm:max-w-xs">
                            <div class="w-7 h-7 rounded-full bg-[#000000] text-white flex items-center justify-center text-xs font-bold shrink-0">
                                {{ auth()->user() && method_exists(auth()->user(), 'initials') ? auth()->user()->initials() : (auth()->user()->initials ?? 'US') }}
                            </div>
                            <span class="text-xs font-semibold text-[#000000] hidden sm:inline truncate whitespace-nowrap">{{ auth()->user()->name }}</span>
                        </a>
                        <form action="{{ route('logout') }}" method="POST" class="inline">
    @csrf
    <button type="submit" class="text-xs text-gray-600 hover:text-black font-semibold cursor-pointer">
        Keluar
    </button>
</form>
                    </div>
                @else
                    <!-- Guest -->
                    @if (Route::has('login'))
                        <div class="flex items-center gap-2 shrink-0">
                            <a
                                href="{{ route('login') }}"
                                class="px-3.5 py-1.5 text-xs font-semibold text-[#000000] bg-[#d9d9d9] hover:bg-[#cccccc] rounded-[4px] transition whitespace-nowrap"
                            >
                                Masuk
                            </a>
                            @if (Route::has('register'))
                                <a
                                    href="{{ route('register') }}"
                                    class="px-3.5 py-1.5 text-xs font-semibold text-white bg-[#000000] hover:bg-[#222222] rounded-[4px] shadow-xs transition whitespace-nowrap"
                                >
                                    Daftar
                                </a>
                            @endif
                        </div>
                    @endif
                @endauth

                <!-- Mobile Menu Button (Hamburger) -->
                <button
                    type="button"
                    onclick="document.getElementById('mobile-menu').classList.toggle('hidden')"
                    class="md:hidden p-2 rounded-[4px] text-gray-700 hover:text-black hover:bg-gray-100 focus:outline-hidden"
                    aria-label="Toggle navigation menu"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Mobile Dropdown Menu -->
        <div id="mobile-menu" class="hidden md:hidden border-t border-[#e5e5e5] py-3 space-y-2">
            <a href="#kategori" onclick="document.getElementById('mobile-menu').classList.add('hidden')" class="block px-3 py-2 text-sm font-medium text-gray-800 hover:bg-gray-50 rounded-[4px]">Kategori</a>
            <a href="#mitra-tenant" onclick="document.getElementById('mobile-menu').classList.add('hidden')" class="block px-3 py-2 text-sm font-medium text-gray-800 hover:bg-gray-50 rounded-[4px]">Mitra Tenant</a>
            <a href="#katalog" onclick="document.getElementById('mobile-menu').classList.add('hidden')" class="block px-3 py-2 text-sm font-medium text-gray-800 hover:bg-gray-50 rounded-[4px]">Rekomendasi Gear</a>
            <a href="#cara-kerja" onclick="document.getElementById('mobile-menu').classList.add('hidden')" class="block px-3 py-2 text-sm font-medium text-gray-800 hover:bg-gray-50 rounded-[4px]">Cara Sewa</a>
            <a href="#keamanan" onclick="document.getElementById('mobile-menu').classList.add('hidden')" class="block px-3 py-2 text-sm font-medium text-gray-800 hover:bg-gray-50 rounded-[4px]">Keamanan</a>
            <a href="#faq" onclick="document.getElementById('mobile-menu').classList.add('hidden')" class="block px-3 py-2 text-sm font-medium text-gray-800 hover:bg-gray-50 rounded-[4px]">FAQ</a>
        </div>
    </div>
</header>
