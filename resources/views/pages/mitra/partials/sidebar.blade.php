    <aside class="fixed inset-y-0 left-0 z-50 flex h-dvh w-[280px] max-w-[85vw] shrink-0 flex-col justify-between overflow-y-auto overscroll-contain border-r border-neutral-200 bg-white px-5 py-6 transition-transform duration-200 md:sticky md:top-0 md:z-30 md:w-[280px] md:translate-x-0 md:px-6 md:py-7 lg:w-[320px] lg:px-8 {{ $sidebarOpen ? 'translate-x-0' : '-translate-x-full' }}">
        <div>
            <div class="mb-10 flex items-center justify-between">
                <button type="button" wire:click="showSection('ringkasan')" class="flex items-center gap-3 text-left">
                    <span class="flex h-9 w-9 items-center justify-center rounded-sm bg-black text-white shadow-xs">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                            <circle cx="12" cy="13" r="3" stroke-width="2" />
                        </svg>
                    </span>
                    <span>
                        <span class="block text-sm font-bold leading-none tracking-tight text-black">MIRRORCAM</span>
                        <span class="mt-1 block text-[8px] font-semibold uppercase leading-none tracking-[0.18em] text-neutral-400">MULTI-TENANT RENTAL</span>
                    </span>
                </button>

                <button type="button" wire:click="toggleSidebar" class="rounded-md p-1 text-neutral-500 hover:bg-neutral-100 md:hidden">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="mb-3">
                <span class="text-[11px] font-semibold uppercase tracking-[0.12em] text-neutral-400">NAVIGASI UTAMA</span>
            </div>

            <nav class="space-y-2.5">
                <button type="button" wire:click="showSection('ringkasan')" class="flex w-full items-center gap-3 rounded-lg border px-4 py-3 text-left text-[13px] font-semibold transition {{ $section === 'ringkasan' ? 'border-black bg-black text-white shadow-xs' : 'border-neutral-800 bg-white text-black hover:bg-neutral-50' }}">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 4h6v6H4V4zm10 0h6v6h-6V4zM4 14h6v6H4v-6zm10 0h6v6h-6v-6z" />
                    </svg>
                    <span>Ringkasan &amp; Dashboard</span>
                </button>

                <button type="button" wire:click="showSection('produk')" class="flex w-full items-center gap-3 rounded-lg border px-4 py-3 text-left text-[13px] font-semibold transition {{ $section === 'produk' ? 'border-black bg-black text-white shadow-xs' : 'border-neutral-800 bg-white text-black hover:bg-neutral-50' }}">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                        <circle cx="12" cy="13" r="3" stroke-width="1.8" />
                    </svg>
                    <span>Kelola Produk</span>
                </button>

                <button type="button" wire:click="showSection('pesanan')" class="flex w-full items-center gap-3 rounded-lg border px-4 py-3 text-left text-[13px] font-semibold transition {{ $section === 'pesanan' ? 'border-black bg-black text-white shadow-xs' : 'border-neutral-800 bg-white text-black hover:bg-neutral-50' }}">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2M9 12h6M9 16h6" />
                    </svg>
                    <span>Kelola Pesanan Sewa</span>
                </button>

                <button type="button" wire:click="showSection('profil')" class="flex w-full items-center gap-3 rounded-lg border px-4 py-3 text-left text-[13px] font-semibold transition {{ $section === 'profil' ? 'border-black bg-black text-white shadow-xs' : 'border-neutral-800 bg-white text-black hover:bg-neutral-50' }}">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 10l1.5-5.5A2 2 0 016.43 3h11.14a2 2 0 011.93 1.5L21 10M3 10v9a2 2 0 002 2h14a2 2 0 002-2v-9M3 10h18M8 14h.01M12 14h.01M16 14h.01" />
                    </svg>
                    <span>Pengaturan Profile</span>
                </button>

                <button type="button" wire:click="showSection('keamanan')" class="flex w-full items-center gap-3 rounded-lg border px-4 py-3 text-left text-[13px] font-semibold transition {{ $section === 'keamanan' ? 'border-black bg-black text-white shadow-xs' : 'border-neutral-800 bg-white text-black hover:bg-neutral-50' }}">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 3l7 3v6c0 4.2-2.9 7.4-7 9-4.1-1.6-7-4.8-7-9V6l7-3z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9.5 12.5l1.8 1.8 3.4-4" />
                    </svg>
                    <span>Keamanan &amp; Password</span>
                </button>

                <button type="button" wire:click="showSection('komisi')" class="flex w-full items-center gap-3 rounded-lg border px-4 py-3 text-left text-[13px] font-semibold transition {{ $section === 'komisi' ? 'border-black bg-black text-white shadow-xs' : 'border-neutral-800 bg-white text-black hover:bg-neutral-50' }}">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <rect x="3" y="6" width="18" height="13" rx="2" stroke-width="1.8" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 10h18M16 14.5h2" />
                    </svg>
                    <span>Pembayaran Komisi</span>
                </button>
            </nav>
        </div>

        <div class="mt-8 border-t border-neutral-100 pt-4">
            <div class="flex items-center justify-between rounded-xl border border-neutral-200 bg-white p-3 shadow-[0_1px_2px_rgba(0,0,0,0.04)]">
                <div class="flex min-w-0 items-center gap-3">
                    @if ($tenant['logo'])
                        <img src="{{ $tenant['logo'] }}" alt="{{ $tenant['nama_toko'] }}" class="h-10 w-10 shrink-0 rounded-full border border-neutral-200 object-cover">
                    @else
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#d9d9d9] text-xs font-bold text-black">
                            {{ $tenant['inisial'] }}
                        </div>
                    @endif

                    <div class="min-w-0">
                        <div class="truncate text-xs font-bold leading-tight text-black">{{ $tenant['nama_toko'] }}</div>
                        <div class="mt-1 text-[10px] font-medium leading-tight text-neutral-400">Admin</div>
                    </div>
                </div>

                <form method="POST" action="{{ route('logout') }}" class="shrink-0">
                    @csrf
                    <button type="submit" title="" class="rounded-md p-1.5 text-neutral-400 transition hover:bg-neutral-100 hover:text-black">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </aside>
