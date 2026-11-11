@extends('layouts.landing')

@section('title', 'Pusat Pengaduan & Klaim Sengketa Rental (Resolution Hub) — MirrorCam')

@section('content')
<div
    class="min-h-screen bg-[#f9f9f9] text-[#000000] pb-16"
    x-data="{
        activeModal: null,
        selectedTicket: null,
        sopModalOpen: false,
        messageModalOpen: false,
        uploadModalOpen: false,
        contactModalOpen: false,
        uploadedFiles: [],
        handleFileSelect(e) {
            const files = Array.from(e.target.files);
            this.uploadedFiles = files.map(f => f.name);
        },
        openDetail(ticket) {
            this.selectedTicket = ticket;
            this.activeModal = 'detail';
        },
        openMessage(ticket) {
            this.selectedTicket = ticket;
            this.messageModalOpen = true;
        },
        openUpload(ticket) {
            this.selectedTicket = ticket;
            this.uploadModalOpen = true;
        },
        openContact(ticket) {
            this.selectedTicket = ticket;
            this.contactModalOpen = true;
        },
        scrollToForm() {
            const el = document.getElementById('form-pengaduan-card');
            if (el) {
                el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                el.classList.add('ring-2', 'ring-black');
                setTimeout(() => el.classList.remove('ring-2', 'ring-black'), 2000);
            }
        }
    }"
>
    <!-- 1. TOP BREADCRUMB & STATUS BAR -->
    <div class="border-b border-[#e5e5e5] bg-[#ffffff]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
            <!-- Breadcrumb Navigation -->
            <nav class="flex items-center gap-2 text-gray-500 font-medium">
                <a href="{{ route('home') }}" class="hover:text-black transition">Beranda</a>
                <span>/</span>
                <span class="text-[#000000] font-bold">Pusat Resolusi & Pengaduan</span>
            </nav>

            <!-- Status Indicator Badge -->
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-[#dcfce7] border border-[#bbf7d0] text-[#16a34a] font-bold rounded-[4px] text-[11px]">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#16a34a] animate-pulse"></span>
                    <span>Sistem Mediasi Online Aktif</span>
                </span>
                <span class="hidden md:inline-block text-gray-300">|</span>
                <span class="hidden md:inline-block text-gray-500 font-medium">Protokol Keamanan & Escrow ISO-27001</span>
            </div>
        </div>
    </div>

    <!-- FLASH MESSAGES -->
    @if (session('status') || session('success'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6">
            <div class="bg-[#dcfce7] border border-[#86efac] text-[#16a34a] rounded-[4px] p-4 flex items-start justify-between gap-3 shadow-xs">
                <div class="flex items-center gap-2.5">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="text-xs font-bold">{{ session('status') ?? session('success') }}</span>
                </div>
                <button type="button" @click="$el.parentElement.remove()" class="text-[#16a34a] hover:opacity-75">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    @endif

    @if ($errors->any())
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6">
            <div class="bg-[#ffdad6] border border-[#fca5a5] text-[#ba1a1a] rounded-[4px] p-4 shadow-xs">
                <div class="flex items-center gap-2 mb-1.5">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span class="text-xs font-bold">Terdapat kesalahan pada input pengaduan:</span>
                </div>
                <ul class="list-disc list-inside text-xs space-y-0.5 pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <!-- 2. HEADER TITLE & ACTION BUTTONS -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 pb-6">
        <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-6">
            <div class="max-w-3xl space-y-2">
                <!-- Tag Pill -->
                <div class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-[#ffffff] border border-gray-300 rounded-[4px] text-[10px] font-extrabold uppercase tracking-widest text-[#000000] shadow-2xs">
                    <svg class="w-3.5 h-3.5 text-[#000000]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                    <span>MirrorCam Guarantee & Resolution Hub</span>
                </div>

                <h1 class="text-2xl sm:text-3xl lg:text-[34px] font-extrabold text-[#000000] tracking-tight leading-tight">
                    Pusat Pengaduan & Klaim Sengketa Rental
                </h1>
                <p class="text-xs sm:text-sm text-gray-600 leading-relaxed font-medium">
                    Layanan perlindungan penyewa dan mitra tenant MirrorCam. Selesaikan kendala unit rusak, deposit tertunda, ketidaksesuaian spesifikasi gear, atau keterlambatan pengembalian secara adil dan transparan.
                </p>
            </div>

            <!-- Action Buttons Top Right -->
            <div class="flex flex-wrap items-center gap-3 shrink-0">
                <button
                    type="button"
                    @click="scrollToForm()"
                    class="px-4 py-2.5 bg-[#000000] hover:bg-[#222222] text-[#ffffff] text-xs font-bold rounded-[4px] flex items-center gap-2 shadow-xs transition cursor-pointer"
                >
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>+ Buat Laporan Pengaduan Baru</span>
                </button>

                <button
                    type="button"
                    @click="sopModalOpen = true"
                    class="px-4 py-2.5 bg-[#d9d9d9] hover:bg-[#c9c9c9] text-[#000000] text-xs font-bold rounded-[4px] border border-gray-300 flex items-center gap-2 shadow-xs transition cursor-pointer"
                >
                    <svg class="w-4 h-4 text-[#000000]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span>Unduh Panduan Sengketa (SOP)</span>
                </button>
            </div>
        </div>
    </div>

    <!-- 3. STATISTIC CARDS ROW (DATA DINAMIS) -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Stat 1: Total Laporan -->
            <div class="bg-[#ffffff] border border-[#e5e5e5] rounded-[4px] p-5 shadow-xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between text-gray-500 mb-2">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-gray-500">Total Laporan Anda</span>
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div class="flex items-baseline justify-between gap-2 mt-1">
                        <span class="text-2xl font-extrabold text-[#000000]">{{ $totalLaporan }} Tiket</span>
                        <span class="text-[11px] text-gray-500 font-medium">Periode {{ now()->translatedFormat('M Y') }}</span>
                    </div>
                </div>
                <div class="mt-4 pt-2 border-t border-[#f0f0f0]">
                    <div class="w-full h-1 bg-[#000000] rounded-full"></div>
                </div>
            </div>

            <!-- Stat 2: Kasus Aktif -->
            <div class="bg-[#ffffff] border border-[#e5e5e5] rounded-[4px] p-5 shadow-xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between text-gray-500 mb-2">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-gray-500">Kasus Aktif</span>
                        <svg class="w-4 h-4 text-[#16a34a]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="flex items-center justify-between gap-2 mt-1">
                        <span class="text-2xl font-extrabold text-[#000000]">{{ $kasusAktif }} Kasus</span>
                        <span class="px-2 py-0.5 bg-[#dcfce7] text-[#16a34a] text-[10px] font-bold uppercase rounded-[4px] border border-[#bbf7d0]">Aktif</span>
                    </div>
                </div>
                <div class="mt-4 pt-2 border-t border-[#f0f0f0]">
                    <p class="text-[11px] text-gray-500 font-medium truncate">Dalam proses verifikasi tenant & tim QC</p>
                </div>
            </div>

            <!-- Stat 3: Kasus Selesai -->
            <div class="bg-[#ffffff] border border-[#e5e5e5] rounded-[4px] p-5 shadow-xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between text-gray-500 mb-2">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-gray-500">Kasus Selesai</span>
                        <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="flex items-center justify-between gap-2 mt-1">
                        <span class="text-2xl font-extrabold text-[#000000]">{{ $kasusSelesai }} Selesai</span>
                        <span class="px-2 py-0.5 bg-[#fff700] text-[#000000] text-[10px] font-bold uppercase rounded-[4px] border border-[#eab308]">Selesai</span>
                    </div>
                </div>
                <div class="mt-4 pt-2 border-t border-[#f0f0f0]">
                    <p class="text-[11px] text-gray-500 font-medium truncate">Refund disetujui & saldo kredit diterima</p>
                </div>
            </div>

            <!-- Stat 4: Perlu Tindakan Segera -->
            <div class="bg-[#ffffff] border border-[#e5e5e5] rounded-[4px] p-5 shadow-xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between text-gray-500 mb-2">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-gray-500">Perlu Tindakan Segera</span>
                        <svg class="w-4 h-4 text-[#ba1a1a]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div class="flex items-center justify-between gap-2 mt-1">
                        <span class="text-2xl font-extrabold text-[#ba1a1a]">{{ $perluTindakan }} Tiket</span>
                        <span class="px-2 py-0.5 bg-[#ffdad6] text-[#ba1a1a] text-[10px] font-bold uppercase rounded-[4px] border border-[#fca5a5]">Terlambat</span>
                    </div>
                </div>
                <div class="mt-4 pt-2 border-t border-[#f0f0f0]">
                    <p class="text-[11px] text-[#ba1a1a] font-semibold flex items-center gap-1 truncate">
                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Menunggu unggahan bukti video serah terima</span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. MAIN TWO-COLUMN CONTENT GRID (8:4 / 7:5) -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

            <!-- ============================================== -->
            <!-- KOLOM KIRI: DAFTAR TIKET PENGADUAN (LG:COL-7/8)-->
            <!-- ============================================== -->
            <div class="lg:col-span-7 xl:col-span-7 space-y-6">

                <!-- 1. Search & Filter Bar -->
                <div class="bg-[#ffffff] border border-[#e5e5e5] rounded-[4px] p-4 shadow-xs space-y-4">
                    <form action="{{ route('pengaduan.index') }}" method="GET" class="space-y-3">
                        <div class="grid grid-cols-1 sm:grid-cols-12 gap-2.5">
                            <!-- Input Search -->
                            <div class="sm:col-span-6 relative">
                                <input
                                    type="text"
                                    name="keyword"
                                    value="{{ $keyword }}"
                                    placeholder="Cari nomor tiket pengaduan, nama gear..."
                                    class="w-full pl-8 pr-3 py-2 text-xs bg-[#f9f9f9] border border-[#e5e5e5] rounded-[4px] text-[#000000] placeholder-gray-400 focus:ring-1 focus:ring-black focus:border-black transition"
                                />
                                <svg class="w-4 h-4 text-gray-400 absolute left-2.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>

                            <!-- Dropdown Kategori -->
                            <div class="sm:col-span-3">
                                <select
                                    name="kategori"
                                    onchange="this.form.submit()"
                                    class="w-full px-2.5 py-2 text-xs bg-[#f9f9f9] border border-[#e5e5e5] rounded-[4px] text-[#000000] focus:ring-1 focus:ring-black focus:border-black transition truncate"
                                >
                                    <option value="all">Semua Kategori</option>
                                    @foreach ($kategoriOptions as $opt)
                                        <option value="{{ $opt }}" {{ $currentKategori === $opt ? 'selected' : '' }}>
                                            {{ $opt }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Dropdown Sorting -->
                            <div class="sm:col-span-3">
                                <select
                                    name="sort"
                                    onchange="this.form.submit()"
                                    class="w-full px-2.5 py-2 text-xs bg-[#f9f9f9] border border-[#e5e5e5] rounded-[4px] text-[#000000] focus:ring-1 focus:ring-black focus:border-black transition truncate"
                                >
                                    <option value="terbaru" {{ $currentSort === 'terbaru' ? 'selected' : '' }}>Terbaru (Waktu Laporan)</option>
                                    <option value="terlama" {{ $currentSort === 'terlama' ? 'selected' : '' }}>Terlama</option>
                                </select>
                            </div>
                        </div>

                        <!-- Pill Tab Filters -->
                        <div class="flex items-center gap-2 pt-2 border-t border-[#f0f0f0] overflow-x-auto pb-1">
                            <input type="hidden" name="status" value="{{ $activeTab }}" id="status-tab-input" />

                            <!-- Semua Tab -->
                            <a
                                href="{{ route('pengaduan.index', array_merge(request()->query(), ['status' => 'semua'])) }}"
                                class="px-3 py-1.5 text-xs rounded-[4px] transition whitespace-nowrap {{ $activeTab === 'semua' ? 'bg-[#000000] text-white font-bold' : 'bg-[#f9f9f9] text-gray-700 hover:bg-gray-200 font-semibold' }}"
                            >
                                Semua ({{ $counts['semua'] }})
                            </a>

                            <!-- Aktif Tab -->
                            <a
                                href="{{ route('pengaduan.index', array_merge(request()->query(), ['status' => 'aktif'])) }}"
                                class="px-3 py-1.5 text-xs rounded-[4px] transition flex items-center gap-1.5 whitespace-nowrap {{ $activeTab === 'aktif' ? 'bg-[#dcfce7] text-[#16a34a] border border-[#86efac] font-bold' : 'bg-[#f9f9f9] text-gray-700 hover:bg-gray-200 font-semibold' }}"
                            >
                                <span class="w-1.5 h-1.5 rounded-full bg-[#16a34a]"></span>
                                <span>Aktif ({{ $counts['aktif'] }})</span>
                            </a>

                            <!-- Selesai Tab -->
                            <a
                                href="{{ route('pengaduan.index', array_merge(request()->query(), ['status' => 'selesai'])) }}"
                                class="px-3 py-1.5 text-xs rounded-[4px] transition flex items-center gap-1.5 whitespace-nowrap {{ $activeTab === 'selesai' ? 'bg-[#fff700] text-[#000000] border border-[#eab308] font-bold' : 'bg-[#f9f9f9] text-gray-700 hover:bg-gray-200 font-semibold' }}"
                            >
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                <span>Selesai ({{ $counts['selesai'] }})</span>
                            </a>

                            <!-- Terlambat Tab -->
                            <a
                                href="{{ route('pengaduan.index', array_merge(request()->query(), ['status' => 'terlambat'])) }}"
                                class="px-3 py-1.5 text-xs rounded-[4px] transition flex items-center gap-1.5 whitespace-nowrap {{ in_array($activeTab, ['terlambat', 'eskalasi']) ? 'bg-[#ffdad6] text-[#ba1a1a] border border-[#fca5a5] font-bold' : 'bg-[#f9f9f9] text-gray-700 hover:bg-gray-200 font-semibold' }}"
                            >
                                <span class="w-1.5 h-1.5 rounded-full bg-[#ba1a1a]"></span>
                                <span>Terlambat / Eskalasi ({{ $counts['terlambat'] }})</span>
                            </a>
                        </div>
                    </form>
                </div>

                <!-- 2. Dynamic Ticket Cards List -->
                <div class="space-y-4">
                    @forelse ($pengaduans as $item)
                        @php
                            $badge = $item->status_badge;
                            $normStatus = $item->status_normalized;
                            $product = $item->pemesanan?->produk;
                            $tenant = $item->tenant;
                            $booking = $item->pemesanan;
                        @endphp

                        <div class="bg-[#ffffff] border border-[#e5e5e5] rounded-[4px] p-5 shadow-xs transition hover:border-black/60 group">
                            <!-- Header Row: Ticket ID, Date, Tenant, Badge -->
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-[#f0f0f0] pb-3">
                                <div class="flex flex-wrap items-center gap-2 text-xs">
                                    <span class="font-extrabold text-[#000000] text-sm tracking-tight">
                                        Tiket #{{ $item->nomor_tiket }}
                                    </span>
                                    <span class="text-gray-400">•</span>
                                    <span class="text-gray-500">
                                        Tanggal Lapor: {{ $item->created_at->translatedFormat('d M Y (H:i T)') }}
                                    </span>
                                    <span class="text-gray-400">•</span>
                                    <span class="text-gray-700 font-semibold">
                                        Tenant: <span class="text-[#000000] font-bold underline">{{ $tenant?->nama_toko ?? 'Mitra MirrorCam' }}</span>
                                    </span>
                                </div>

                                <!-- Status Badge -->
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 text-[11px] font-extrabold uppercase rounded-[4px] border self-start sm:self-auto"
                                    style="background-color: {{ $badge['bg'] }}; color: {{ $badge['text'] }}; border-color: {{ $badge['border'] }};"
                                >
                                    {{ $badge['label'] }}
                                </span>
                            </div>

                            <!-- Product Info Box -->
                            <div class="mt-4 bg-[#f9f9f9] border border-[#e5e5e5] rounded-[4px] p-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-3.5">
                                <div class="flex items-center gap-3 min-w-0">
                                    <!-- Thumbnail -->
                                    <div class="w-14 h-14 bg-white border border-[#e5e5e5] rounded-[4px] overflow-hidden shrink-0 flex items-center justify-center p-1">
                                        @if ($product?->primary_image)
                                            <img src="{{ $product->primary_image }}" alt="{{ $product->nama_produk }}" class="w-full h-full object-cover rounded-[2px]" />
                                        @else
                                            <svg class="w-7 h-7 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                            </svg>
                                        @endif
                                    </div>

                                    <!-- Description -->
                                    <div class="min-w-0">
                                        <span class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider truncate">
                                            PERANGKAT TERKAIT PESANAN: {{ $booking?->sub_order_eksternal ?? ('INV-MC' . $item->id_pemesanan) }}
                                        </span>
                                        <h3 class="text-xs sm:text-sm font-extrabold text-[#000000] truncate">
                                            {{ $product?->nama_produk ?? 'Peralatan Multimedia MirrorCam' }}
                                        </h3>
                                        <p class="text-[11px] text-gray-500 font-medium truncate mt-0.5">
                                            Sewa Harian: Rp {{ number_format((float) ($booking?->harga_sewa_booking ?? $product?->harga_sewa ?? 250000), 0, ',', '.') }} / hari • Status: Terverifikasi Digital QC
                                        </p>
                                    </div>
                                </div>

                                <!-- Escrow / Claim Pill Right -->
                                <div class="shrink-0 text-left sm:text-right">
                                    @if ($normStatus === 'aktif')
                                        <span class="inline-block px-2.5 py-1 bg-[#ffffff] border border-gray-300 text-[11px] font-bold text-gray-700 rounded-[4px] shadow-2xs">
                                            Escrow: {{ $item->formatted_estimasi_klaim ?: 'Rp 1.500.000' }} Ditahan
                                        </span>
                                    @elseif ($normStatus === 'terlambat')
                                        <span class="inline-block px-2.5 py-1 bg-[#ffdad6] text-[#ba1a1a] text-[11px] font-bold rounded-[4px] border border-[#fca5a5]">
                                            {{ $item->status_klaim ?: 'Denda Dalam Mediasi' }}
                                        </span>
                                    @else
                                        <span class="inline-block px-2.5 py-1 bg-[#f0fdf4] text-[#16a34a] text-[11px] font-bold rounded-[4px] border border-[#bbf7d0]">
                                            Sengketa Ditutup
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <!-- Category & Chronology -->
                            <div class="mt-4 space-y-1.5">
                                <h4 class="text-xs font-bold text-[#000000] uppercase tracking-wide">
                                    KATEGORI: {{ $item->kategori_pelanggaran }}
                                </h4>
                                <p class="text-xs text-gray-700 leading-relaxed font-normal">
                                    {{ $item->kronologi_kejadian }}
                                </p>
                            </div>

                            <!-- SLA Warning / Resolution Message Box -->
                            @if ($normStatus === 'aktif' || $normStatus === 'terlambat')
                                <div class="mt-3.5 bg-[#fffbeb] border border-[#fde68a] rounded-[4px] p-3 text-xs text-[#B45309] flex items-start gap-2 shadow-2xs">
                                    <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        @if ($normStatus === 'terlambat')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                        @else
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        @endif
                                    </svg>
                                    <span class="font-medium leading-relaxed">
                                        @if ($item->pesan_peringatan)
                                            {{ $item->pesan_peringatan }}
                                        @elseif ($normStatus === 'terlambat')
                                            Peringatan: Tenggat waktu penyerahan bukti rekaman serah terima telah terlewat. Segera unggah bukti sebelum eskalasi otomatis ke tim penjaminan.
                                        @else
                                            Tenggat Waktu Tanggapan Tenant: {{ $item->sla_remaining ?: '03 Jam 45 Menit' }} (Sesuai SLA QC Digital)
                                        @endif
                                    </span>
                                </div>
                            @elseif ($normStatus === 'selesai')
                                <div class="mt-3.5 bg-[#f0fdf4] border border-[#bbf7d0] rounded-[4px] p-3 text-xs text-[#16a34a] flex items-start gap-2 shadow-2xs">
                                    <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span class="font-medium leading-relaxed">
                                        {{ $item->catatan_resolusi ?: 'Resolusi Sengketa: Kompensasi kredit sewa Rp 150.000 telah masuk ke saldo MirrorCam Anda.' }}
                                    </span>
                                </div>
                            @endif

                            <!-- Attachment Links -->
                            @if (!empty($item->bukti_files))
                                <div class="mt-3.5 flex flex-wrap items-center gap-2 pt-2 border-t border-[#f0f0f0]">
                                    <span class="text-[10px] font-bold text-gray-500 uppercase tracking-wider mr-1">BUKTI TERLAMPIR:</span>
                                    @foreach ($item->bukti_files as $file)
                                        <a
                                            href="{{ $file['url'] }}"
                                            target="_blank"
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-[#ffffff] hover:bg-[#f2f2f2] border border-gray-300 rounded-[4px] text-[11px] font-medium text-gray-700 shadow-2xs transition"
                                        >
                                            <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                @if ($file['is_video'])
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                                @else
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                @endif
                                            </svg>
                                            <span class="truncate max-w-[150px]">{{ $file['name'] }}</span>
                                        </a>
                                    @endforeach
                                </div>
                            @endif

                            <!-- Bottom Action Buttons Container -->
                            <div class="mt-4 pt-3 border-t border-[#e5e5e5] flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                @if ($normStatus === 'aktif')
                                    <div class="flex items-center gap-2">
                                        <button
                                            type="button"
                                            @click="openDetail({{ json_encode($item) }})"
                                            class="px-4 py-2 bg-[#d9d9d9] hover:bg-[#c9c9c9] text-[#000000] text-xs font-bold rounded-[4px] border border-gray-300 transition shadow-xs cursor-pointer"
                                        >
                                            Lihat Detail Kasus
                                        </button>
                                        <button
                                            type="button"
                                            @click="openMessage({{ json_encode($item) }})"
                                            class="px-4 py-2 bg-[#ffffff] hover:bg-[#f9f9f9] text-[#000000] text-xs font-bold rounded-[4px] border border-black transition shadow-xs cursor-pointer"
                                        >
                                            Kirim Pesan Mediator
                                        </button>
                                    </div>
                                    <span class="text-[11px] text-gray-500 font-medium">Status: Investigasi Berjalan</span>

                                @elseif ($normStatus === 'terlambat')
                                    <div class="text-[11px] text-[#ba1a1a] font-semibold">
                                        Status Klaim: {{ $item->status_klaim ?: 'Pending Upload Waybill / Resi Kurir' }}
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <button
                                            type="button"
                                            @click="openUpload({{ json_encode($item) }})"
                                            class="px-4 py-2 bg-[#d9d9d9] hover:bg-[#c9c9c9] text-[#000000] text-xs font-bold rounded-[4px] border border-gray-300 transition shadow-xs cursor-pointer"
                                        >
                                            Unggah Bukti Segera
                                        </button>
                                        <button
                                            type="button"
                                            @click="openContact({{ json_encode($item) }})"
                                            class="px-4 py-2 bg-[#ffffff] hover:bg-[#f9f9f9] text-[#000000] text-xs font-bold rounded-[4px] border border-black transition shadow-xs cursor-pointer"
                                        >
                                            Hubungi Tim Penengah
                                        </button>
                                    </div>

                                @else
                                    <!-- Selesai -->
                                    <div class="flex items-center gap-1.5 text-xs text-gray-700 font-semibold">
                                        <span>Evaluasi Kepuasan Mediasi:</span>
                                        <span class="font-bold text-amber-500">★ {{ $item->evaluasi_kepuasan ?: '5.0 / 5.0 (Puas)' }}</span>
                                    </div>
                                    <button
                                        type="button"
                                        @click="openDetail({{ json_encode($item) }})"
                                        class="px-4 py-2 bg-[#d9d9d9] hover:bg-[#c9c9c9] text-[#000000] text-xs font-bold rounded-[4px] border border-gray-300 transition shadow-xs cursor-pointer"
                                    >
                                        Lihat Rangkuman Hasil Sengketa
                                    </button>
                                @endif
                            </div>
                        </div>
                    @empty
                        <!-- Clean Empty State Component -->
                        <div class="bg-[#ffffff] border border-[#e5e5e5] rounded-[4px] p-8 sm:p-12 text-center shadow-xs">
                            <div class="w-16 h-16 bg-[#f9f9f9] border border-gray-200 rounded-full flex items-center justify-center mx-auto mb-4 text-[#000000]">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                            </div>
                            <h3 class="text-base font-extrabold text-[#000000]">Belum Ada Riwayat Pengaduan & Klaim Sengketa</h3>
                            <p class="text-xs text-gray-500 max-w-md mx-auto mt-1.5 leading-relaxed">
                                Seluruh transaksi sewa peralatan kamera dan studio Anda dalam keadaan aman. Jika Anda mengalami kendala fisik gear atau deposit, formulir di samping siap memfasilitasi rekonsiliasi.
                            </p>
                            <div class="mt-5">
                                <button
                                    type="button"
                                    @click="scrollToForm()"
                                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#000000] hover:bg-[#222222] text-[#ffffff] text-xs font-bold rounded-[4px] transition shadow-xs cursor-pointer"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                    <span>+ Ajukan Pengaduan Sekarang</span>
                                </button>
                            </div>
                        </div>
                    @endforelse
                </div>

                <!-- 3. Pagination Section -->
                <div class="mt-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4 text-xs text-gray-600 pt-3 border-t border-[#e5e5e5]">
                    <span class="font-medium">
                        Menampilkan {{ $pengaduans->firstItem() ?? 0 }} dari {{ $pengaduans->total() }} pengaduan terdaftar di akun {{ $user->name }}
                    </span>

                    @if ($pengaduans->hasPages())
                        <div class="flex items-center gap-1.5">
                            {{-- Previous Page Link --}}
                            @if ($pengaduans->onFirstPage())
                                <span class="px-3 py-1.5 bg-gray-100 text-gray-400 rounded-[4px] border border-gray-200 cursor-not-allowed">Sebelumnya</span>
                            @else
                                <a href="{{ $pengaduans->previousPageUrl() }}" class="px-3 py-1.5 bg-white text-black hover:bg-gray-100 rounded-[4px] border border-gray-300 font-semibold transition">Sebelumnya</a>
                            @endif

                            {{-- Current Page --}}
                            <span class="px-3 py-1.5 bg-black text-white font-bold rounded-[4px]">{{ $pengaduans->currentPage() }}</span>

                            {{-- Next Page Link --}}
                            @if ($pengaduans->hasMorePages())
                                <a href="{{ $pengaduans->nextPageUrl() }}" class="px-3 py-1.5 bg-white text-black hover:bg-gray-100 rounded-[4px] border border-gray-300 font-semibold transition">Selanjutnya</a>
                            @else
                                <span class="px-3 py-1.5 bg-gray-100 text-gray-400 rounded-[4px] border border-gray-200 cursor-not-allowed">Selanjutnya</span>
                            @endif
                        </div>
                    @endif
                </div>
            </div>


            <!-- ============================================== -->
            <!-- KOLOM KANAN: FORM & INFORMASI SOP (LG:COL-5/4)-->
            <!-- ============================================== -->
            <div class="lg:col-span-5 xl:col-span-5 space-y-6">

                <!-- 1. FORM AJUKAN PENGADUAN RENTAL -->
                <div id="form-pengaduan-card" class="bg-[#ffffff] border border-[#e5e5e5] rounded-[4px] p-6 shadow-xs transition">
                    <!-- Form Header -->
                    <div class="flex items-center gap-2.5 pb-4 border-b border-[#e5e5e5] mb-5">
                        <div class="w-8 h-8 rounded-[4px] bg-[#000000] text-white flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-sm sm:text-base font-extrabold text-[#000000] tracking-tight">Ajukan Pengaduan Rental</h2>
                            <p class="text-[11px] text-gray-500 font-medium">Bantuan resmi resolusi sengketa unit & sewa</p>
                        </div>
                    </div>

                    <!-- Submission Form -->
                    <form action="{{ route('pengaduan.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                        @csrf

                        <!-- Field 1: Pilih Nomor Pesanan / Gear -->
                        <div class="space-y-1">
                            <label class="block text-xs font-bold text-[#000000]">
                                Pilih Nomor Pesanan / Gear <span class="text-red-500">*</span>
                            </label>
                            <select
                                name="id_pemesanan"
                                required
                                class="w-full px-3 py-2 text-xs bg-[#ffffff] border border-[#e5e5e5] rounded-[4px] text-[#000000] focus:ring-1 focus:ring-black focus:border-black transition"
                            >
                                <option value="" disabled selected>-- Pilih Pesanan Terdaftar --</option>
                                @forelse ($userBookings as $booking)
                                    <option value="{{ $booking->id_pemesanan }}" {{ old('id_pemesanan') == $booking->id_pemesanan ? 'selected' : '' }}>
                                        {{ $booking->sub_order_eksternal ?: ('INV-' . $booking->id_pemesanan) }}: {{ $booking->product_name }} — {{ $booking->tenant_name }}
                                    </option>
                                @empty
                                    <option value="" disabled>Belum ada riwayat sewa aktif/selesai pada akun ini</option>
                                @endforelse
                            </select>
                        </div>

                        <!-- Field 2: Kategori Kendala -->
                        <div class="space-y-1">
                            <label class="block text-xs font-bold text-[#000000]">
                                Kategori Kendala <span class="text-red-500">*</span>
                            </label>
                            <select
                                name="kategori_pelanggaran"
                                required
                                class="w-full px-3 py-2 text-xs bg-[#ffffff] border border-[#e5e5e5] rounded-[4px] text-[#000000] focus:ring-1 focus:ring-black focus:border-black transition"
                            >
                                <option value="" disabled selected>-- Pilih Jenis Kendala --</option>
                                @foreach ($kategoriOptions as $opt)
                                    <option value="{{ $opt }}" {{ old('kategori_pelanggaran') === $opt ? 'selected' : '' }}>
                                        {{ $opt }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Field 3: Tenggat Waktu Kejadian -->
                        <div class="space-y-1">
                            <label class="block text-xs font-bold text-[#000000]">
                                Tenggat Waktu Kejadian <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="datetime-local"
                                name="waktu_kejadian"
                                value="{{ old('waktu_kejadian', now()->format('Y-m-d\TH:i')) }}"
                                required
                                class="w-full px-3 py-2 text-xs bg-[#ffffff] border border-[#e5e5e5] rounded-[4px] text-[#000000] focus:ring-1 focus:ring-black focus:border-black transition"
                            />
                        </div>

                        <!-- Field 4: Unggah Foto / Video Bukti (Drag & Drop Zone) -->
                        <div class="space-y-1">
                            <label class="block text-xs font-bold text-[#000000]">
                                Unggah Foto / Video Bukti <span class="text-red-500">*</span>
                            </label>
                            <div
                                class="border-2 border-dashed border-[#d1d5db] hover:border-black rounded-[4px] p-5 text-center bg-[#f9f9f9] transition relative cursor-pointer"
                                @click="$refs.fileInput.click()"
                            >
                                <input
                                    type="file"
                                    name="bukti_dokumen[]"
                                    multiple
                                    accept="image/*,video/mp4,video/quicktime,.raw,.pdf"
                                    x-ref="fileInput"
                                    @change="handleFileSelect($event)"
                                    class="hidden"
                                />

                                <div class="w-10 h-10 rounded-[4px] bg-[#ffffff] border border-[#e5e5e5] flex items-center justify-center mx-auto mb-2 text-gray-700 shadow-2xs">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                    </svg>
                                </div>

                                <p class="text-xs font-bold text-[#000000]">Seret berkas atau Klik untuk Memilih</p>
                                <p class="text-[10px] text-gray-500 mt-0.5">Maks 5 File, MP4/JPG/PNG/RAW max 25MB</p>

                                <!-- Uploaded files list preview -->
                                <template x-if="uploadedFiles.length > 0">
                                    <div class="mt-3 pt-2 border-t border-gray-200 text-left space-y-1">
                                        <span class="text-[10px] font-bold text-gray-500 uppercase">Berkas Terpilih:</span>
                                        <template x-for="(file, idx) in uploadedFiles" :key="idx">
                                            <div class="flex items-center gap-1.5 text-[11px] text-gray-800 font-medium">
                                                <svg class="w-3.5 h-3.5 text-green-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                </svg>
                                                <span class="truncate" x-text="file"></span>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Field 5: Rincian Kronologi Masalah -->
                        <div class="space-y-1">
                            <label class="block text-xs font-bold text-[#000000]">
                                Rincian Kronologi Masalah <span class="text-red-500">*</span>
                            </label>
                            <textarea
                                name="kronologi_kejadian"
                                rows="4"
                                required
                                placeholder="Jelaskan secara rinci kronologi kendala, kondisi alat saat unboxing/serah terima, dan bukti komunikasi dengan tenant terkait..."
                                class="w-full px-3 py-2 text-xs bg-[#ffffff] border border-[#e5e5e5] rounded-[4px] text-[#000000] placeholder-gray-400 focus:ring-1 focus:ring-black focus:border-black transition"
                            >{{ old('kronologi_kejadian') }}</textarea>
                        </div>

                        <!-- Warning Disclaimer Box -->
                        <div class="bg-[#fffbeb] border border-[#fde68a] rounded-[4px] p-3 text-xs text-[#B45309] flex items-start gap-2">
                            <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="leading-relaxed font-medium">
                                Catatan: Pengaduan wajib diajukan maksimal 1x24 jam sejak serah terima gear.
                            </span>
                        </div>

                        <!-- Submit Button -->
                        <button
                            type="submit"
                            class="w-full py-3 bg-[#d9d9d9] hover:bg-[#c9c9c9] text-[#000000] text-xs sm:text-sm font-extrabold rounded-[4px] border border-gray-300 shadow-xs transition flex items-center justify-center gap-2 cursor-pointer"
                        >
                            <svg class="w-4 h-4 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                            <span>Kirim Pengaduan Sekarang</span>
                        </button>
                    </form>
                </div>


                <!-- 2. CARD SOP & REGULASI SENGKETA -->
                <div class="bg-[#ffffff] border border-[#e5e5e5] rounded-[4px] p-6 shadow-xs space-y-5">
                    <!-- Card Header -->
                    <div class="flex items-center gap-2.5 pb-3 border-b border-[#e5e5e5]">
                        <div class="w-8 h-8 rounded-[4px] bg-[#000000] text-white flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-extrabold text-[#000000] tracking-tight">SOP & Regulasi Sengketa</h3>
                            <p class="text-[11px] text-gray-500 font-medium">Panduan resmi mediasi sengketa sewa</p>
                        </div>
                    </div>

                    <!-- SOP Step 1 -->
                    <div class="flex items-start gap-3">
                        <div class="w-6 h-6 rounded-[4px] bg-[#000000] text-white text-xs font-bold flex items-center justify-center shrink-0 mt-0.5">
                            1
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-[#000000]">Investigasi Objektif Digital</h4>
                            <p class="text-[11px] text-gray-600 leading-relaxed mt-0.5">
                                Investigasi objektif berdasarkan Digital Checklist sebelum serah terima (foto metadata, serial number, stiker seal).
                            </p>
                        </div>
                    </div>

                    <!-- SOP Step 2 -->
                    <div class="flex items-start gap-3">
                        <div class="w-6 h-6 rounded-[4px] bg-[#000000] text-white text-xs font-bold flex items-center justify-center shrink-0 mt-0.5">
                            2
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-[#000000]">Perlindungan Dana Escrow</h4>
                            <p class="text-[11px] text-gray-600 leading-relaxed mt-0.5">
                                Dana deposit ditahan di Escrow Rekening Bersama MirrorCam selama proses mediasi berlangsung dan tidak akan cair tanpa persetujuan kedua pihak.
                            </p>
                        </div>
                    </div>

                    <!-- SOP Step 3 -->
                    <div class="flex items-start gap-3">
                        <div class="w-6 h-6 rounded-[4px] bg-[#000000] text-white text-xs font-bold flex items-center justify-center shrink-0 mt-0.5">
                            3
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-[#000000]">Batas Waktu Mediasi Mengikat</h4>
                            <p class="text-[11px] text-gray-600 leading-relaxed mt-0.5">
                                Waktu mediasi maksimal 2x24 jam kerja sebelum keputusan mengikat dikeluarkan oleh komite independen MirrorCam.
                            </p>
                        </div>
                    </div>

                    <!-- Call Center / Hotline Box -->
                    <div class="bg-[#f9f9f9] border border-[#e5e5e5] rounded-[4px] p-4 space-y-3">
                        <div>
                            <span class="text-[10px] font-extrabold text-gray-500 uppercase tracking-widest block mb-1">
                                PUSAT KRISIS PRODUKSI
                            </span>
                            <p class="text-xs text-gray-700">
                                Perlu bantuan darurat? Hubungi <span class="font-bold text-[#000000]">Hotline Sengketa 24/7 (021-8899-772)</span>
                            </p>
                        </div>

                        <div class="grid grid-cols-2 gap-2 pt-1">
                            <a
                                href="tel:0218899772"
                                class="py-2 px-2.5 bg-[#ffffff] hover:bg-gray-100 border border-gray-300 rounded-[4px] text-[11px] font-bold text-[#000000] flex items-center justify-center gap-1.5 transition shadow-2xs"
                            >
                                <svg class="w-3.5 h-3.5 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                </svg>
                                <span>Telepon Sekarang</span>
                            </a>

                            <a
                                href="https://wa.me/6281234567890?text=Halo%20Duty%20Manager%20MirrorCam,%20saya%20memerlukan%20bantuan%20sengketa%20rental"
                                target="_blank"
                                class="py-2 px-2.5 bg-[#ffffff] hover:bg-gray-100 border border-gray-300 rounded-[4px] text-[11px] font-bold text-[#000000] flex items-center justify-center gap-1.5 transition shadow-2xs"
                            >
                                <svg class="w-3.5 h-3.5 text-green-600" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86.173.086.275.072.376-.044.101-.116.433-.506.549-.68.116-.173.231-.145.39-.087s1.011.477 1.184.564.289.13.332.202c.043.073.043.419-.101.824z"/>
                                </svg>
                                <span>WhatsApp Manager</span>
                            </a>
                        </div>
                    </div>

                    <!-- Bottom Assurance Badge -->
                    <div class="flex items-center gap-3 pt-2">
                        <div class="w-8 h-8 rounded-full bg-[#f9f9f9] border border-gray-200 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                        <div>
                            <span class="text-xs font-extrabold text-[#000000] block">100% Proteksi Sengketa Terpadu</span>
                            <span class="text-[11px] text-gray-500 font-medium">Semua sewa dilindungi jaminan keabsahan inventaris.</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>


    <!-- ============================================== -->
    <!-- INTERACTIVE MODALS (ALPINE.JS DRIVEN)          -->
    <!-- ============================================== -->

    <!-- 1. DETAIL KASUS MODAL -->
    <div
        x-show="activeModal === 'detail'"
        x-cloak
        class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-xs flex items-center justify-center p-4"
        @keydown.escape.window="activeModal = null"
    >
        <div
            class="bg-[#ffffff] border border-[#e5e5e5] rounded-[4px] max-w-2xl w-full p-6 shadow-xl relative"
            @click.away="activeModal = null"
        >
            <!-- Header -->
            <div class="flex items-center justify-between pb-4 border-b border-[#e5e5e5]">
                <div>
                    <h3 class="text-base font-extrabold text-[#000000]">
                        Rincian Kasus <span x-text="selectedTicket ? '#' + selectedTicket.nomor_tiket : ''"></span>
                    </h3>
                    <p class="text-xs text-gray-500">Status Penyelesaian dan Mediasi Sengketa</p>
                </div>
                <button
                    type="button"
                    @click="activeModal = null"
                    class="w-7 h-7 rounded-[4px] bg-gray-100 hover:bg-gray-200 flex items-center justify-center text-gray-700 transition"
                >
                    ✕
                </button>
            </div>

            <!-- Modal Content -->
            <template x-if="selectedTicket">
                <div class="mt-4 space-y-4 text-xs">
                    <!-- Status & Escrow Bar -->
                    <div class="grid grid-cols-2 gap-3 p-3 bg-[#f9f9f9] border border-[#e5e5e5] rounded-[4px]">
                        <div>
                            <span class="text-[10px] font-bold text-gray-500 uppercase block">Status Kasus:</span>
                            <span class="font-extrabold text-sm" x-text="selectedTicket.status_kasus"></span>
                        </div>
                        <div>
                            <span class="text-[10px] font-bold text-gray-500 uppercase block">Estimasi Klaim / Escrow:</span>
                            <span class="font-extrabold text-sm text-[#000000]" x-text="selectedTicket.estimasi_klaim ? 'Rp ' + Number(selectedTicket.estimasi_klaim).toLocaleString('id-ID') : 'Rp 1.500.000'"></span>
                        </div>
                    </div>

                    <!-- Category & Chronology -->
                    <div class="space-y-1">
                        <span class="text-[10px] font-bold text-gray-500 uppercase block">Kategori Kendala:</span>
                        <p class="font-bold text-gray-900" x-text="selectedTicket.kategori_pelanggaran"></p>
                    </div>

                    <div class="space-y-1">
                        <span class="text-[10px] font-bold text-gray-500 uppercase block">Kronologi Kejadian:</span>
                        <p class="text-gray-700 leading-relaxed bg-[#f9f9f9] p-3 rounded-[4px] border border-[#e5e5e5]" x-text="selectedTicket.kronologi_kejadian"></p>
                    </div>

                    <!-- Warning or Resolution text -->
                    <template x-if="selectedTicket.catatan_resolusi">
                        <div class="p-3 bg-[#f0fdf4] border border-[#bbf7d0] rounded-[4px] text-[#16a34a]">
                            <span class="font-bold block mb-0.5">Hasil / Catatan Resolusi:</span>
                            <span x-text="selectedTicket.catatan_resolusi"></span>
                        </div>
                    </template>

                    <!-- Bukti -->
                    <template x-if="selectedTicket.bukti_files && selectedTicket.bukti_files.length > 0">
                        <div class="space-y-1.5 pt-2 border-t border-gray-200">
                            <span class="text-[10px] font-bold text-gray-500 uppercase block">Berkas Bukti Digital:</span>
                            <div class="flex flex-wrap gap-2">
                                <template x-for="(f, i) in selectedTicket.bukti_files" :key="i">
                                    <a :href="f.url" target="_blank" class="px-2.5 py-1 bg-gray-100 hover:bg-gray-200 border border-gray-300 rounded-[4px] text-black font-semibold text-[11px] flex items-center gap-1.5">
                                        <span>📎</span>
                                        <span x-text="f.name"></span>
                                    </a>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </template>

            <!-- Modal Footer -->
            <div class="mt-6 pt-3 border-t border-[#e5e5e5] flex justify-end gap-2">
                <button
                    type="button"
                    @click="activeModal = null"
                    class="px-4 py-2 bg-[#d9d9d9] hover:bg-[#c9c9c9] text-black text-xs font-bold rounded-[4px] border border-gray-300 transition"
                >
                    Tutup
                </button>
            </div>
        </div>
    </div>


    <!-- 2. SOP MODAL -->
    <div
        x-show="sopModalOpen"
        x-cloak
        class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-xs flex items-center justify-center p-4"
        @keydown.escape.window="sopModalOpen = false"
    >
        <div
            class="bg-[#ffffff] border border-[#e5e5e5] rounded-[4px] max-w-2xl w-full p-6 shadow-xl relative"
            @click.away="sopModalOpen = false"
        >
            <div class="flex items-center justify-between pb-4 border-b border-[#e5e5e5]">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 bg-black text-white rounded-[4px] flex items-center justify-center text-xs font-bold">
                        SOP
                    </div>
                    <div>
                        <h3 class="text-sm sm:text-base font-extrabold text-[#000000]">
                            Buku Panduan Standar Operasional Mediasi Sengketa
                        </h3>
                        <p class="text-[11px] text-gray-500">Regulasi Perlindungan Transaksi Sewa MirrorCam v2.4</p>
                    </div>
                </div>
                <button
                    type="button"
                    @click="sopModalOpen = false"
                    class="w-7 h-7 rounded-[4px] bg-gray-100 hover:bg-gray-200 flex items-center justify-center text-gray-700 transition"
                >
                    ✕
                </button>
            </div>

            <div class="mt-4 space-y-3.5 text-xs text-gray-700 max-h-[60vh] overflow-y-auto pr-1">
                <div class="p-3 bg-[#f9f9f9] border border-gray-200 rounded-[4px] space-y-1">
                    <h4 class="font-extrabold text-black">Pasal 1: Batas Waktu Pelaporan 1x24 Jam</h4>
                    <p class="leading-relaxed">
                        Penyewa wajib melakukan inspeksi fisik, kelengkapan lensa, dan fungsi sensor saat serah terima. Komplain cacat fisik tidak berlaku jika dilaporkan melebihi 24 jam sejak konfirmasi serah terima kurir atau logistik resmi.
                    </p>
                </div>

                <div class="p-3 bg-[#f9f9f9] border border-gray-200 rounded-[4px] space-y-1">
                    <h4 class="font-extrabold text-black">Pasal 2: Penahanan Dana Escrow & Jaminan Sewa</h4>
                    <p class="leading-relaxed">
                        Seluruh dana pembayaran sewa dan deposit berada di rekening penampungan bersama MirrorCam. Apabila terjadi tiket sengketa aktif, pencairan ke pihak tenant otomatis dibekukan hingga ada keputusan komite independen.
                    </p>
                </div>

                <div class="p-3 bg-[#f9f9f9] border border-gray-200 rounded-[4px] space-y-1">
                    <h4 class="font-extrabold text-black">Pasal 3: Uji Forensik Metadata Foto & Video Serah Terima</h4>
                    <p class="leading-relaxed">
                        Penyelesaian kendala optik/sensor menggunakan perbandingan metadata file RAW uji coba unboxing dengan log checklist inspeksi QC digital tenant saat pra-keberangkatan alat.
                    </p>
                </div>

                <div class="p-3 bg-[#f9f9f9] border border-gray-200 rounded-[4px] space-y-1">
                    <h4 class="font-extrabold text-black">Pasal 4: Kompensasi Penggantian & Pengembalian Dana</h4>
                    <p class="leading-relaxed">
                        Jika alat terbukti cacat dari tenant dan tidak dapat digunakan untuk produksi, penyewa berhak mendapatkan unit pengganti sejenis dalam 4 jam atau pengembalian saldo kredit 100% ditambah kompensasi operasional.
                    </p>
                </div>
            </div>

            <div class="mt-5 pt-3 border-t border-[#e5e5e5] flex flex-wrap items-center justify-between gap-3">
                <span class="text-[11px] text-gray-500">Terdaftar di Kementerian Hukum & HAM RI</span>
                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        onclick="window.print()"
                        class="px-4 py-2 bg-black text-white hover:bg-gray-800 text-xs font-bold rounded-[4px] transition flex items-center gap-1.5"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        <span>Cetak Dokumen SOP</span>
                    </button>
                    <button
                        type="button"
                        @click="sopModalOpen = false"
                        class="px-4 py-2 bg-[#d9d9d9] hover:bg-[#c9c9c9] text-black text-xs font-bold rounded-[4px] border border-gray-300 transition"
                    >
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>


    <!-- 3. KIRIM PESAN MEDIATOR MODAL -->
    <div
        x-show="messageModalOpen"
        x-cloak
        class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-xs flex items-center justify-center p-4"
        @keydown.escape.window="messageModalOpen = false"
    >
        <div
            class="bg-[#ffffff] border border-[#e5e5e5] rounded-[4px] max-w-lg w-full p-6 shadow-xl relative"
            @click.away="messageModalOpen = false"
        >
            <div class="flex items-center justify-between pb-3 border-b border-[#e5e5e5]">
                <div>
                    <h3 class="text-sm font-extrabold text-[#000000]">Kirim Pesan ke Tim Mediator MirrorCam</h3>
                    <p class="text-[11px] text-gray-500" x-text="selectedTicket ? 'Tiket #' + selectedTicket.nomor_tiket : ''"></p>
                </div>
                <button
                    type="button"
                    @click="messageModalOpen = false"
                    class="w-7 h-7 rounded-[4px] bg-gray-100 hover:bg-gray-200 flex items-center justify-center text-gray-700 transition"
                >
                    ✕
                </button>
            </div>

            <div class="mt-4 space-y-3 text-xs">
                <div class="p-3 bg-[#f9f9f9] border border-gray-200 rounded-[4px]">
                    <span class="font-bold block text-gray-900 mb-1">Mediator Bertugas: Tim Resolusi QC MirrorCam</span>
                    <p class="text-gray-600">Pesan Anda akan dicatat dalam berkas audit sengketa resmi dan diteruskan ke pihak tenant.</p>
                </div>

                <div class="space-y-1">
                    <label class="font-bold text-gray-900 block">Isi Pesan / Klarifikasi Tambahan:</label>
                    <textarea
                        rows="4"
                        placeholder="Tuliskan keterangan tambahan atau tanggapan untuk tenant dan mediator..."
                        class="w-full p-2.5 text-xs border border-gray-300 rounded-[4px] focus:ring-1 focus:ring-black focus:border-black"
                    ></textarea>
                </div>
            </div>

            <div class="mt-5 pt-3 border-t border-[#e5e5e5] flex justify-end gap-2">
                <button
                    type="button"
                    @click="messageModalOpen = false"
                    class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-black text-xs font-bold rounded-[4px] transition"
                >
                    Batal
                </button>
                <button
                    type="button"
                    @click="alert('Pesan mediasi Anda telah terkirim ke panel moderator.'); messageModalOpen = false;"
                    class="px-4 py-2 bg-black hover:bg-gray-800 text-white text-xs font-bold rounded-[4px] transition"
                >
                    Kirim Pesan
                </button>
            </div>
        </div>
    </div>


    <!-- 4. UNGGAH BUKTI SEGERA MODAL -->
    <div
        x-show="uploadModalOpen"
        x-cloak
        class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-xs flex items-center justify-center p-4"
        @keydown.escape.window="uploadModalOpen = false"
    >
        <div
            class="bg-[#ffffff] border border-[#e5e5e5] rounded-[4px] max-w-lg w-full p-6 shadow-xl relative"
            @click.away="uploadModalOpen = false"
        >
            <div class="flex items-center justify-between pb-3 border-b border-[#e5e5e5]">
                <div>
                    <h3 class="text-sm font-extrabold text-[#ba1a1a]">Unggah Bukti Segera (Eskalasi Terlambat)</h3>
                    <p class="text-[11px] text-gray-500" x-text="selectedTicket ? 'Tiket #' + selectedTicket.nomor_tiket : ''"></p>
                </div>
                <button
                    type="button"
                    @click="uploadModalOpen = false"
                    class="w-7 h-7 rounded-[4px] bg-gray-100 hover:bg-gray-200 flex items-center justify-center text-gray-700 transition"
                >
                    ✕
                </button>
            </div>

            <div class="mt-4 space-y-3 text-xs">
                <div class="p-3 bg-[#ffdad6] border border-[#fca5a5] rounded-[4px] text-[#ba1a1a]">
                    <span class="font-bold block mb-1">Perhatian Penting:</span>
                    <p>Unggah foto resi kurir, waybill pengiriman kembali, atau video penyerahan gear ke tenant sebelum batas waktu eskalasi berakhir.</p>
                </div>

                <div class="border-2 border-dashed border-gray-300 rounded-[4px] p-6 text-center bg-gray-50">
                    <svg class="w-8 h-8 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                    </svg>
                    <span class="text-xs font-bold text-black block">Pilih Berkas Resi / Video Bukti</span>
                    <span class="text-[10px] text-gray-500 block mt-0.5">Format JPG, PNG, PDF max 20MB</span>
                    <input type="file" class="mt-3 text-xs text-gray-600 block w-full" />
                </div>
            </div>

            <div class="mt-5 pt-3 border-t border-[#e5e5e5] flex justify-end gap-2">
                <button
                    type="button"
                    @click="uploadModalOpen = false"
                    class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-black text-xs font-bold rounded-[4px] transition"
                >
                    Batal
                </button>
                <button
                    type="button"
                    @click="alert('Bukti susulan Anda telah berhasil diunggah.'); uploadModalOpen = false;"
                    class="px-4 py-2 bg-black hover:bg-gray-800 text-white text-xs font-bold rounded-[4px] transition"
                >
                    Kirim Bukti Sekarang
                </button>
            </div>
        </div>
    </div>


    <!-- 5. HUBUNGI TIM PENENGAH MODAL -->
    <div
        x-show="contactModalOpen"
        x-cloak
        class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-xs flex items-center justify-center p-4"
        @keydown.escape.window="contactModalOpen = false"
    >
        <div
            class="bg-[#ffffff] border border-[#e5e5e5] rounded-[4px] max-w-md w-full p-6 shadow-xl relative"
            @click.away="contactModalOpen = false"
        >
            <div class="flex items-center justify-between pb-3 border-b border-[#e5e5e5]">
                <div>
                    <h3 class="text-sm font-extrabold text-[#000000]">Kontak Tim Penengah & Hotline Darurat</h3>
                    <p class="text-[11px] text-gray-500">Dukungan Darurat 24/7 Mediasi Lintas Tenant</p>
                </div>
                <button
                    type="button"
                    @click="contactModalOpen = false"
                    class="w-7 h-7 rounded-[4px] bg-gray-100 hover:bg-gray-200 flex items-center justify-center text-gray-700 transition"
                >
                    ✕
                </button>
            </div>

            <div class="mt-4 space-y-3 text-xs text-gray-700">
                <div class="p-3 bg-[#f9f9f9] border border-gray-200 rounded-[4px] space-y-2">
                    <span class="font-bold text-black block">Petugas Penengah Mediasi:</span>
                    <div class="space-y-1.5 text-[11px]">
                        <p><strong>Hotline Khusus:</strong> 021-8899-772 (Hunting 24 Jam)</p>
                        <p><strong>WhatsApp Duty Lead:</strong> +62 812-3456-7890</p>
                        <p><strong>Email Arbitrase:</strong> resolution@mirrorcam.com</p>
                        <p><strong>Waktu Respons Darurat:</strong> Kurang dari 15 Menit</p>
                    </div>
                </div>

                <p class="text-[11px] text-gray-500 leading-relaxed">
                    Harap siapkan nomor tiket dan nomor pesanan Anda saat melakukan panggilan darurat agar staf penengah dapat langsung memeriksa rekaman digital QC.
                </p>
            </div>

            <div class="mt-5 pt-3 border-t border-[#e5e5e5] flex justify-end gap-2">
                <button
                    type="button"
                    @click="contactModalOpen = false"
                    class="px-4 py-2 bg-black hover:bg-gray-800 text-white text-xs font-bold rounded-[4px] transition"
                >
                    Mengerti
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
