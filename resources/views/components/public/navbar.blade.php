@props(['user' => null])

<header class="sticky top-0 z-50 bg-[#ffffff] border-b border-[#e5e5e5] shadow-xs">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="relative flex items-center justify-between h-16 gap-4">
            <!-- Brand Logo -->
            <div class="flex items-center min-w-0">
                <a href="{{ route('home') }}" class="flex items-center gap-2 group shrink-0">
                    <div class="w-8 h-8 bg-[#000000] text-white rounded-[4px] flex items-center justify-center shadow-xs transition group-hover:scale-105">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <span class="text-base sm:text-lg font-extrabold tracking-tight text-[#000000] whitespace-nowrap">MIRRORCAM</span>
                </a>
            </div>

            <!-- Nav Menu Links (Desktop, centered) -->
            <!-- Nav Menu Links (Desktop, centered) -->
            <nav class="hidden md:flex items-center gap-6 lg:gap-8 text-xs lg:text-sm font-semibold text-[#000000] absolute left-1/2 -translate-x-1/2">
                <a href="{{ route('home') }}" class="relative py-2 whitespace-nowrap transition hover:text-black {{ request()->routeIs('home') ? 'font-bold text-[#000000]' : 'text-gray-600' }}">
                    Beranda
                    @if (request()->routeIs('home'))
                        <span class="absolute bottom-0 left-0 w-full h-[2px] bg-[#000000]"></span>
                    @endif
                </a>
                <a href="{{ route('home') }}#mitra-tenant" class="relative py-2 whitespace-nowrap text-gray-600 transition hover:text-black">
                    Tenant
                </a>
                <a href="{{ route('pengaduan.index') }}" class="relative py-2 whitespace-nowrap transition hover:text-black {{ request()->routeIs('pengaduan.*') ? 'font-bold text-[#000000]' : 'text-gray-600' }}">
                    Pengaduan
                    @if (request()->routeIs('pengaduan.*'))
                        <span class="absolute bottom-0 left-0 w-full h-[2.5px] bg-[#000000]"></span>
                    @endif
                </a>
            </nav>

            <!-- Header Actions / Auth (Right side) -->
            <div class="flex items-center gap-3 shrink-0">
                @auth
                    <!-- Notification Bell -->
                    <a href="{{ route('pengaduan.index') }}" class="relative p-1.5 text-gray-700 hover:text-black transition rounded-[4px] hover:bg-gray-100" title="Notifikasi Mediasi">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        <span class="absolute top-1 right-1 w-4 h-4 bg-[#ef4444] text-white text-[10px] font-extrabold rounded-full flex items-center justify-center">1</span>
                    </a>

                    <!-- User is logged in -->
                    <div class="flex items-center gap-2">
                        <a href="{{ route('dashboard') }}" class="flex items-center gap-2 px-2.5 py-1.5 rounded-[4px] hover:bg-[#f9f9f9] border border-[#e5e5e5] transition">
                            <div class="w-7 h-7 rounded-full bg-[#000000] text-white flex items-center justify-center text-xs font-bold shrink-0">
                                {{ auth()->user() && method_exists(auth()->user(), 'initials') ? auth()->user()->initials() : (auth()->user()->initials ?? 'US') }}
                            </div>
                            <div class="hidden sm:flex flex-col text-left leading-tight">
                                <span class="text-xs font-bold text-[#000000] max-w-[120px] truncate">{{ auth()->user()->name }}</span>
                                <span class="text-[10px] text-gray-500">User</span>
                            </div>
                            <svg class="w-3.5 h-3.5 text-gray-500 ml-1 hidden sm:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </a>
                        <form action="{{ route('logout') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="text-xs text-gray-600 hover:text-black font-semibold cursor-pointer px-2 py-1.5 rounded-[4px] hover:bg-gray-100 transition">
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
                                Login
                            </a>
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
