                    <section class="rounded-xl border border-neutral-200 bg-white p-6 shadow-[0_1px_2px_rgba(0,0,0,0.02)] md:p-7">
                        <div class="flex flex-col justify-between gap-5 lg:flex-row lg:items-center">
                            <div>
                                <h1 class="text-[25px] font-bold leading-tight tracking-tight text-black md:text-[28px]">{{ $hasTenant ? 'Halo, Pemilik '.$tenant['nama_toko'] : 'Halo, Pemilik Toko' }}</h1>
                                <p class="mt-3 max-w-2xl text-xs leading-relaxed text-neutral-500 md:text-sm">
                                    @if ($hasTenant)
                                        Ringkasan operasional harian, katalog inventaris gear, serta pemantauan komisi platform Anda di {{ $tenant['alamat'] }}.
                                    @else
                                        Lengkapi profil tenant Anda untuk mulai mengelola katalog gear dan memantau pendapatan.
                                    @endif
                                </p>
                            </div>

                            <div class="flex flex-col gap-3 sm:flex-row">
                                <button type="button" wire:click="exportCsv" wire:loading.attr="disabled" class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md bg-[#e9e9e9] px-5 py-3 text-xs font-semibold text-black transition hover:bg-[#dedede] disabled:opacity-60">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 3v12m0 0l-4-4m4 4l4-4M5 21h14" />
                                    </svg>
                                    <span>Unduh Rekap Bulanan</span>
                                </button>

                                <button type="button" wire:click="openProductModal" class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md bg-black px-5 py-3 text-xs font-semibold text-white transition hover:bg-neutral-800">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m6-6H6" />
                                    </svg>
                                    <span>Tambah Produk</span>
                                </button>
                            </div>
                        </div>
                    </section>

                    <section class="mt-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                        <div class="rounded-lg border border-neutral-200 bg-white p-5 shadow-[0_1px_2px_rgba(0,0,0,0.02)] md:p-6">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.1em] text-neutral-500">TOTAL PRODUK</p>
                            <div class="mt-6 flex items-baseline gap-2">
                                <span class="text-[42px] font-normal leading-none tracking-tight text-black">{{ number_format($stats['total_produk'], 0, ',', '.') }}</span>
                                <span class="text-sm text-neutral-500">Unit</span>
                            </div>
                        </div>

                        <div class="rounded-lg border border-neutral-200 bg-white p-5 shadow-[0_1px_2px_rgba(0,0,0,0.02)] md:p-6">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.1em] text-neutral-500">PENYEWAAN BERJALAN</p>
                            <div class="mt-6 flex items-baseline gap-2">
                                <span class="text-[42px] font-normal leading-none tracking-tight text-black">{{ str_pad((string) $stats['penyewaan_berjalan'], 2, '0', STR_PAD_LEFT) }}</span>
                                <span class="text-sm text-neutral-500">Booking</span>
                            </div>
                        </div>

                        <div class="rounded-lg border border-neutral-200 bg-white p-5 shadow-[0_1px_2px_rgba(0,0,0,0.02)] md:p-6">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.1em] text-neutral-500">PENDAPATAN TENANT</p>
                            <div class="mt-6 flex flex-wrap items-baseline gap-x-3 gap-y-1">
                                <span class="text-[29px] font-normal leading-none tracking-tight text-black md:text-[32px]">{{ $this->rupiah($stats['pendapatan_tenant']) }}</span>
                                <span class="text-xs font-medium {{ $growth >= 0 ? 'text-[#16a34a]' : 'text-[#dc2626]' }}">{{ $growthLabel }}</span>
                            </div>
                        </div>
                    </section>

                    <section class="mt-5 overflow-hidden rounded-xl border border-neutral-200 bg-white shadow-[0_1px_2px_rgba(0,0,0,0.02)]">
                        <div class="flex flex-col gap-4 border-b border-neutral-200 px-5 py-5 sm:flex-row sm:items-center sm:justify-between md:px-6">
                            <div class="flex items-center gap-3">
                                <svg class="h-5 w-5 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 20V10m5 10V4m5 16v-7m5 7V8" />
                                </svg>
                                <h2 class="text-base font-bold tracking-tight text-black md:text-lg">Tren &amp; Analisis Pendapatan Sewa</h2>
                            </div>

                            <button type="button" wire:click="exportCsv" wire:loading.attr="disabled" class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md bg-[#e9e9e9] px-4 py-2.5 text-[11px] font-semibold text-black transition hover:bg-[#dedede] disabled:opacity-60">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 3v12m0 0l-4-4m4 4l4-4M5 21h14" />
                                </svg>
                                <span>Unduh Laporan (CSV/PDF)</span>
                            </button>
                        </div>

                        <div class="p-5 md:p-6">
                            <div class="grid gap-4 md:grid-cols-3">
                                <div class="rounded-md bg-[#f1f1f1] px-5 py-4">
                                    <p class="text-[10px] font-semibold uppercase tracking-[0.08em] text-neutral-500">PENDAPATAN TENANT</p>
                                    <p class="mt-4 text-xl font-bold tracking-tight text-black">{{ $this->rupiah($stats['pendapatan_tenant']) }}</p>
                                </div>

                                <div class="rounded-md bg-[#f1f1f1] px-5 py-4">
                                    <p class="text-[10px] font-semibold uppercase tracking-[0.08em] text-neutral-500">PENDAPATAN RATA-RATA HARIAN</p>
                                    <p class="mt-4 text-xl font-bold tracking-tight text-black">{{ $this->rupiah($stats['pendapatan_harian_rata_rata']) }}</p>
                                </div>

                                <div class="rounded-md bg-[#f1f1f1] px-5 py-4">
                                    <p class="text-[10px] font-semibold uppercase tracking-[0.08em] text-neutral-500">TOTAL UNIT TERSEWA</p>
                                    <p class="mt-4 text-xl font-bold tracking-tight text-black">{{ number_format($stats['total_unit_tersewa'], 0, ',', '.') }} Unit</p>
                                </div>
                            </div>

                            <div class="mt-6 grid gap-5 xl:grid-cols-[minmax(0,1fr)_320px]">
                                <div class="min-w-0">
                                    @if ($chartHasData)
                                    <svg viewBox="0 0 640 250" class="h-auto w-full" role="img" aria-label="Grafik tren pendapatan sewa">
                                        <defs>
                                            <linearGradient id="revenueAreaGradient" x1="0" y1="0" x2="0" y2="1">
                                                <stop offset="0%" stop-color="#bfbfbf" stop-opacity="0.72" />
                                                <stop offset="100%" stop-color="#f3f3f3" stop-opacity="0.12" />
                                            </linearGradient>
                                        </defs>

                                        @foreach ($chart['grid'] as $line)
                                            <line x1="{{ $chart['plot']['left'] }}" y1="{{ $line['y'] }}" x2="{{ $chart['plot']['right'] }}" y2="{{ $line['y'] }}" stroke="#dedede" stroke-width="1" stroke-dasharray="2 5" />
                                            <text x="{{ $chart['plot']['left'] - 8 }}" y="{{ $line['y'] + 3 }}" text-anchor="end" font-size="9" fill="#9ca3af">{{ $line['label'] }}</text>
                                        @endforeach

                                        <path d="{{ $chart['area_path'] }}" fill="url(#revenueAreaGradient)" />
                                        <path d="{{ $chart['line_path'] }}" fill="none" stroke="#111111" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />

                                        @foreach ($chart['points'] as $point)
                                            <text x="{{ $point['x'] }}" y="{{ $chart['plot']['bottom'] + 19 }}" text-anchor="middle" font-size="9" fill="#9ca3af">{{ $point['label'] }}</text>
                                        @endforeach

                                        <circle cx="{{ $chart['last']['x'] }}" cy="{{ $chart['last']['y'] }}" r="3.2" fill="#111111" />

                                        <g>
                                            <rect x="{{ $chart['tooltip']['x'] }}" y="{{ $chart['tooltip']['y'] }}" width="{{ $chart['tooltip']['w'] }}" height="20" rx="3" fill="#111111" />
                                            <text x="{{ $chart['tooltip']['x'] + ($chart['tooltip']['w'] / 2) }}" y="{{ $chart['tooltip']['y'] + 13.5 }}" text-anchor="middle" font-size="9" font-weight="600" fill="#ffffff">{{ $chart['tooltip']['label'] }}</text>
                                        </g>
                                    </svg>
                                    @else
                                    <div class="flex h-[250px] w-full items-center justify-center rounded-md border border-dashed border-neutral-200 bg-[#fafafa]">
                                        <div class="text-center">
                                            <p class="text-xs font-semibold text-neutral-500">Belum ada data pendapatan</p>
                                            <p class="mt-1 text-[10px] text-neutral-400">Data akan muncul setelah ada transaksi penyewaan.</p>
                                        </div>
                                    </div>
                                    @endif
                                </div>

                                <aside class="rounded-lg border border-neutral-200 bg-white p-4">
                                    <div class="flex items-start gap-2.5">
                                        <span class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-md bg-[#f1f1f1] text-black">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 21V10l8-6 8 6v11M9 21v-6h6v6M3 10l1-4h16l1 4" />
                                            </svg>
                                        </span>
                                        <div>
                                            <h3 class="text-xs font-bold leading-snug text-black">Pengaturan Cepat Ketersediaan Toko</h3>
                                            <p class="mt-1 text-[10px] leading-relaxed text-neutral-500">Sinkronisasi status pemesanan instan pada sistem</p>
                                        </div>
                                    </div>

                                    <div class="mt-4 space-y-2.5">
                                        <div class="flex items-center justify-between gap-3 rounded-md bg-[#f1f1f1] px-3 py-2.5">
                                             <div class="min-w-0">
                                                 <p class="text-[11px] font-bold text-black">Mode Libur Toko</p>
                                                 <p class="mt-0.5 text-[9px] leading-relaxed text-neutral-500">{{ $maintenanceMode ? 'Toko sedang tutup sementara' : 'Tutup toko sementara' }}</p>
                                             </div>
                                             <button type="button" wire:click="toggleMaintenance" wire:loading.attr="disabled" class="relative inline-flex h-5 w-9 shrink-0 items-center rounded-full transition {{ $maintenanceMode ? 'bg-black' : 'bg-neutral-300' }}">
                                                 <span class="inline-block h-4 w-4 rounded-full bg-white shadow-xs transition {{ $maintenanceMode ? 'translate-x-[18px]' : 'translate-x-0.5' }}"></span>
                                             </button>
                                         </div>
                                         @error('maintenanceMode') <p class="text-[9px] text-red-600">{{ $message }}</p> @enderror
                                     </div>

                                     <div class="mt-4 flex items-center justify-between gap-3">
                                         <span class="flex items-center gap-1.5 text-[9px] font-medium text-black">
                                             <span class="h-1.5 w-1.5 rounded-full {{ $maintenanceMode ? 'bg-amber-500' : 'bg-black' }}"></span>
                                             {{ $maintenanceMode ? 'Toko Libur' : 'Sinkronisasi' }} &middot; Real-time
                                         </span>
                                         <button type="button" wire:click="savePreferences" class="rounded-md bg-[#e9e9e9] px-3 py-2 text-[10px] font-semibold text-black transition hover:bg-[#dedede]">Simpan Preferensi</button>
                                     </div>
                                </aside>
                            </div>
                        </div>
                    </section>
