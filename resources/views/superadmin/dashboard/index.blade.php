<div class="space-y-10 sm:space-y-12">
    <!-- 1. Header Halaman -->
    <div>
        <h1 class="text-2xl sm:text-3xl font-extrabold text-[#000000] tracking-tight">
            Dashboard MirrorCam
        </h1>
        <p class="text-xs sm:text-sm text-gray-500 mt-1 font-medium">
            Kelola dan pantau seluruh aktivitas sistem, tenant, pengguna, serta data operasional dalam satu tempat.
        </p>
    </div>

    <!-- 2. Baris 3 Kartu KPI -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-7 sm:gap-8">
        @include('superadmin.components.stat-card', [
            'title' => 'Total Tenant Mitra',
            'value' => number_format($kpi['totalTenant']),
            'trend' => $kpi['tenantGrowth'],
            'trendLabel' => 'Mitra Aktif',
        ])

        @include('superadmin.components.stat-card', [
            'title' => 'Total Pesanan Sewa',
            'value' => number_format($kpi['totalOrders']),
            'trend' => $kpi['ordersGrowth'],
            'trendLabel' => 'Transaksi',
        ])

        @include('superadmin.components.stat-card', [
            'title' => 'Pendapatan Komisi',
            'value' => $kpi['totalKomisiFormatted'],
            'trend' => $kpi['komisiGrowth'],
        ])
    </div>

    <!-- 3. Baris 2 Grafik (6 Bulan Terakhir) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-7 sm:gap-8">
        <!-- Grafik Kiri: Tren Pertumbuhan & Akusisi Mitra Tenant -->
        <div class="bg-[#ffffff] border border-[#e5e5e5] rounded-[4px] p-7 sm:p-8 shadow-xs flex flex-col justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-black shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    <h2 class="text-sm font-bold text-[#000000]">
                        Tren Pertumbuhan & Akusisi Mitra Tenant
                    </h2>
                </div>
                <p class="text-[11px] text-gray-500 mt-1">
                    Pertumbuhan pendaftaran mitra toko sewa 6 bulan terakhir
                </p>
            </div>

            <!-- Visual Bar Chart -->
            <div class="mt-8 pt-4">
                <div class="h-44 flex items-end justify-between gap-4 border-b border-gray-100 pb-2">
                    @foreach ($tenantGrowthChart['months'] as $month)
                        @php
                            $max = max(1, $tenantGrowthChart['maxValue']);
                            $heightAktif = max(10, min(100, round(($month['aktif'] / $max) * 100)));
                            $heightValidasi = max(6, min(80, round(($month['validasi'] / $max) * 80)));
                        @endphp
                        <div class="flex-1 flex flex-col items-center justify-end h-full group relative">
                            @if ($month['is_current'] && $month['aktif'] > 0)
                                <span class="text-[10px] font-bold text-black mb-1 bg-gray-100 px-1.5 py-0.5 rounded-[3px]">
                                    +{{ $month['aktif'] }}
                                </span>
                            @endif
                            <div class="w-full flex items-end justify-center gap-1.5 h-full">
                                <!-- Bar Aktif (Hitam) -->
                                <div
                                    class="w-3.5 sm:w-4 bg-[#000000] rounded-t-[2px] transition-all duration-300"
                                    style="height: {{ $heightAktif }}%;"
                                    title="Aktif: {{ $month['aktif'] }}"
                                ></div>
                                <!-- Bar Validasi (Kuning) -->
                                <div
                                    class="w-3.5 sm:w-4 bg-[#eab308] rounded-t-[2px] transition-all duration-300"
                                    style="height: {{ $heightValidasi }}%;"
                                    title="Validasi: {{ $month['validasi'] }}"
                                ></div>
                            </div>
                            <span class="text-[10px] text-gray-500 font-semibold mt-2 {{ $month['is_current'] ? 'text-black font-extrabold' : '' }}">
                                {{ $month['label'] }}
                            </span>
                        </div>
                    @endforeach
                </div>

                <!-- Legend -->
                <div class="flex items-center gap-5 mt-4 text-[11px] font-semibold text-gray-600">
                    <div class="flex items-center gap-1.5">
                        <span class="w-3 h-3 bg-[#000000] rounded-[2px]"></span>
                        <span>Aktif</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="w-3 h-3 bg-[#eab308] rounded-[2px]"></span>
                        <span>Validasi</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Grafik Kanan: Hasil Pendapatan Komisi Platform (10%) -->
        <div class="bg-[#ffffff] border border-[#e5e5e5] rounded-[4px] p-7 sm:p-8 shadow-xs flex flex-col justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-black shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                    <h2 class="text-sm font-bold text-[#000000]">
                        Hasil Pendapatan Komisi Platform (10%)
                    </h2>
                </div>
                <p class="text-[11px] text-gray-500 mt-1">
                    Realisasi GMV total booking vs Komisi bersih MirrorCam
                </p>
            </div>

            <!-- Visual Line / Area SVG Chart -->
            <div class="mt-8 pt-4">
                <div class="relative h-44 w-full flex flex-col justify-between">
                    <!-- Value Callout Badge Top Right -->
                    <div class="flex justify-end mb-1">
                        <span class="inline-flex items-center bg-[#000000] text-[#ffffff] px-2.5 py-1 rounded-[3px] text-[10px] font-bold shadow-xs">
                            {{ $commissionChart['latestFormatted'] }}
                        </span>
                    </div>

                    <!-- SVG Curve Area -->
                    <div class="w-full h-28 relative">
                        <svg class="w-full h-full overflow-visible" viewBox="0 0 500 120" preserveAspectRatio="none">
                            <defs>
                                <linearGradient id="commGrad" x1="0" y1="0" x2="0" y2="1">
                                    <stop offset="0%" stop-color="#000000" stop-opacity="0.18" />
                                    <stop offset="100%" stop-color="#000000" stop-opacity="0.01" />
                                </linearGradient>
                            </defs>
                            <!-- Background Grid Lines -->
                            <line x1="0" y1="20" x2="500" y2="20" stroke="#f3f4f6" stroke-dasharray="4 4"/>
                            <line x1="0" y1="70" x2="500" y2="70" stroke="#f3f4f6" stroke-dasharray="4 4"/>
                            <line x1="0" y1="119" x2="500" y2="119" stroke="#e5e5e5"/>

                            <!-- Area Fill -->
                            <path d="M 0 110 Q 100 100, 200 85 T 400 45 L 490 20 L 490 120 L 0 120 Z" fill="url(#commGrad)" />
                            <!-- Line Stroke -->
                            <path d="M 0 110 Q 100 100, 200 85 T 400 45 L 490 20" fill="none" stroke="#000000" stroke-width="2.5" stroke-linecap="round" />
                            <!-- Final dot -->
                            <circle cx="490" cy="20" r="4" fill="#000000" />
                        </svg>
                    </div>

                    <!-- X-Axis Labels -->
                    <div class="flex items-center justify-between text-[10px] text-gray-500 font-semibold pt-2 border-t border-gray-100">
                        @foreach ($commissionChart['months'] as $m)
                            <span class="{{ $m['is_current'] ? 'text-black font-extrabold' : '' }}">
                                {{ $m['label'] }}
                            </span>
                        @endforeach
                    </div>
                </div>

                <div class="flex items-center gap-2 mt-4 text-[11px] text-gray-400">
                    <span>* Diperbarui secara real-time dari seluruh transaksi booking berbayar.</span>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. Modul Pusat Aduan (Terhubung Langsung dengan pengaduan_pelanggans) -->
    <div class="bg-[#ffffff] border border-[#e5e5e5] rounded-[4px] p-7 sm:p-8 shadow-xs space-y-7 sm:space-y-8">
        <!-- Header Pusat Aduan: Judul (Kiri) dan Tabs Filter (Kanan) -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-gray-100 pb-4">
            <div>
                <h2 class="text-base sm:text-lg font-bold text-[#000000]">
                    Pusat Aduan
                </h2>
                <p class="text-xs text-gray-500 mt-0.5">
                    Laporan sengketa dan pengaduan pelanggaran dari pelanggan secara real-time.
                </p>
            </div>

            <!-- Filter Tabs -->
            <div class="flex items-center gap-1.5 bg-[#f9f9f9] p-1 border border-gray-200 rounded-[4px]">
                <button
                    type="button"
                    wire:click="setTab('semua')"
                    class="px-3 py-1.5 text-xs font-semibold rounded-[4px] transition {{ $activeComplaintTab === 'semua' ? 'bg-[#000000] text-[#ffffff] shadow-xs' : 'text-gray-600 hover:text-black' }}"
                >
                    Semua Aduan ({{ $complaintCounts['all'] }})
                </button>
                <button
                    type="button"
                    wire:click="setTab('perlu_ditangani')"
                    class="px-3 py-1.5 text-xs font-semibold rounded-[4px] transition {{ $activeComplaintTab === 'perlu_ditangani' ? 'bg-[#000000] text-[#ffffff] shadow-xs' : 'text-gray-600 hover:text-black' }}"
                >
                    Perlu Ditangani ({{ $complaintCounts['perlu'] }})
                </button>
                <button
                    type="button"
                    wire:click="setTab('selesai')"
                    class="px-3 py-1.5 text-xs font-semibold rounded-[4px] transition {{ $activeComplaintTab === 'selesai' ? 'bg-[#000000] text-[#ffffff] shadow-xs' : 'text-gray-600 hover:text-black' }}"
                >
                    Selesai ({{ $complaintCounts['selesai'] }})
                </button>
            </div>
        </div>

        <!-- Daftar Item Aduan -->
        @if ($complaints->isNotEmpty())
            <div class="space-y-4">
                @foreach ($complaints as $aduan)
                    @php
                        $isSelesai = strtolower($aduan->status_kasus ?? '') === 'selesai';
                        $cleanWa = preg_replace('/[^0-9]/', '', $aduan->user->no_whatsapp ?? '');
                        if (str_starts_with($cleanWa, '0')) {
                            $cleanWa = '62' . substr($cleanWa, 1);
                        }
                    @endphp
                    <div class="border border-[#e5e5e5] rounded-[4px] p-4 sm:p-5 hover:bg-[#fafafa] transition bg-[#ffffff]">
                        <!-- Baris Atas: Nomor Tiket & Status & Waktu -->
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-xs text-[#000000]">
                                    {{ $aduan->nomor_tiket ?: '#ADU-' . str_pad($aduan->id_pengaduan, 3, '0', STR_PAD_LEFT) }}
                                </span>

                                @if ($isSelesai)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-[4px] text-[10px] font-bold bg-[#fff700] text-[#000000]">
                                        Selesai
                                    </span>
                                @else
                                    <span class="w-2 h-2 rounded-full bg-[#f59e0b] animate-ping" title="Perlu Ditangani"></span>
                                    <span class="w-2 h-2 rounded-full bg-[#f59e0b] -ml-3"></span>
                                @endif
                            </div>

                            <span class="text-[11px] text-gray-400 font-medium">
                                {{ $aduan->created_at?->diffForHumans() }}
                            </span>
                        </div>

                        <!-- Isi Aduan (Kronologi) -->
                        <div class="mt-2.5">
                            <p class="text-xs text-gray-800 leading-relaxed font-medium italic">
                                "{{ $aduan->kronologi_kejadian ?: $aduan->kategori_pelanggaran }}"
                            </p>
                            @if (!empty($aduan->kategori_pelanggaran) && $aduan->kategori_pelanggaran !== $aduan->kronologi_kejadian)
                                <span class="inline-block mt-1 text-[10px] font-bold text-gray-500 uppercase tracking-wider">
                                    Kategori: {{ $aduan->kategori_pelanggaran }}
                                </span>
                            @endif
                        </div>

                        <!-- Identitas Pihak & Bukti -->
                        <div class="mt-3 text-[11px] text-gray-500 flex flex-wrap items-center justify-between gap-2">
                            <div>
                                <span class="font-medium">Pelapor:</span>
                                <span class="font-bold text-black">{{ $aduan->user->nama_lengkap_ktp ?? $aduan->user->name ?? 'Penyewa' }} (Penyewa)</span>
                                <span class="mx-1">&bull;</span>
                                <span class="font-medium">Terlapor:</span>
                                <span class="font-bold text-black">{{ $aduan->tenant->nama_toko ?? 'Mitra Tenant' }}</span>
                            </div>

                            @if (!empty($aduan->url_bukti_dokumen))
                                <a
                                    href="{{ asset('storage/' . $aduan->url_bukti_dokumen) }}"
                                    target="_blank"
                                    class="text-[11px] text-black underline font-semibold hover:opacity-80"
                                >
                                    Lihat Lampiran Bukti &rarr;
                                </a>
                            @endif
                        </div>

                        <!-- Baris Aksi / Resolusi -->
                        <div class="mt-4 pt-3 border-t border-gray-100 flex flex-wrap items-center justify-between gap-3">
                            @if ($isSelesai)
                                <div class="text-[11px] text-[#16a34a] font-semibold flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    <span>{{ $aduan->catatan_resolusi ?: 'Kasus Ditutup & Telah Diselesaikan' }}</span>
                                </div>
                            @else
                                <div class="flex items-center gap-2">
                                    @if (!empty($cleanWa))
                                        <a
                                            href="https://wa.me/{{ $cleanWa }}"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="inline-flex items-center gap-1.5 bg-[#000000] text-[#ffffff] px-3.5 py-1.5 rounded-[4px] text-xs font-semibold hover:bg-neutral-800 transition shadow-xs"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86.174.086.275.072.376-.044.101-.116.433-.506.549-.68.116-.173.231-.145.39-.086.159.058 1.011.477 1.184.564.173.087.289.13.332.203.043.072.043.419-.101.824z"/>
                                            </svg>
                                            <span>Hubungi Pelanggan Via WhatsApp</span>
                                        </a>
                                    @endif

                                    <button
                                        type="button"
                                        wire:click="openResolve({{ $aduan->id_pengaduan }}, '{{ $aduan->nomor_tiket ?: '#ADU-' . $aduan->id_pengaduan }}')"
                                        class="bg-[#000000] text-[#ffffff] px-4 py-1.5 rounded-[4px] text-xs font-semibold hover:bg-neutral-800 transition shadow-xs"
                                    >
                                        Selesai
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination Baris Bawah -->
            <div class="pt-4 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-gray-500">
                <span>
                    Menampilkan {{ $complaints->firstItem() ?? 0 }}-{{ $complaints->lastItem() ?? 0 }} dari {{ $complaints->total() }} laporan aduan
                </span>
                <div>
                    {{ $complaints->links('pagination::simple-tailwind') }}
                </div>
            </div>
        @else
            <!-- Empty State Komponen -->
            <div class="py-12 px-6 text-center bg-[#f9f9f9] border border-dashed border-gray-200 rounded-[4px]">
                <svg class="w-8 h-8 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <h3 class="text-xs font-bold text-black">Belum Ada Laporan Pengaduan</h3>
                <p class="text-[11px] text-gray-500 mt-1 max-w-sm mx-auto">
                    Seluruh pengaduan yang diajukan oleh pengguna penyewa di modul Pusat Pengaduan akan otomatis muncul di sini.
                </p>
            </div>
        @endif
    </div>

    <!-- Modal Form Penyelesaian Sengketa (SRS UC-25) -->
    @if ($resolveModalOpen)
        <div
            wire:click.self="closeResolve"
            class="fixed inset-0 z-50 overflow-y-auto bg-black/60 flex items-center justify-center p-4"
        >
            <div class="bg-[#ffffff] rounded-[4px] border border-[#e5e5e5] max-w-md w-full p-6 shadow-xl space-y-4">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <h3 class="text-sm font-bold text-[#000000]">
                        Penyelesaian Sengketa Aduan {{ $selectedComplaintTicket }}
                    </h3>
                    <button
                        type="button"
                        wire:click="closeResolve"
                        class="text-gray-400 hover:text-black font-bold text-sm"
                    >
                        &times;
                    </button>
                </div>

                <form wire:submit="resolveComplaint" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-black mb-1">
                            Keputusan Sengketa (Status Klaim)
                        </label>
                        <select
                            wire:model="keputusan"
                            class="w-full bg-[#ffffff] text-xs text-black border border-gray-300 rounded-[4px] p-2 focus:border-black focus:outline-hidden"
                        >
                            <option value="Selesai & Kasus Ditutup">Selesai & Kasus Ditutup</option>
                            <option value="Denda Keterlambatan Diberlakukan">Denda Keterlambatan Diberlakukan</option>
                            <option value="Klaim Penggantian Disetujui">Klaim Penggantian Disetujui</option>
                            <option value="Mediasi Damai Disepakati">Mediasi Damai Disepakati</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-black mb-1">
                            Justifikasi & Catatan Resolusi Tertulis (SRS UC-25) <span class="text-red-500">*</span>
                        </label>
                        <textarea
                            wire:model="justifikasi"
                            rows="3"
                            required
                            placeholder="Tuliskan ringkasan mediasi dan alasan keputusan yang dicatat ke audit log..."
                            class="w-full bg-[#ffffff] text-xs text-black border border-gray-300 rounded-[4px] p-2.5 focus:border-black focus:outline-hidden"
                        ></textarea>
                        @error('justifikasi')
                            <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-gray-100">
                        <button
                            type="button"
                            wire:click="closeResolve"
                            class="px-4 py-2 bg-[#d9d9d9] text-[#000000] rounded-[4px] text-xs font-semibold hover:bg-gray-300 transition"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            class="px-4 py-2 bg-[#000000] text-[#ffffff] rounded-[4px] text-xs font-semibold hover:bg-neutral-800 transition"
                        >
                            Simpan & Selesaikan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
