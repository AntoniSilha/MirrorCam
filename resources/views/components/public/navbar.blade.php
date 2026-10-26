@props(['user' => null])

<header class="sticky top-0 z-50 bg-[#ffffff] border-b border-[#e5e5e5] shadow-xs">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            <!-- Brand Logo -->
            <div class="flex items-center gap-8">
                <a href="{{ route('home') }}" class="flex items-center gap-2 group">
                    <div class="w-8 h-8 bg-[#000000] text-white rounded-[4px] flex items-center justify-center shadow-xs transition group-hover:scale-105">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <span class="text-lg font-extrabold tracking-tight text-[#000000]">MIRRORCAM</span>
                </a>

                <!-- Nav Menu Links (Desktop) -->
                <nav class="hidden md:flex items-center gap-6 text-sm font-medium text-[#000000]/80">
                    <a href="#kategori" class="hover:text-[#000000] transition">Kategori</a>
                    <a href="#mitra-tenant" class="hover:text-[#000000] transition">Mitra Tenant</a>
                    <a href="#katalog" class="hover:text-[#000000] transition">Rekomendasi Gear</a>
                    <a href="#cara-kerja" class="hover:text-[#000000] transition">Cara Sewa</a>
                    <a href="#keamanan" class="hover:text-[#000000] transition">Keamanan</a>
                    <a href="#faq" class="hover:text-[#000000] transition">FAQ</a>
                </nav>
            </div>

            <!-- Header Actions / Auth -->
            <div class="flex items-center gap-3">
                <!-- Search input shortcut in header -->
                <div class="hidden lg:block relative">
                    <form action="{{ route('home') }}" method="GET">
                        <div class="relative">
                            <input
                                type="text"
                                name="keyword"
                                placeholder="Cari gear, kamera..."
                                class="w-56 pl-9 pr-3 py-1.5 text-xs bg-[#f9f9f9] border border-[#e5e5e5] rounded-[4px] text-[#000000] placeholder-gray-400 focus:outline-hidden focus:ring-1 focus:ring-[#000000] focus:border-[#000000] transition"
                            />
                            <svg class="w-4 h-4 text-gray-400 absolute left-2.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                    </form>
                </div>

                @auth
                    <!-- User is logged in -->
                    <div class="flex items-center gap-3">
                        <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 px-3 py-1.5 rounded-[4px] hover:bg-[#f9f9f9] border border-transparent hover:border-[#e5e5e5] transition">
                            <div class="w-7 h-7 rounded-full bg-[#000000] text-white flex items-center justify-center text-xs font-bold">
                                {{ auth()->user()->initials() ?? 'US' }}
                            </div>
                            <span class="text-xs font-semibold text-[#000000] hidden sm:inline">{{ auth()->user()->name }}</span>
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="text-xs font-medium text-gray-500 hover:text-[#000000] px-2 py-1 transition">
                                Keluar
                            </button>
                        </form>
                    </div>
                @else
                    <!-- Guest -->
                    @if (Route::has('login'))
                        <div class="flex items-center gap-2">
                            <a
                                href="{{ route('login') }}"
                                class="px-3.5 py-1.5 text-xs font-semibold text-[#000000] bg-[#d9d9d9] hover:bg-[#cccccc] rounded-[4px] transition"
                            >
                                Masuk
                            </a>
                            @if (Route::has('register'))
                                <a
                                    href="{{ route('register') }}"
                                    class="px-3.5 py-1.5 text-xs font-semibold text-white bg-[#000000] hover:bg-[#222222] rounded-[4px] shadow-xs transition"
                                >
                                    Daftar
                                </a>
                            @endif
                        </div>
                    @endif
                @endauth
            </div>
        </div>
    </div>
</header>
