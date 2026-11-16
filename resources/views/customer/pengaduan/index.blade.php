@extends('layouts.landing')

@section('title', 'Formulir Laporan & Pengaduan Pelanggaran — MirrorCam')

@section('content')
<div
    class="min-h-screen bg-[#f9f9f9] text-[#000000] pb-16"
    x-data="{
        uploadedFiles: [],
        selectedTenant: '{{ old('id_tenant', '') }}',
        selectedBooking: '{{ old('id_pemesanan', '') }}',
        init() {
            if (!this.selectedTenant && this.selectedBooking && this.$refs.bookingSelect) {
                const opt = Array.from(this.$refs.bookingSelect.options).find((o) => o.value === this.selectedBooking);
                if (opt) this.selectedTenant = opt.dataset.tenant || '';
            }
            this.applyBookingFilter();
        },
        onBookingChange() {
            const sel = this.$refs.bookingSelect;
            const opt = sel ? sel.options[sel.selectedIndex] : null;
            if (opt && opt.value && opt.dataset.tenant) {
                this.selectedTenant = opt.dataset.tenant;
            }
            this.applyBookingFilter();
        },
        applyBookingFilter() {
            const sel = this.$refs.bookingSelect;
            if (!sel) return;
            Array.from(sel.options).forEach((opt) => {
                if (!opt.value) return;
                const match = !this.selectedTenant || opt.dataset.tenant === this.selectedTenant;
                opt.hidden = !match;
                opt.disabled = !match;
            });
            const current = sel.options[sel.selectedIndex];
            if (current && current.disabled) {
                sel.value = '';
            }
        },
        handleFileSelect(e) {
            const files = Array.from(e.target.files);
            this.uploadedFiles = files.map(f => f.name);
        }
    }"
>
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

    <!-- HEADER -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-10 pb-6">
        <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-6">
            <div class="max-w-3xl">
                <h1 class="text-[26px] sm:text-3xl lg:text-[38px] font-extrabold text-[#000000] tracking-tight leading-tight">
                    Formulir Laporan &amp; Pengaduan Pelanggaran
                </h1>
                <p class="mt-2.5 text-xs sm:text-sm text-gray-600 leading-relaxed font-medium max-w-2xl">
                    Menemukan unit tidak sesuai standard, penahanan deposit sepihak, atau pungutan liar di luar invoice resmi? Laporkan langsung ke MirrorCam.
                </p>
            </div>

            <div class="flex items-start gap-3 shrink-0 lg:pt-1">
                <div class="w-10 h-10 rounded-[4px] border border-[#d9d9d9] bg-[#ffffff] flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-[#000000]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                    </svg>
                </div>
                <div>
                    <span class="block text-[11px] font-extrabold uppercase tracking-[0.08em] text-[#000000]">Laporan Rahasia &amp; Terenkripsi</span>
                    <span class="block text-[11px] text-gray-500 mt-0.5">Respon investigasi tim maks 1x24 jam</span>
                </div>
            </div>
        </div>
    </div>

    <!-- FORMULIR PENGADUAN -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-4">
        <div id="form-pengaduan-card" class="bg-[#ffffff] border border-[#e5e5e5] rounded-[4px] shadow-xs">
            <form action="{{ route('pengaduan.store') }}" method="POST" enctype="multipart/form-data" class="p-6 sm:p-8 lg:p-10 space-y-7">
                @csrf
                <input type="hidden" name="waktu_kejadian" value="{{ old('waktu_kejadian', now()->format('Y-m-d\TH:i')) }}" />

                <!-- Identitas Pelapor -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 lg:gap-8">
                    <div class="space-y-2">
                        <label for="nama_pelapor" class="block text-sm font-bold text-[#000000]">
                            Nama Lengkap Pelapor (sesuai identitas booking) <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            id="nama_pelapor"
                            name="nama_pelapor"
                            value="{{ old('nama_pelapor', $user->nama_lengkap_ktp ?? $user->name) }}"
                            placeholder="Contoh: Raditya Pratama"
                            required
                            class="w-full px-4 py-3 text-sm bg-[#f4f4f4] border border-transparent rounded-[4px] text-[#000000] placeholder-gray-400 focus:bg-[#ffffff] focus:ring-1 focus:ring-black focus:border-black transition"
                        />
                    </div>

                    <div class="space-y-2">
                        <label for="kontak_pelapor" class="block text-sm font-bold text-[#000000]">
                            Nomor Kontak Aktif / WhatsApp <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="tel"
                            id="kontak_pelapor"
                            name="kontak_pelapor"
                            value="{{ old('kontak_pelapor', $user->no_whatsapp) }}"
                            placeholder="0812-3456-7890"
                            required
                            class="w-full px-4 py-3 text-sm bg-[#f4f4f4] border border-transparent rounded-[4px] text-[#000000] placeholder-gray-400 focus:bg-[#ffffff] focus:ring-1 focus:ring-black focus:border-black transition"
                        />
                    </div>
                </div>

                <!-- Booking & Tenant -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 lg:gap-8">
                    <div class="space-y-2">
                        <label for="id_pemesanan" class="block text-sm font-bold text-[#000000]">
                            Nomor Booking / Kode Invoice <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <select
                                id="id_pemesanan"
                                name="id_pemesanan"
                                required
                                x-ref="bookingSelect"
                                x-model="selectedBooking"
                                @change="onBookingChange()"
                                class="appearance-none w-full px-4 py-3 pr-10 text-sm bg-[#f4f4f4] border border-transparent rounded-[4px] text-[#000000] focus:bg-[#ffffff] focus:ring-1 focus:ring-black focus:border-black transition cursor-pointer"
                            >
                                <option value="" disabled {{ old('id_pemesanan') ? '' : 'selected' }}>Pilih nomor booking / kode invoice...</option>
                                @forelse ($userBookings as $booking)
                                    <option
                                        value="{{ $booking->id_pemesanan }}"
                                        data-tenant="{{ $booking->id_tenant }}"
                                        {{ old('id_pemesanan') == $booking->id_pemesanan ? 'selected' : '' }}
                                    >
                                        {{ $booking->booking_code }} — {{ $booking->product_name }}
                                    </option>
                                @empty
                                    <option value="" disabled>Belum ada riwayat sewa aktif/selesai pada akun ini</option>
                                @endforelse
                            </select>
                            <svg class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4" />
                            </svg>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label for="id_tenant" class="block text-sm font-bold text-[#000000]">
                            Pilih Mitra Hub / Tenant Terlapor <span class="text-red-500">*</span>
                        </label>
                        @php
                            $tenantOptions = $userBookings->map(fn ($booking) => [
                                'id' => $booking->id_tenant,
                                'name' => $booking->tenant_name,
                            ])->unique('id')->values();
                        @endphp
                        <div class="relative">
                            <select
                                id="id_tenant"
                                name="id_tenant"
                                required
                                x-model="selectedTenant"
                                @change="applyBookingFilter()"
                                class="appearance-none w-full px-4 py-3 pr-10 text-sm bg-[#f4f4f4] border border-transparent rounded-[4px] text-[#000000] focus:bg-[#ffffff] focus:ring-1 focus:ring-black focus:border-black transition cursor-pointer"
                            >
                                <option value="" disabled>Pilih salah satu mitra hub yang bersangkutan...</option>
                                @foreach ($tenantOptions as $tenant)
                                    <option value="{{ $tenant['id'] }}">{{ $tenant['name'] }}</option>
                                @endforeach
                            </select>
                            <svg class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Kategori Dugaan Pelanggaran -->
                <div class="space-y-3">
                    <span class="block text-sm font-bold text-[#000000]">
                        Kategori Dugaan Pelanggaran <span class="text-red-500">*</span>
                    </span>
                    @php
                        $kategoriDugaan = [
                            ['value' => 'Kondisi Barang Rusak', 'desc' => 'Tidak sesuai standard'],
                            ['value' => 'Pungutan Liar di Luar Invoice', 'desc' => 'Biaya fiktif di lokasi tenant'],
                            ['value' => 'Penahanan Deposit Sepihak', 'desc' => 'Klaim tanpa dasar bukti kuat'],
                            ['value' => 'Manipulasi Jam Pengembalian', 'desc' => 'Penalti over-hour yang tidak valid'],
                            ['value' => 'Perilaku Buruk / Intimidasi Petugas Hub', 'desc' => 'Pelanggaran etika atau ancaman non-profesional'],
                        ];
                        $selectedKategori = old('kategori_pelanggaran', 'Kondisi Barang Rusak');
                    @endphp
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                        @foreach ($kategoriDugaan as $kategori)
                            <label class="flex items-start gap-3 px-4 py-3.5 bg-[#f4f4f4] hover:bg-[#ededed] rounded-[4px] cursor-pointer transition">
                                <input
                                    type="radio"
                                    name="kategori_pelanggaran"
                                    value="{{ $kategori['value'] }}"
                                    {{ $selectedKategori === $kategori['value'] ? 'checked' : '' }}
                                    required
                                    class="mt-0.5 h-4 w-4 shrink-0 accent-black cursor-pointer"
                                />
                                <span class="min-w-0">
                                    <span class="block text-sm font-bold text-[#000000] leading-snug">{{ $kategori['value'] }}</span>
                                    <span class="block text-[11px] text-gray-500 leading-snug mt-0.5">{{ $kategori['desc'] }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <!-- Kronologi Rinci Kejadian -->
                <div class="space-y-2">
                    <label for="kronologi_kejadian" class="block text-sm font-bold text-[#000000]">
                        Kronologi Rinci Kejadian <span class="text-red-500">*</span>
                    </label>
                    <textarea
                        id="kronologi_kejadian"
                        name="kronologi_kejadian"
                        rows="5"
                        required
                        placeholder="Isikan Keluhan Anda Disini"
                        class="w-full px-4 py-3 text-sm bg-[#f4f4f4] border border-transparent rounded-[4px] text-[#000000] placeholder-gray-400 focus:bg-[#ffffff] focus:ring-1 focus:ring-black focus:border-black transition resize-y"
                    >{{ old('kronologi_kejadian') }}</textarea>
                </div>

                <!-- Unggah Dokumen / Bukti Pendukung -->
                <div class="space-y-2">
                    <span class="block text-sm font-bold text-[#000000]">
                        Unggah Dokumen / Bukti Pendukung (Maksimal 10MB)
                    </span>
                    <div
                        class="relative bg-[#f4f4f4] hover:bg-[#efefef] rounded-[4px] py-9 px-6 text-center transition cursor-pointer"
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

                        <div class="w-12 h-12 rounded-[4px] bg-[#ffffff] flex items-center justify-center mx-auto mb-3 shadow-2xs">
                            <svg class="w-6 h-6 text-[#000000]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                            </svg>
                        </div>

                        <p class="text-sm font-bold text-[#000000]">Tarik file ke sini, atau klik untuk memilih file dari perangkat</p>
                        <p class="text-[11px] text-gray-500 mt-1">Unggah foto bukti ke sini</p>

                        <template x-if="uploadedFiles.length > 0">
                            <div class="mt-4 pt-3 border-t border-gray-200 text-left space-y-1 max-w-md mx-auto">
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

                <!-- Pernyataan -->
                <label class="flex items-start gap-3 px-4 py-4 bg-[#f4f4f4] rounded-[4px] cursor-pointer">
                    <input type="checkbox" name="pernyataan" required class="mt-0.5 h-4 w-4 shrink-0 accent-black cursor-pointer rounded-[2px]" />
                    <span class="text-[11px] sm:text-xs text-gray-700 leading-relaxed">
                        Saya menyatakan bahwa keterangan dan dokumen bukti yang saya sampaikan adalah sah, benar, dan sesuai fakta lapangan untuk ditindaklanjuti secara resmi oleh Tim Kepatuhan MirrorCam Nusantara.
                    </span>
                </label>

                <!-- Submit -->
                <div>
                    <button
                        type="submit"
                        class="px-8 py-3 bg-[#000000] hover:bg-[#222222] text-[#ffffff] text-sm font-bold rounded-[4px] shadow-xs transition cursor-pointer"
                    >
                        Kirim Laporan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
