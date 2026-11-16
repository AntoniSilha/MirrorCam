@extends('superadmin.layouts.app')

@section('title', 'Manajemen Tenant Mitra — SuperAdmin MirrorCam')

@section('content')
<div class="space-y-10 sm:space-y-12" x-data="{ createModalOpen: false, suspendModalOpen: false, suspendTenantId: null, suspendTenantName: '' }">
    <!-- 1. Header Halaman & Aksi Atas -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-[#000000] tracking-tight">
                Manajemen Tenant Mitra
            </h1>
            <p class="text-xs sm:text-sm text-gray-500 mt-1 font-medium">
                Kelola data tenant, pantau status, dan pastikan setiap tenant dapat beroperasi dengan baik.
            </p>
        </div>

        <div class="flex items-center gap-2.5">
            <a
                href="{{ route('superadmin.tenant.export') }}"
                class="inline-flex items-center gap-2 bg-[#ffffff] text-[#000000] border border-gray-300 hover:bg-gray-50 px-4 py-2.5 rounded-[4px] text-xs font-semibold shadow-xs transition"
            >
                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                <span>Ekspor CSV / XLSX</span>
            </a>

            <button
                type="button"
                @click="createModalOpen = true"
                class="inline-flex items-center gap-1.5 bg-[#000000] text-[#ffffff] hover:bg-neutral-800 px-4 py-2.5 rounded-[4px] text-xs font-semibold shadow-xs transition cursor-pointer"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Tambah Mitra Manual</span>
            </button>
        </div>
    </div>

    <!-- 2. Baris 3 Kartu KPI -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-7 sm:gap-8">
        @include('superadmin.components.stat-card', [
            'title' => 'TOTAL TOKO MITRA',
            'value' => number_format($kpi['totalToko']),
            'subtext' => 'Toko Terdaftar',
            'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>',
        ])

        @include('superadmin.components.stat-card', [
            'title' => 'ANTREAN VERIFIKASI',
            'value' => number_format($kpi['antreanVerifikasi']),
            'subtext' => 'Butuh Review',
        ])

        @include('superadmin.components.stat-card', [
            'title' => 'ALAT DISEWA MELALUI MITRA',
            'value' => number_format($kpi['alatDisewa']),
            'subtext' => 'Unit Gear',
        ])
    </div>

    <!-- 3. Filter & Pencarian Tenant -->
    <div class="bg-[#ffffff] border border-[#e5e5e5] rounded-[4px] p-5 shadow-xs space-y-4">
        <!-- Status Tabs Baris Atas -->
        <div class="flex items-center gap-1.5 overflow-x-auto pb-1 scrollbar-none">
            <a
                href="{{ route('superadmin.tenant.index', array_merge(request()->except('tab', 'page'), ['tab' => 'all'])) }}"
                class="px-4 py-2 text-xs font-semibold rounded-[4px] whitespace-nowrap transition {{ $activeTab === 'all' ? 'bg-[#000000] text-[#ffffff] shadow-xs' : 'bg-[#f9f9f9] text-gray-700 hover:text-black border border-gray-200' }}"
            >
                Semua Tenant ({{ $counts['all'] }})
            </a>
            <a
                href="{{ route('superadmin.tenant.index', array_merge(request()->except('tab', 'page'), ['tab' => 'menunggu_validasi'])) }}"
                class="px-4 py-2 text-xs font-semibold rounded-[4px] whitespace-nowrap transition {{ $activeTab === 'menunggu_validasi' ? 'bg-[#000000] text-[#ffffff] shadow-xs' : 'bg-[#f9f9f9] text-gray-700 hover:text-black border border-gray-200' }}"
            >
                Menunggu Validasi ({{ $counts['menunggu'] }})
            </a>
            <a
                href="{{ route('superadmin.tenant.index', array_merge(request()->except('tab', 'page'), ['tab' => 'mitra_aktif'])) }}"
                class="px-4 py-2 text-xs font-semibold rounded-[4px] whitespace-nowrap transition {{ $activeTab === 'mitra_aktif' ? 'bg-[#000000] text-[#ffffff] shadow-xs' : 'bg-[#f9f9f9] text-gray-700 hover:text-black border border-gray-200' }}"
            >
                Mitra Aktif ({{ $counts['aktif'] }})
            </a>
            <a
                href="{{ route('superadmin.tenant.index', array_merge(request()->except('tab', 'page'), ['tab' => 'ditangguhkan'])) }}"
                class="px-4 py-2 text-xs font-semibold rounded-[4px] whitespace-nowrap transition {{ $activeTab === 'ditangguhkan' ? 'bg-[#000000] text-[#ffffff] shadow-xs' : 'bg-[#f9f9f9] text-gray-700 hover:text-black border border-gray-200' }}"
            >
                Ditangguhkan / Suspend ({{ $counts['suspend'] }})
            </a>
        </div>

        <!-- Form Pencarian & Dropdown Kota -->
        <form method="GET" action="{{ route('superadmin.tenant.index') }}" class="flex flex-col sm:flex-row items-center gap-3">
            @if ($activeTab !== 'all')
                <input type="hidden" name="tab" value="{{ $activeTab }}">
            @endif

            <div class="relative flex-1 w-full">
                <input
                    type="text"
                    name="search"
                    value="{{ $search }}"
                    placeholder="Cari nama toko, PIC, ID Tenant (#TNT-...), kota, atau NIK..."
                    class="w-full bg-[#f9f9f9] text-xs text-black placeholder-gray-400 pl-9 pr-4 py-2.5 border border-gray-300 rounded-[4px] focus:outline-hidden focus:border-black transition"
                >
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
            </div>

            <div class="w-full sm:w-60">
                <select
                    name="city"
                    onchange="this.form.submit()"
                    class="w-full bg-[#f9f9f9] text-xs text-black border border-gray-300 rounded-[4px] py-2.5 px-3 focus:outline-hidden focus:border-black transition cursor-pointer"
                >
                    <option value="">Kota: Semua Wilayah</option>
                    @foreach ($cities as $c)
                        <option value="{{ $c }}" {{ strtolower($selectedCity) === strtolower($c) ? 'selected' : '' }}>
                            Kota: {{ $c }}
                        </option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>

    <!-- 4. Tabel Mitra Tenant -->
    <div class="bg-[#ffffff] border border-[#e5e5e5] rounded-[4px] overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-[#f9f9f9] border-b border-gray-200 text-gray-500 font-bold uppercase tracking-wider text-[10px]">
                        <th class="py-3.5 px-4">TOKO & ID MITRA</th>
                        <th class="py-3.5 px-4">PIC & KONTAK</th>
                        <th class="py-3.5 px-4">KOTA & WILAYAH HUB</th>
                        <th class="py-3.5 px-4">ULASAN</th>
                        <th class="py-3.5 px-4">REKENING SETTLEMENT</th>
                        <th class="py-3.5 px-4">STATUS</th>
                        <th class="py-3.5 px-4 text-center">AKSI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($tenants as $tenant)
                        @php
                            $isAktif = strtolower($tenant->status_mitra ?? '') === 'aktif';
                        @endphp
                        <tr class="hover:bg-[#fafafa] transition">
                            <!-- Kolom 1: Toko & ID Mitra -->
                            <td class="py-4 px-4 align-top">
                                <div class="flex items-start gap-3">
                                    <div class="w-9 h-9 rounded-[4px] bg-[#d9d9d9] text-[#000000] flex items-center justify-center font-bold text-xs shrink-0">
                                        {{ $tenant->initials }}
                                    </div>
                                    <div class="min-w-0">
                                        <span class="font-bold text-black block leading-tight truncate">
                                            {{ $tenant->nama_toko }}
                                        </span>
                                        <span class="text-[11px] text-gray-400 font-medium">
                                            {{ $tenant->id_mitra_formatted }}
                                        </span>
                                    </div>
                                </div>
                            </td>

                            <!-- Kolom 2: PIC & Kontak -->
                            <td class="py-4 px-4 align-top">
                                <span class="font-bold text-black block leading-tight">
                                    {{ $tenant->nama_penanggungjawab }}
                                </span>
                                <span class="text-[11px] text-gray-500 block mt-0.5">
                                    {{ $tenant->whatsapp_resmi }}
                                </span>
                                <span class="text-[11px] text-gray-400 block truncate max-w-[180px]">
                                    {{ $tenant->email_resmi }}
                                </span>
                            </td>

                            <!-- Kolom 3: Kota & Wilayah Hub -->
                            <td class="py-4 px-4 align-top">
                                <span class="font-bold text-black block leading-tight">
                                    {{ $tenant->kota }}
                                </span>
                                <span class="text-[11px] text-gray-500 block mt-0.5 truncate max-w-[180px]">
                                    {{ $tenant->alamat_toko }}
                                </span>
                                @if ($tenant->detailOperasional)
                                    <span class="text-[10px] text-gray-400 block mt-0.5">
                                        ({{ substr($tenant->detailOperasional->jam_buka, 0, 5) }} - {{ substr($tenant->detailOperasional->jam_tutup, 0, 5) }})
                                    </span>
                                @endif
                            </td>

                            <!-- Kolom 4: Ulasan -->
                            <td class="py-4 px-4 align-top">
                                <div class="flex items-center gap-1 font-bold text-[#16a34a] text-xs">
                                    <span>&#9734;</span>
                                    <span>{{ $tenant->formatted_rating }}</span>
                                </div>
                            </td>

                            <!-- Kolom 5: Rekening Settlement -->
                            <td class="py-4 px-4 align-top">
                                @if ($tenant->rekeningSettlement)
                                    <span class="font-bold text-black block leading-tight">
                                        {{ $tenant->rekeningSettlement->nama_bank }}
                                    </span>
                                    <span class="font-bold text-gray-700 block text-[11px]">
                                        {{ $tenant->rekeningSettlement->nomor_rekening }}
                                    </span>
                                    <span class="text-[10px] text-gray-400 block truncate max-w-[160px]">
                                        {{ $tenant->rekeningSettlement->nama_pemilik_rekening }}
                                    </span>
                                @else
                                    <span class="text-[11px] text-gray-400 italic">Belum diisi</span>
                                @endif
                            </td>

                            <!-- Kolom 6: Status -->
                            <td class="py-4 px-4 align-top">
                                @include('superadmin.components.status-badge', ['status' => $tenant->status_mitra])
                            </td>

                            <!-- Kolom 7: Aksi -->
                            <td class="py-4 px-4 align-top text-center">
                                <div class="inline-flex items-center gap-2">
                                    <!-- Tombol Setujui / Aktifkan -->
                                    <form action="{{ route('superadmin.tenant.approve', $tenant->id_tenant) }}" method="POST" class="inline">
                                        @csrf
                                        <button
                                            type="submit"
                                            class="w-7 h-7 rounded-full border border-gray-300 flex items-center justify-center text-gray-700 hover:text-black hover:bg-gray-100 transition shadow-2xs {{ $isAktif ? 'opacity-40 cursor-not-allowed' : 'cursor-pointer' }}"
                                            title="Setujui / Aktifkan Mitra"
                                            {{ $isAktif ? 'disabled' : '' }}
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </button>
                                    </form>

                                    <!-- Tombol Tangguhkan / Suspend -->
                                    <button
                                        type="button"
                                        @click="suspendModalOpen = true; suspendTenantId = {{ $tenant->id_tenant }}; suspendTenantName = '{{ $tenant->nama_toko }}'"
                                        class="w-7 h-7 rounded-full border border-gray-300 flex items-center justify-center text-gray-700 hover:text-red-600 hover:bg-red-50 transition shadow-2xs cursor-pointer"
                                        title="Tangguhkan / Suspend Mitra"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 px-6 text-center bg-[#f9f9f9]">
                                <svg class="w-8 h-8 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                                <h3 class="text-xs font-bold text-black">Belum Ada Data Tenant Mitra</h3>
                                <p class="text-[11px] text-gray-500 mt-1 max-w-sm mx-auto">
                                    Data mitra yang mendaftar atau ditambahkan secara manual akan tampil dalam tabel ini.
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination Baris Bawah -->
        <div class="p-4 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-gray-500 bg-[#ffffff]">
            <span>
                Menampilkan {{ $tenants->firstItem() ?? 0 }}-{{ $tenants->lastItem() ?? 0 }} dari {{ $tenants->total() }} tenant mitra terdaftar
            </span>
            <div>
                {{ $tenants->appends(request()->query())->links('pagination::simple-tailwind') }}
            </div>
        </div>
    </div>

    <!-- Modal Form Tambah Mitra Manual (SRS UC-23 / FR-ADM-01) -->
    <div
        x-show="createModalOpen"
        x-cloak
        class="fixed inset-0 z-50 overflow-y-auto bg-black/60 flex items-center justify-center p-4"
    >
        <div
            @click.away="createModalOpen = false"
            class="bg-[#ffffff] rounded-[4px] border border-[#e5e5e5] max-w-xl w-full p-6 sm:p-7 shadow-xl space-y-5"
        >
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <h3 class="text-sm sm:text-base font-bold text-[#000000]">
                    + Tambah Mitra Tenant Manual (Onboarding Platform)
                </h3>
                <button
                    type="button"
                    @click="createModalOpen = false"
                    class="text-gray-400 hover:text-black font-bold text-lg cursor-pointer"
                >
                    &times;
                </button>
            </div>

            <form action="{{ route('superadmin.tenant.store') }}" method="POST" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[11px] font-bold text-black mb-1">
                            Nama Toko / Rental <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            name="nama_toko"
                            required
                            placeholder="Contoh: Jakarta Cine Gear"
                            class="w-full bg-[#f9f9f9] text-xs text-black border border-gray-300 rounded-[4px] p-2.5 focus:border-black focus:outline-hidden"
                        >
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-black mb-1">
                            Nama PIC (Penanggung Jawab) <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            name="nama_penanggungjawab"
                            required
                            placeholder="Contoh: Budi Santoso"
                            class="w-full bg-[#f9f9f9] text-xs text-black border border-gray-300 rounded-[4px] p-2.5 focus:border-black focus:outline-hidden"
                        >
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-black mb-1">
                            Nomor WhatsApp Resmi <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            name="whatsapp_resmi"
                            required
                            placeholder="Contoh: 08112093812"
                            class="w-full bg-[#f9f9f9] text-xs text-black border border-gray-300 rounded-[4px] p-2.5 focus:border-black focus:outline-hidden"
                        >
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-black mb-1">
                            Email Resmi Tenant <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="email"
                            name="email_resmi"
                            required
                            placeholder="Contoh: budi@cinegear.id"
                            class="w-full bg-[#f9f9f9] text-xs text-black border border-gray-300 rounded-[4px] p-2.5 focus:border-black focus:outline-hidden"
                        >
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-black mb-1">
                            Kota / Wilayah Hub <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            name="kota"
                            required
                            placeholder="Contoh: Jakarta Selatan"
                            class="w-full bg-[#f9f9f9] text-xs text-black border border-gray-300 rounded-[4px] p-2.5 focus:border-black focus:outline-hidden"
                        >
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-black mb-1">
                            Status Awal
                        </label>
                        <select
                            name="status_mitra"
                            class="w-full bg-[#f9f9f9] text-xs text-black border border-gray-300 rounded-[4px] p-2.5 focus:border-black focus:outline-hidden"
                        >
                            <option value="aktif">Aktif Langsung</option>
                            <option value="Menunggu Verifikasi">Menunggu Verifikasi</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-black mb-1">
                        Alamat Lengkap Toko / Hub <span class="text-red-500">*</span>
                    </label>
                    <textarea
                        name="alamat_toko"
                        rows="2"
                        required
                        placeholder="Contoh: Jl. Senopati No. 12, Kebayoran Baru"
                        class="w-full bg-[#f9f9f9] text-xs text-black border border-gray-300 rounded-[4px] p-2.5 focus:border-black focus:outline-hidden"
                    ></textarea>
                </div>

                <!-- Info Rekening Settlement -->
                <div class="border-t border-gray-100 pt-3">
                    <span class="block text-[10px] font-bold tracking-wider text-gray-400 uppercase mb-2">
                        REKENING BANK SETTLEMENT (OPSIONAL)
                    </span>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <input
                            type="text"
                            name="nama_bank"
                            placeholder="Bank (BCA/Mandiri)"
                            class="bg-[#f9f9f9] text-xs text-black border border-gray-300 rounded-[4px] p-2 focus:border-black focus:outline-hidden"
                        >
                        <input
                            type="text"
                            name="nomor_rekening"
                            placeholder="Nomor Rekening"
                            class="bg-[#f9f9f9] text-xs text-black border border-gray-300 rounded-[4px] p-2 focus:border-black focus:outline-hidden"
                        >
                        <input
                            type="text"
                            name="nama_pemilik_rekening"
                            placeholder="Nama Pemilik"
                            class="bg-[#f9f9f9] text-xs text-black border border-gray-300 rounded-[4px] p-2 focus:border-black focus:outline-hidden"
                        >
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100">
                    <button
                        type="button"
                        @click="createModalOpen = false"
                        class="px-4 py-2 bg-[#d9d9d9] text-[#000000] rounded-[4px] text-xs font-semibold hover:bg-gray-300 transition"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        class="px-5 py-2 bg-[#000000] text-[#ffffff] rounded-[4px] text-xs font-semibold hover:bg-neutral-800 transition"
                    >
                        Simpan & Aktifkan Mitra
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Konfirmasi Penangguhan (Suspend) -->
    <div
        x-show="suspendModalOpen"
        x-cloak
        class="fixed inset-0 z-50 overflow-y-auto bg-black/60 flex items-center justify-center p-4"
    >
        <div
            @click.away="suspendModalOpen = false"
            class="bg-[#ffffff] rounded-[4px] border border-[#e5e5e5] max-w-md w-full p-6 shadow-xl space-y-4"
        >
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <h3 class="text-sm font-bold text-red-600 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <span>Konfirmasi Penangguhan Mitra</span>
                </h3>
                <button
                    type="button"
                    @click="suspendModalOpen = false"
                    class="text-gray-400 hover:text-black font-bold text-sm"
                >
                    &times;
                </button>
            </div>

            <p class="text-xs text-gray-600 leading-relaxed">
                Apakah Anda yakin ingin menangguhkan tenant <span class="font-bold text-black" x-text="suspendTenantName"></span>? Tenant yang ditangguhkan tidak akan dapat menerima pesanan baru.
            </p>

            <form :action="'/superadmin/tenant/' + suspendTenantId + '/suspend'" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-black mb-1">
                        Alasan Penangguhan (Catatan Audit)
                    </label>
                    <textarea
                        name="alasan"
                        rows="2"
                        placeholder="Contoh: Sengketa aduan berulang atau keterlambatan settlement..."
                        class="w-full bg-[#ffffff] text-xs text-black border border-gray-300 rounded-[4px] p-2 focus:border-black focus:outline-hidden"
                    ></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-gray-100">
                    <button
                        type="button"
                        @click="suspendModalOpen = false"
                        class="px-4 py-2 bg-[#d9d9d9] text-[#000000] rounded-[4px] text-xs font-semibold hover:bg-gray-300 transition"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        class="px-4 py-2 bg-[#ba1a1a] text-[#ffffff] rounded-[4px] text-xs font-semibold hover:bg-red-800 transition"
                    >
                        Tangguhkan Mitra
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
