<section class="space-y-5 pb-10">
    {{-- HEADER CARD TOKO & STATUS BUKA/TUTUP --}}
    <div class="rounded-xl border border-neutral-200 bg-white p-5 shadow-[0_1px_2px_rgba(0,0,0,0.02)] sm:p-6">
        <div class="flex flex-col justify-between gap-5 lg:flex-row lg:items-center">
            {{-- Kiri: Foto Toko + Nama + Kode + Bio --}}
            <div class="flex items-start gap-4 sm:items-center">
                <div class="relative shrink-0">
                    <div class="h-16 w-16 overflow-hidden rounded-xl border border-neutral-200 bg-neutral-100 sm:h-20 sm:w-20">
                        @if ($logoUpload)
                            <img src="{{ $logoUpload->temporaryUrl() }}" alt="Preview Logo Toko" class="h-full w-full object-cover">
                        @elseif ($currentLogoUrl)
                            <img src="{{ $currentLogoUrl }}" alt="{{ $namaToko }}" class="h-full w-full object-cover">
                        @else
                            <div class="flex h-full w-full items-center justify-center bg-neutral-900 text-lg font-bold uppercase text-white">
                                {{ \Illuminate\Support\Str::substr($namaToko ?: 'MC', 0, 2) }}
                            </div>
                        @endif
                    </div>

                    {{-- Tombol Ikon Kamera Upload Foto --}}
                    <label for="logoUploadInput" title="Ganti Foto Toko"
                        class="absolute -bottom-1.5 -right-1.5 flex h-7 w-7 cursor-pointer items-center justify-center rounded-full border-2 border-white bg-black text-white shadow-sm transition hover:bg-neutral-800">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </label>
                    <input id="logoUploadInput" type="file" wire:model="logoUpload" accept="image/png,image/jpeg,image/webp" class="hidden">
                </div>

                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="text-base font-bold tracking-tight text-black sm:text-lg">
                            {{ $namaToko ?: 'Nama Toko Belum Diatur' }}
                        </h1>
                        @if ($tenantKode)
                            <span class="rounded bg-neutral-100 px-2 py-0.5 font-mono text-[11px] font-semibold text-neutral-600">
                                {{ $tenantKode }}
                            </span>
                        @endif
                    </div>
                    <p class="mt-1 line-clamp-2 max-w-xl text-xs leading-relaxed text-neutral-500">
                        {{ $bioDeskripsi ?: 'Belum ada deskripsi singkat toko. Lengkapi deskripsi di bawah agar tampil di katalog publik MirrorCam.' }}
                    </p>
                    <div wire:loading wire:target="logoUpload" class="mt-1 text-[11px] font-medium text-neutral-600">
                        Mengunggah foto toko...
                    </div>
                    @error('logoUpload')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Kanan: Status Toko Sewa (Toggle Buka / Tutup) & Jam Operasional --}}
            <div class="flex flex-col items-start gap-2 lg:items-end">
                <div class="flex w-full items-center justify-between gap-4 rounded-xl border border-neutral-200 bg-[#fafafa] px-4 py-2.5 sm:w-auto sm:min-w-[235px]">
                    <div>
                        <div class="flex items-center gap-1.5 text-xs font-semibold text-black">
                            <span class="h-2 w-2 rounded-full {{ $maintenanceMode ? 'bg-red-500' : 'bg-emerald-500' }}"></span>
                            <span>Status Toko Sewa</span>
                        </div>
                        <p class="mt-0.5 text-[11px] font-medium {{ $maintenanceMode ? 'text-red-600' : 'text-emerald-600' }}">
                            {{ $maintenanceMode ? 'Tutup Sementara (Mode Libur)' : 'Buka (Menerima Pesanan)' }}
                        </p>
                    </div>

                    <button type="button" wire:click="toggleMaintenance" aria-label="Toggle Status Toko Sewa"
                        class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none {{ $maintenanceMode ? 'bg-neutral-300' : 'bg-black' }}">
                        <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow-xs transition duration-200 ease-in-out {{ $maintenanceMode ? 'translate-x-0' : 'translate-x-5' }}"></span>
                    </button>
                </div>

                <div class="flex items-center gap-1.5 text-[11px] text-neutral-500">
                    <svg class="h-3.5 w-3.5 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ $jamOperasionalText }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- FORM UTAMA PENGATURAN PROFILE --}}
    <form wire:submit="saveTenantProfile" class="space-y-5">
        {{-- 1. INFORMASI TOKO & LEGALITAS --}}
        <div class="rounded-xl border border-neutral-200 bg-white p-5 shadow-[0_1px_2px_rgba(0,0,0,0.02)] sm:p-6">
            <div class="flex items-start gap-3">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-neutral-200 bg-neutral-50 text-neutral-700">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M3 7l1.664-3.328A2 2 0 016.453 2.5h11.094a2 2 0 011.789 1.172L21 7M3 7h18M3 7v11a2 2 0 002 2h14a2 2 0 002-2V7M9 20v-6h6v6" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-sm font-bold text-black">1. Informasi Toko &amp; Legalitas</h2>
                    <p class="text-xs text-neutral-500">Identitas storefront resmi yang tampil di katalog sewa MirrorCam</p>
                </div>
            </div>

            <div class="mt-5 grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-xs font-semibold text-neutral-800">
                        Nama Toko / Rental <span class="text-red-500">*</span>
                    </label>
                    <input type="text" wire:model="namaToko" placeholder="Contoh: Indonesia Gallery"
                        class="mt-1.5 w-full rounded-lg border border-neutral-200 bg-[#f4f4f5] px-3.5 py-2.5 text-xs font-medium text-neutral-900 transition focus:border-black focus:bg-white focus:outline-none">
                    @error('namaToko') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-neutral-800">
                        Kategori Bisnis Rental <span class="text-red-500">*</span>
                    </label>
                    <select wire:model="kategoriBisnis"
                        class="mt-1.5 w-full rounded-lg border border-neutral-200 bg-[#f4f4f5] px-3.5 py-2.5 text-xs font-medium text-neutral-900 transition focus:border-black focus:bg-white focus:outline-none">
                        <option value="Rental Kamera, Lensa & Lighting Studio">Rental Kamera, Lensa &amp; Lighting Studio</option>
                        <option value="Rental Kamera & Lensa">Rental Kamera &amp; Lensa</option>
                        <option value="Rental Peralatan Audio & Podcast">Rental Peralatan Audio &amp; Podcast</option>
                        <option value="Rental Drone & Sinematografi">Rental Drone &amp; Sinematografi</option>
                        <option value="Studio Foto & Sewa Alat Produksi">Studio Foto &amp; Sewa Alat Produksi</option>
                    </select>
                    @error('kategoriBisnis') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="mt-4">
                <label class="block text-xs font-semibold text-neutral-800">Slogan / Tagline Toko</label>
                <input type="text" wire:model="sloganToko" placeholder="Contoh: Kamera Prima, Karya Maksimal"
                    class="mt-1.5 w-full rounded-lg border border-neutral-200 bg-[#f4f4f5] px-3.5 py-2.5 text-xs font-medium text-neutral-900 transition focus:border-black focus:bg-white focus:outline-none">
                @error('sloganToko') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="mt-4">
                <div class="flex items-center justify-between">
                    <label class="block text-xs font-semibold text-neutral-800">Deskripsi Singkat Toko (Bio Katalog Publik)</label>
                    <span class="text-[11px] text-neutral-400">Maks. 300 Karakter ({{ strlen($bioDeskripsi) }}/300)</span>
                </div>
                <textarea wire:model.live="bioDeskripsi" rows="3" maxlength="300"
                    placeholder="Tuliskan deskripsi singkat mengenai spesialisasi alat kamera dan layanan rental toko Anda..."
                    class="mt-1.5 w-full rounded-lg border border-neutral-200 bg-[#f4f4f5] px-3.5 py-2.5 text-xs leading-relaxed text-neutral-900 transition focus:border-black focus:bg-white focus:outline-none"></textarea>
                @error('bioDeskripsi') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- 2. KONTAK & PENANGGUNG JAWAB OPERASIONAL (PIC) --}}
        <div class="rounded-xl border border-neutral-200 bg-white p-5 shadow-[0_1px_2px_rgba(0,0,0,0.02)] sm:p-6">
            <div class="flex items-start gap-3">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-neutral-200 bg-neutral-50 text-neutral-700">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-sm font-bold text-black">2. Kontak &amp; Penanggung Jawab Operasional (PIC)</h2>
                    <p class="text-xs text-neutral-500">Person In Charge (PIC) penandatanganan berita acara serah terima (BAST) peralatan</p>
                </div>
            </div>

            <div class="mt-5 grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-xs font-semibold text-neutral-800">
                        Nama Lengkap Pemilik <span class="text-red-500">*</span>
                    </label>
                    <input type="text" wire:model="namaPenanggungJawab" placeholder="Nama lengkap penanggung jawab"
                        class="mt-1.5 w-full rounded-lg border border-neutral-200 bg-[#f4f4f5] px-3.5 py-2.5 text-xs font-medium text-neutral-900 transition focus:border-black focus:bg-white focus:outline-none">
                    @error('namaPenanggungJawab') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-neutral-800">
                        Jabatan <span class="text-red-500">*</span>
                    </label>
                    <input type="text" wire:model="jabatanPenanggungJawab" placeholder="Contoh: Owner & General Manager"
                        class="mt-1.5 w-full rounded-lg border border-neutral-200 bg-[#f4f4f5] px-3.5 py-2.5 text-xs font-medium text-neutral-900 transition focus:border-black focus:bg-white focus:outline-none">
                    @error('jabatanPenanggungJawab') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-neutral-800">
                        Email Notifikasi Pesanan Masuk <span class="text-red-500">*</span>
                    </label>
                    <input type="email" wire:model="emailResmi" placeholder="email@tokoanda.id"
                        class="mt-1.5 w-full rounded-lg border border-neutral-200 bg-[#f4f4f5] px-3.5 py-2.5 text-xs font-medium text-neutral-900 transition focus:border-black focus:bg-white focus:outline-none">
                    @error('emailResmi') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-neutral-800">
                        WhatsApp Operasional <span class="text-red-500">*</span>
                    </label>
                    <input type="text" wire:model="whatsappResmi" placeholder="+62-812-xxxx-xxxx"
                        class="mt-1.5 w-full rounded-lg border border-neutral-200 bg-[#f4f4f5] px-3.5 py-2.5 text-xs font-medium text-neutral-900 transition focus:border-black focus:bg-white focus:outline-none">
                    @error('whatsappResmi') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- 3. LOKASI TOKO & HUB PENYERAHAN UNIT --}}
        <div class="rounded-xl border border-neutral-200 bg-white p-5 shadow-[0_1px_2px_rgba(0,0,0,0.02)] sm:p-6">
            <div class="flex items-start gap-3">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-neutral-200 bg-neutral-50 text-neutral-700">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-sm font-bold text-black">3. Lokasi Toko &amp; Hub Penyerahan Unit</h2>
                    <p class="text-xs text-neutral-500">Titik serah-terima kamera, pemeriksaan fisik unit &amp; retur sewa</p>
                </div>
            </div>

            <div class="mt-5">
                <label class="block text-xs font-semibold text-neutral-800">
                    Alamat Offline Tenant <span class="text-red-500">*</span>
                </label>
                <input type="text" wire:model.live.debounce.600ms="alamatToko"
                    placeholder="Masukkan alamat lengkap toko offline"
                    class="mt-1.5 w-full rounded-lg border border-neutral-200 bg-[#f4f4f5] px-3.5 py-2.5 text-xs font-medium text-neutral-900 transition focus:border-black focus:bg-white focus:outline-none">
                @error('alamatToko') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            {{-- Preview Peta Lokasi --}}
            @php
                $mapQuery = urlencode($alamatToko ?: 'Banyuwangi, Jawa Timur');
            @endphp
            <div class="relative mt-4 h-44 w-full overflow-hidden rounded-xl border border-neutral-200 bg-neutral-100">
                <iframe
                    title="Peta Lokasi Toko"
                    class="h-full w-full grayscale-[65%] contrast-[1.05]"
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"
                    src="https://maps.google.com/maps?q={{ $mapQuery }}&t=&z=15&ie=UTF8&iwloc=&output=embed">
                </iframe>
                <div class="pointer-events-none absolute bottom-3 left-1/2 -translate-x-1/2 rounded-md border border-neutral-200 bg-white/95 px-3 py-1 text-center shadow-sm">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-neutral-900">
                        {{ \Illuminate\Support\Str::limit($alamatToko ?: 'LOKASI TOKO TENANT', 42) }}
                    </p>
                    @if ($koordinatLokasi)
                        <p class="text-[9px] text-neutral-500">{{ $koordinatLokasi }}</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- 4. REKENING BANK PENCAIRAN SALDO SEWA --}}
        <div class="rounded-xl border border-neutral-200 bg-white p-5 shadow-[0_1px_2px_rgba(0,0,0,0.02)] sm:p-6">
            <div class="flex items-start gap-3">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-neutral-200 bg-neutral-50 text-neutral-700">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-sm font-bold text-black">4. Rekening Bank Pencairan Saldo Sewa</h2>
                    <p class="text-xs text-neutral-500">Hasil sewa harian &amp; deposit keamanan tenant dicairkan ke rekening ini</p>
                </div>
            </div>

            {{-- Kartu Rekening Saat Ini --}}
            <div class="mt-4 flex flex-col justify-between gap-4 rounded-xl border border-neutral-200 bg-[#f4f4f5] p-4 sm:flex-row sm:items-center">
                <div class="flex items-center gap-3.5">
                    <div class="flex h-11 w-14 shrink-0 items-center justify-center rounded-lg border border-neutral-200 bg-white px-2 text-xs font-extrabold tracking-tight text-blue-700 shadow-2xs">
                        {{ $namaBank ?: 'BANK' }}
                    </div>
                    <div>
                        <div class="flex items-center gap-1.5">
                            <span class="font-mono text-sm font-bold tracking-wide text-black">
                                {{ $nomorRekening ?: 'Belum ada rekening terdaftar' }}
                            </span>
                            @if ($nomorRekening)
                                <span class="inline-flex h-4 w-4 items-center justify-center rounded-full bg-emerald-500 text-white" title="Terverifikasi">
                                    <svg class="h-2.5 w-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                    </svg>
                                </span>
                            @endif
                        </div>
                        <p class="mt-0.5 text-[11px] font-medium uppercase tracking-wide text-neutral-500">
                            {{ $namaPemilikRekening ?: 'Silakan tambahkan rekening pencairan' }}
                        </p>
                    </div>
                </div>

                <button type="button" wire:click="toggleRekeningForm"
                    class="inline-flex items-center gap-1.5 self-start rounded-lg px-2.5 py-1.5 text-xs font-semibold text-neutral-800 transition hover:bg-neutral-200/70 sm:self-center">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                    </svg>
                    <span>{{ $editingRekening ? 'Tutup Form' : 'Ubah Rekening' }}</span>
                </button>
            </div>

            {{-- Form Edit Rekening (Muncul Saat Klik "Ubah Rekening") --}}
            @if ($editingRekening)
                <div class="mt-4 rounded-xl border border-neutral-200 bg-neutral-50 p-4">
                    <div class="grid gap-3 sm:grid-cols-3">
                        <div>
                            <label class="block text-[11px] font-semibold text-neutral-700">Nama Bank</label>
                            <select wire:model="namaBank"
                                class="mt-1 w-full rounded-lg border border-neutral-300 bg-white px-3 py-2 text-xs font-medium text-neutral-900 focus:border-black focus:outline-none">
                                <option value="">Pilih Bank</option>
                                <option value="BCA">BCA</option>
                                <option value="BRI">BRI</option>
                                <option value="BNI">BNI</option>
                                <option value="MANDIRI">MANDIRI</option>
                                <option value="BSI">BSI</option>
                                <option value="CIMB">CIMB NIAGA</option>
                            </select>
                            @error('namaBank') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-neutral-700">Nomor Rekening</label>
                            <input type="text" wire:model="nomorRekening" placeholder="Contoh: 882-019-3341"
                                class="mt-1 w-full rounded-lg border border-neutral-300 bg-white px-3 py-2 text-xs font-medium text-neutral-900 focus:border-black focus:outline-none">
                            @error('nomorRekening') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-neutral-700">Atas Nama Pemilik Rekening</label>
                            <input type="text" wire:model="namaPemilikRekening" placeholder="Nama sesuai buku tabungan"
                                class="mt-1 w-full rounded-lg border border-neutral-300 bg-white px-3 py-2 text-xs font-medium text-neutral-900 focus:border-black focus:outline-none">
                            @error('namaPemilikRekening') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="mt-3 flex justify-end gap-2">
                        <button type="button" wire:click="toggleRekeningForm"
                            class="rounded-lg border border-neutral-300 bg-white px-3 py-1.5 text-xs font-semibold text-neutral-700 hover:bg-neutral-100">
                            Batal
                        </button>
                        <button type="button" wire:click="saveRekening"
                            class="rounded-lg bg-black px-3.5 py-1.5 text-xs font-semibold text-white hover:bg-neutral-800">
                            Simpan Rekening
                        </button>
                    </div>
                </div>
            @endif
        </div>

        {{-- TOMBOL AKSI SIMPAN PERUBAHAN --}}
        <div class="flex items-center justify-end gap-3 pt-1">
            <button type="submit" wire:loading.attr="disabled"
                class="inline-flex items-center gap-2 rounded-lg bg-black px-5 py-2.5 text-xs font-semibold text-white shadow-xs transition hover:bg-neutral-800 disabled:opacity-60">
                <span wire:loading.remove wire:target="saveTenantProfile">Simpan Perubahan</span>
                <span wire:loading wire:target="saveTenantProfile">Menyimpan...</span>
            </button>
        </div>
    </form>
</section>
