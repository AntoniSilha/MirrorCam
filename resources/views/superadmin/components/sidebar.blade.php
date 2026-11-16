<aside class="w-64 bg-[#ffffff] border-r border-[#e5e5e5] min-h-screen flex flex-col justify-between p-6 shrink-0">
    <div>
        <!-- Brand Logo -->
        <a href="{{ route('home') }}" class="flex items-center gap-2.5 group">
            <div class="w-8 h-8 bg-[#000000] text-white rounded-[4px] flex items-center justify-center shadow-xs">
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

        <!-- Main Navigation Links -->
        <div class="mt-8 space-y-2">
            <span class="block text-[10px] font-bold tracking-wider text-gray-400 uppercase mb-3">
                NAVIGASI UTAMA
            </span>

            <!-- Menu 1: Ringkasan & Dashboard -->
            @php $isDashboard = request()->routeIs('superadmin.dashboard'); @endphp
            <a
                href="{{ route('superadmin.dashboard') }}"
                class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-[4px] text-xs font-semibold transition {{ $isDashboard ? 'bg-[#000000] text-[#ffffff] shadow-xs' : 'bg-[#ffffff] text-[#000000] border border-gray-300 hover:bg-gray-50' }}"
            >
                <svg class="w-4 h-4 shrink-0 {{ $isDashboard ? 'text-white' : 'text-black' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <rect x="3" y="3" width="7" height="7" rx="1" stroke-width="2"/>
                    <rect x="14" y="3" width="7" height="7" rx="1" stroke-width="2"/>
                    <rect x="14" y="14" width="7" height="7" rx="1" stroke-width="2"/>
                    <rect x="3" y="14" width="7" height="7" rx="1" stroke-width="2"/>
                </svg>
                <span>Ringkasan & Dashboard</span>
            </a>

            <!-- Menu 2: Kelola Tenant -->
            @php $isTenant = request()->routeIs('superadmin.tenant.*'); @endphp
            <a
                href="{{ route('superadmin.tenant.index') }}"
                class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-[4px] text-xs font-semibold transition {{ $isTenant ? 'bg-[#000000] text-[#ffffff] shadow-xs' : 'bg-[#ffffff] text-[#000000] border border-gray-300 hover:bg-gray-50' }}"
            >
                <svg class="w-4 h-4 shrink-0 {{ $isTenant ? 'text-white' : 'text-black' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                <span>Kelola Tenant</span>
            </a>

            <!-- Menu 3: Pendapatan Komisi -->
            @php $isKomisi = request()->routeIs('superadmin.komisi.*'); @endphp
            <a
                href="{{ route('superadmin.komisi.index') }}"
                class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-[4px] text-xs font-semibold transition {{ $isKomisi ? 'bg-[#000000] text-[#ffffff] shadow-xs' : 'bg-[#ffffff] text-[#000000] border border-gray-300 hover:bg-gray-50' }}"
            >
                <svg class="w-4 h-4 shrink-0 {{ $isKomisi ? 'text-white' : 'text-black' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <rect x="2" y="6" width="20" height="12" rx="2" stroke-width="2"/>
                    <circle cx="12" cy="12" r="2" stroke-width="2"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 12h.01M18 12h.01"/>
                </svg>
                <span>Pendapatan Komisi</span>
            </a>
        </div>
    </div>

    <!-- Bottom User Profile Card -->
    <div class="pt-6 border-t border-[#e5e5e5]">
        <div class="bg-[#ffffff] border border-gray-200 rounded-[4px] p-2.5 flex items-center justify-between gap-2 shadow-xs">
            <div class="flex items-center gap-2.5 min-w-0">
                <div class="w-8 h-8 rounded-full bg-gray-200 overflow-hidden border border-gray-300 shrink-0 flex items-center justify-center font-bold text-xs text-black">
                    @if (auth()->check() && auth()->user()->avatar)
                        <img src="{{ auth()->user()->avatar }}" alt="SuperAdmin" class="w-full h-full object-cover">
                    @else
                        <img src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=120&q=80" alt="SuperAdmin" class="w-full h-full object-cover">
                    @endif
                </div>
                <div class="flex flex-col min-w-0">
                    <span class="text-xs font-bold text-[#000000] truncate leading-tight">
                        {{ auth()->user()->nama_lengkap_ktp ?? auth()->user()->name ?? 'MirrorCam' }}
                    </span>
                    <span class="text-[10px] text-gray-500 font-medium leading-tight">
                        SuperAdmin
                    </span>
                </div>
            </div>

            <!-- Logout Button -->
            <form action="{{ route('logout') }}" method="POST" class="shrink-0">
                @csrf
                <button
                    type="submit"
                    class="p-1.5 text-gray-400 hover:text-black hover:bg-gray-100 rounded-[4px] transition"
                    title="Keluar / Logout"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                </button>
            </form>
        </div>
    </div>
</aside>
