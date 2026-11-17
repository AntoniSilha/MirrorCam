@props(['user' => null])

<header class="sticky top-0 z-50 bg-[#ffffff] border-b border-[#e5e5e5] shadow-xs">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="relative flex items-center justify-between h-16 gap-4">
            <!-- Brand Logo -->
            <div class="flex items-center min-w-0">
                <a href="{{ route('home') }}" class="flex items-center gap-2.5 group shrink-0">
                    <div class="w-8 h-8 bg-[#000000] text-white rounded-[4px] flex items-center justify-center shadow-xs transition group-hover:scale-105">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <div class="flex flex-col text-left">
                        <span class="text-xs sm:text-sm font-extrabold tracking-tight text-[#000000] leading-none whitespace-nowrap">MIRRORCAM</span>
                        <span class="text-[7.5px] sm:text-[8px] font-bold tracking-widest text-[#000000] uppercase leading-tight mt-0.5">MULTI-TENANT RENTAL</span>
                    </div>
                </a>
            </div>

            <!-- Nav Menu Links (Desktop, centered) -->
            <!-- Nav Menu Links (Desktop, centered) -->
            <nav class="hidden md:flex items-center gap-4 lg:gap-6 text-xs lg:text-sm font-semibold text-[#000000] absolute left-1/2 -translate-x-1/2">
    <a href="{{ route('home') }}"
       class="px-3.5 py-1.5 rounded-[4px] whitespace-nowrap transition {{ request()->routeIs('home') ? 'bg-[#000000] text-[#ffffff]' : 'text-gray-600 hover:text-[#000000]' }}">
        Beranda
    </a>

    <a href="{{ route('tenant.index') }}"
       class="px-3.5 py-1.5 rounded-[4px] whitespace-nowrap transition {{ request()->routeIs('tenant.index') || request()->routeIs('tenant.show') ? 'bg-[#000000] text-[#ffffff]' : 'text-gray-600 hover:text-[#000000]' }}">
        Tenant
    </a>

    <a href="{{ route('katalog.index') }}"
   class="px-3.5 py-1.5 rounded-[4px] whitespace-nowrap transition {{ request()->routeIs('katalog.*') || request()->routeIs('tenant.katalog') ? 'bg-[#000000] text-[#ffffff]' : 'text-gray-600 hover:text-[#000000]' }}">
    Produk
    </a>

    <a href="{{ route('pengaduan.index') }}"
       class="px-3.5 py-1.5 rounded-[4px] whitespace-nowrap transition {{ request()->routeIs('pengaduan.*') ? 'bg-[#000000] text-[#ffffff]' : 'text-gray-600 hover:text-[#000000]' }}">
        Pengaduan
    </a>
</nav>

            <!-- Header Actions / Auth (Right side) -->
            <div class="flex items-center gap-4 shrink-0">
                @php($cartCount = app(\App\Services\CartService::class)->count())
                <!-- Shopping Bag / Cart Icon with Cart Item Count -->
                <a href="{{ route('keranjang.index') }}" class="relative p-1 text-[#000000] hover:opacity-80 transition" title="Keranjang Belanja" aria-label="Keranjang Belanja ({{ $cartCount }} item)">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-[#000000]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                    @if ($cartCount > 0)
                        <span class="absolute -top-1 -right-1 bg-[#000000] text-white text-[9px] sm:text-[10px] font-extrabold rounded-[3px] min-w-4 h-4 px-1 flex items-center justify-center">{{ $cartCount }}</span>
                    @endif
                </a>

                @auth
                    <!-- User is logged in -->
                    <div class="flex items-center gap-2">
                        <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 px-2 py-1 rounded-[4px] hover:bg-[#f9f9f9] transition">
                            <div class="w-8 h-8 rounded-full overflow-hidden bg-gray-200 border border-gray-300 shrink-0 flex items-center justify-center">
                                @if (auth()->user()->avatar)
                                    <img src="{{ auth()->user()->avatar }}" alt="{{ auth()->user()->name }}" class="w-full h-full object-cover">
                                @else
                                    <img src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=120&q=80" alt="{{ auth()->user()->name }}" class="w-full h-full object-cover">
                                @endif
                            </div>
                            <div class="hidden sm:flex flex-col text-left leading-tight">
                                <span class="text-xs font-bold text-[#000000] max-w-[120px] truncate">{{ auth()->user()->name }}</span>
                                <span class="text-[10px] text-gray-500">{{ auth()->user()->role_label }}</span>
                            </div>
                        </a>
                        <form action="{{ route('logout') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="text-xs text-gray-500 hover:text-black font-semibold cursor-pointer px-2 py-1.5 rounded-[4px] hover:bg-gray-100 transition" title="Logout">
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
            <a href="{{ route('home') }}" onclick="document.getElementById('mobile-menu').classList.add('hidden')" class="block px-3 py-2 text-sm font-medium {{ request()->routeIs('home') ? 'bg-black text-white' : 'text-gray-800 hover:bg-gray-50' }} rounded-[4px]">Beranda</a>
            <a href="{{ route('tenant.index') }}" onclick="document.getElementById('mobile-menu').classList.add('hidden')" class="block px-3 py-2 text-sm font-medium {{ request()->routeIs('tenant.*') ? 'bg-black text-white' : 'text-gray-800 hover:bg-gray-50' }} rounded-[4px]">Tenant</a>
            <a href="{{ route('pengaduan.index') }}" onclick="document.getElementById('mobile-menu').classList.add('hidden')" class="block px-3 py-2 text-sm font-medium {{ request()->routeIs('pengaduan.*') ? 'bg-black text-white' : 'text-gray-800 hover:bg-gray-50' }} rounded-[4px]">Pengaduan</a>
        </div>
    </div>
</header>
