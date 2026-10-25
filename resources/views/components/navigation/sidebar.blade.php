@props(['user' => []])

@php
    $userName = $user['nama_lengkap_ktp'] ?? auth()->user()?->name ?? 'Antoni Silha';
    $userRole = $user['role'] ?? 'User';
    $userAvatar = $user['avatar'] ?? auth()->user()?->avatar ?? null;
    $userInitials = $user['initials'] ?? (method_exists(auth()->user(), 'initials') ? auth()->user()->initials() : 'AS');
    $isOrdersActive = request()->routeIs('dashboard');
@endphp

<aside class="w-full md:w-64 lg:w-72 bg-[#ffffff] border-r border-neutral-200 min-h-screen flex flex-col justify-between p-6 shrink-0">
    <div>
        <!-- Brand / Logo -->
        <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 mb-10">
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
            <form method="POST" action="{{ route('logout') }}" class="shrink-0">
                @csrf
                <button type="submit" title="Keluar" class="p-1.5 text-neutral-400 hover:text-black rounded-sm hover:bg-neutral-100 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                </button>
            </form>
        </div>
    </div>
</aside>
