@props(['user' => []])

@php
    $userName = $user['nama_lengkap_ktp'] ?? auth()->user()?->name ?? 'Antoni Silha';
    $userRole = $user['role'] ?? 'User';
    $userAvatar = $user['avatar'] ?? auth()->user()?->avatar_url ?? null;
    $userInitials = $user['initials'] ?? (auth()->user() && method_exists(auth()->user(), 'initials') ? auth()->user()->initials() : 'AS');
    $isOrdersActive = request()->routeIs('dashboard');
    $isProfileActive = request()->routeIs('profile.*');
@endphp

<aside class="fixed inset-y-0 left-0 z-50 flex h-dvh w-[280px] max-w-[85vw] shrink-0 -translate-x-full flex-col justify-between overflow-y-auto overscroll-contain border-r border-neutral-200 bg-[#ffffff] p-5 transition-transform duration-200 peer-checked:translate-x-0 md:sticky md:top-0 md:z-30 md:h-screen md:w-64 md:translate-x-0 md:p-6 lg:w-72">
    <div>
        <!-- Brand / Logo -->
        <div class="mb-8 flex items-center justify-between md:mb-10">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-sm bg-[#000000] flex items-center justify-center text-white shadow-xs">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                        <circle cx="12" cy="13" r="3" stroke-width="2" />
                    </svg>
                </div>
                <div>
                    <span class="block text-sm font-bold tracking-tight text-black leading-none">MIRRORCAM</span>
                    <span class="block text-[8px] font-semibold tracking-widest text-neutral-400 uppercase mt-0.5 leading-none">MULTI-TENANT RENTAL</span>
                </div>
            </a>

            <label for="user-sidebar-toggle" class="rounded-md p-1.5 text-neutral-500 transition hover:bg-neutral-100 hover:text-black md:hidden" aria-label="Tutup menu">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </label>
        </div>

        <!-- Section Label -->
        <div class="mb-3">
            <span class="text-[11px] font-semibold text-neutral-400 tracking-wider uppercase">NAVIGASI UTAMA</span>
        </div>

        <!-- Navigation Links -->
        <nav class="space-y-2.5">
            <!-- Data Pesanan Link -->
            <a href="{{ route('dashboard') }}"
               class="w-full flex items-center gap-3 px-4 py-2.5 text-xs font-semibold rounded-sm transition-all duration-150 {{ $isOrdersActive ? 'bg-[#000000] text-[#ffffff] shadow-xs' : 'bg-[#ffffff] text-[#000000] border border-neutral-300 hover:bg-neutral-50' }}">
                <svg class="w-4 h-4 {{ $isOrdersActive ? 'text-white' : 'text-black' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2" stroke-width="2"></rect>
                    <line x1="16" y1="2" x2="16" y2="6" stroke-width="2"></line>
                    <line x1="8" y1="2" x2="8" y2="6" stroke-width="2"></line>
                    <line x1="3" y1="10" x2="21" y2="10" stroke-width="2"></line>
                </svg>
                <span>Data Pesanan</span>
            </a>

            <!-- Profile Link -->
            <a href="{{ route('profile.edit') }}"
               class="w-full flex items-center gap-3 px-4 py-2.5 text-xs font-semibold rounded-sm transition-all duration-150 {{ $isProfileActive ? 'bg-[#000000] text-[#ffffff] shadow-xs' : 'bg-[#ffffff] text-[#000000] border border-neutral-300 hover:bg-neutral-50' }}">
                <svg class="w-4 h-4 {{ $isProfileActive ? 'text-white' : 'text-black' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                <span>Profil Saya</span>
            </a>
        </nav>
    </div>

    <!-- User Profile Widget (Antoni Silha) -->
    <div class="mt-8 pt-4 border-t border-neutral-100">
        <div class="border border-neutral-200 rounded-lg p-2.5 flex items-center justify-between bg-white shadow-2xs hover:border-neutral-300 transition">
            <div class="flex items-center gap-2.5 min-w-0">
                @if ($userAvatar)
                    <img src="{{ $userAvatar }}" alt="{{ $userName }}" class="w-8 h-8 rounded-full object-cover border border-neutral-200 shrink-0" />
                @else
                    <div class="w-8 h-8 rounded-full bg-[#d9d9d9] text-black font-bold flex items-center justify-center text-xs shrink-0">
                        {{ $userInitials }}
                    </div>
                @endif
                <div class="min-w-0">
                    <div class="text-xs font-bold text-black truncate leading-tight">{{ $userName }}</div>
                    <div class="text-[10px] text-neutral-400 font-medium leading-tight mt-0.5">{{ $userRole }}</div>
                </div>
            </div>

            <!-- Logout Action -->
            <form action="{{ route('logout') }}" method="POST" class="inline">
    @csrf
    <button type="submit" class="text-xs text-gray-600 hover:text-black font-semibold cursor-pointer">
        Keluar
    </button>
</form>
        </div>
    </div>
</aside>
