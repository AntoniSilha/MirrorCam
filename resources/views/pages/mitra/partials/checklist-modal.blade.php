@if ($showChecklistModal && $this->selectedBooking)
    @php
        $active = $this->selectedBooking;
        $stage1Done = ! empty($active['is_payment_verified']);
        $stage2Done = ! empty($active['is_in_use']) || ! empty($active['is_completed']) || ! empty($active['is_disputed']);
        $stage3Done = ! empty($active['is_completed']) || ! empty($active['is_disputed']);
    @endphp
    <div class="fixed inset-0 z-[70] flex items-center justify-center overflow-y-auto bg-black/60 p-4 font-['Montserrat',sans-serif]" wire:click.self="closeChecklistModal">
        <div class="relative w-full max-w-3xl overflow-hidden rounded-[4px] border border-neutral-200 bg-[#ffffff] shadow-2xl">
            <!-- Modal Header -->
            <div class="flex items-start justify-between border-b border-neutral-200 bg-[#ffffff] px-6 py-5">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="rounded-[4px] bg-black px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-white">Checklist Operasional Pesanan</span>
                        <span class="text-xs font-bold text-neutral-500">Kode: {{ $active['kode'] }}</span>
                    </div>
                    <h2 class="mt-1 text-base font-bold text-[#000000]">{{ $active['produk'] }}</h2>
                    <p class="text-xs text-neutral-500">Penyewa: <span class="font-bold text-black">{{ $active['penyewa'] }}</span> (NIK: {{ $active['nik_ktp'] }}) • Periode: {{ $active['mulai'] }} s/d {{ $active['selesai'] }}</p>
                </div>
                <button type="button" wire:click="closeChecklistModal" class="rounded-[4px] p-1.5 text-neutral-400 hover:bg-neutral-100 hover:text-black">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="max-h-[75vh] space-y-6 overflow-y-auto p-6">
                @if (session()->has('status'))
                    <div class="rounded-[4px] border border-[#dcfce7] bg-[#dcfce7] px-4 py-3 text-xs font-bold text-[#16a34a]">
                        {{ session('status') }}
                    </div>
                @endif

                @error('checklist_payment')
                    <div class="rounded-[4px] border border-[#ffdad6] bg-[#ffdad6] px-4 py-3 text-xs font-bold text-[#ba1a1a]">
                        {{ $message }}
                    </div>
                @enderror
                @error('checklist_handover')
                    <div class="rounded-[4px] border border-[#ffdad6] bg-[#ffdad6] px-4 py-3 text-xs font-bold text-[#ba1a1a]">
                        {{ $message }}
                    </div>
                @enderror
                @error('checklist_return')
                    <div class="rounded-[4px] border border-[#ffdad6] bg-[#ffdad6] px-4 py-3 text-xs font-bold text-[#ba1a1a]">
                        {{ $message }}
                    </div>
                @enderror

                <!-- Stepper Bar (Urutan Transisi Status Bertahap) -->
                <div class="grid grid-cols-3 gap-2">
                    <div class="rounded-[4px] p-3 text-center border {{ $stage1Done ? 'bg-[#fff700]/20 border-[#fff700] text-black' : 'bg-[#f9f9f9] border-neutral-200 text-neutral-400' }}">
                        <div class="text-[10px] font-bold uppercase tracking-wider">Tahap 1</div>
                        <div class="text-xs font-bold {{ $stage1Done ? 'text-black' : 'text-neutral-500' }}">Pembayaran</div>
                        <span class="mt-1 inline-block text-[10px] font-semibold {{ $stage1Done ? 'text-black font-bold' : 'text-neutral-400' }}">
                            {{ $stage1Done ? '✓ Lunas' : 'Menunggu Pelunasan' }}
                        </span>
                    </div>
                    <div class="rounded-[4px] p-3 text-center border {{ $stage2Done ? 'bg-[#dcfce7] border-[#bbf7d0] text-[#16a34a]' : ($stage1Done ? 'bg-[#f9f9f9] border-black text-black' : 'bg-[#f9f9f9] border-neutral-200 text-neutral-400') }}">
                        <div class="text-[10px] font-bold uppercase tracking-wider">Tahap 2</div>
                        <div class="text-xs font-bold {{ $stage2Done ? 'text-[#16a34a]' : ($stage1Done ? 'text-black' : 'text-neutral-500') }}">Barang Digunakan</div>
                        <span class="mt-1 inline-block text-[10px] font-semibold {{ $stage2Done ? 'text-[#16a34a] font-bold' : 'text-neutral-400' }}">
                            {{ $stage2Done ? '✓ Serah Terima Selesai' : ($stage1Done ? 'Siap Serah Terima' : 'Terkunci (Tahap 1)') }}
                        </span>
                    </div>
                    <div class="rounded-[4px] p-3 text-center border {{ $stage3Done ? ($active['is_disputed'] ? 'bg-[#ffdad6] border-[#ffdad6] text-[#ba1a1a]' : 'bg-[#fff700]/20 border-[#fff700] text-black') : ($stage2Done ? 'bg-[#f9f9f9] border-black text-black' : 'bg-[#f9f9f9] border-neutral-200 text-neutral-400') }}">
                        <div class="text-[10px] font-bold uppercase tracking-wider">Tahap 3</div>
                        <div class="text-xs font-bold {{ $stage3Done ? ($active['is_disputed'] ? 'text-[#ba1a1a]' : 'text-black') : ($stage2Done ? 'text-black' : 'text-neutral-500') }}">Pengembalian Unit</div>
                        <span class="mt-1 inline-block text-[10px] font-semibold {{ $stage3Done ? 'font-bold' : 'text-neutral-400' }}">
                            {{ $stage3Done ? ($active['is_disputed'] ? '⚠ Sengketa (Disputed)' : '✓ Selesai') : ($stage2Done ? 'Unit Sedang Dipakai' : 'Terkunci (Tahap 2)') }}
                        </span>
                    </div>
                </div>

                <!-- TAHAP 1 BOX: CEKLIST PEMBAYARAN SELESAI -->
                <div class="rounded-[4px] border border-neutral-200 bg-[#f9f9f9] p-5">
                    <div class="flex items-center justify-between border-b border-neutral-200 pb-3">
                        <div class="flex items-center gap-2">
                            <span class="flex h-5 w-5 items-center justify-center rounded-[4px] bg-black text-[10px] font-bold text-white">1</span>
                            <h3 class="text-sm font-bold text-[#000000]">Tahap 1: Ceklist Pembayaran Selesai (SRS UC-08 & FR-PAY-02)</h3>
                        </div>
                        @if ($stage1Done)
                            <span class="rounded-[4px] border border-[#fff700] bg-[#fff700] px-2.5 py-1 text-[10px] font-bold text-[#000000]">
                                ✓ LUNAS & TERVERIFIKASI
                            </span>
                        @else
                            <span class="rounded-[4px] border border-[#ffdad6] bg-[#ffdad6] px-2.5 py-1 text-[10px] font-bold text-[#ba1a1a]">
                                {{ strtoupper($active['status_harga']) }} (Belum Lunas)
                            </span>
                        @endif
                    </div>

                    <!-- Ringkasan Nominal -->
                    <div class="mt-4 grid grid-cols-3 gap-3">
                        <div class="rounded-[4px] bg-white p-3 border border-neutral-200">
                            <span class="text-[10px] font-semibold uppercase text-neutral-400">Total Sewa</span>
                            <div class="mt-0.5 text-xs font-bold text-black">{{ $this->rupiah($active['total']) }}</div>
                        </div>
                        <div class="rounded-[4px] bg-white p-3 border border-neutral-200">
                            <span class="text-[10px] font-semibold uppercase text-neutral-400">Nominal DP</span>
                            <div class="mt-0.5 text-xs font-bold text-black">{{ $this->rupiah($active['nominal_dp']) }}</div>
                        </div>
                        <div class="rounded-[4px] bg-white p-3 border border-neutral-200">
                            <span class="text-[10px] font-semibold uppercase text-neutral-400">Sisa Tagihan</span>
                            <div class="mt-0.5 text-xs font-bold {{ $stage1Done ? 'text-[#16a34a]' : 'text-[#ba1a1a]' }}">
                                {{ $this->rupiah($active['sisa_tagihan']) }}
                            </div>
                        </div>
                    </div>

                    @if ($stage1Done)
                        <div class="mt-4 rounded-[4px] bg-white p-3 text-xs text-neutral-600 border border-neutral-200">
                            <span class="font-bold text-black">Status Pembayaran Terverifikasi:</span> Seluruh biaya sewa sebesar {{ $this->rupiah($active['total']) }} telah berstatus lunas. Anda dapat melanjutkan serah terima barang pada Tahap 2.
                        </div>
                    @else
                        <div class="mt-4 space-y-3 rounded-[4px] bg-white p-4 border border-neutral-200">
                            <p class="text-xs text-neutral-600">
                                Pelanggan menggunakan skema pembayaran DP. Sisa tagihan sebesar <strong class="text-black">{{ $this->rupiah($active['sisa_tagihan']) }}</strong> wajib dilunasi saat pengambilan barang di tenant (SRS UC-08).
                            </p>
                            <div>
                                <label class="block text-[11px] font-semibold text-neutral-600">Catatan Pelunasan Sisa Tagihan (Opsional)</label>
                                <input type="text" wire:model="catatanPembayaran" placeholder="Contoh: Diterima tunai / transfer pelunasan saat pengambilan unit" class="mt-1 w-full rounded-[4px] border border-neutral-300 bg-[#ffffff] px-3 py-2 text-xs text-[#000000] outline-none focus:border-black">
                            </div>
                            <button type="button" wire:click="verifyPayment({{ $active['id'] }})" class="rounded-[4px] bg-[#000000] px-4 py-2 text-xs font-bold text-[#ffffff] hover:bg-neutral-800 transition">
                                Ceklist Pembayaran Selesai (Lunas)
                            </button>
                        </div>
                    @endif
                </div>

                <!-- TAHAP 2 BOX: CEKLIST BARANG DIGUNAKAN / SERAH TERIMA -->
                <div class="rounded-[4px] border border-neutral-200 bg-[#f9f9f9] p-5">
                    <div class="flex items-center justify-between border-b border-neutral-200 pb-3">
                        <div class="flex items-center gap-2">
                            <span class="flex h-5 w-5 items-center justify-center rounded-[4px] bg-black text-[10px] font-bold text-white">2</span>
                            <h3 class="text-sm font-bold text-[#000000]">Tahap 2: Ceklist Barang Digunakan / Serah Terima (SRS FR-TEN-03 & UC-10)</h3>
                        </div>
                        @if ($stage2Done)
                            <span class="rounded-[4px] border border-[#bbf7d0] bg-[#dcfce7] px-2.5 py-1 text-[10px] font-bold text-[#16a34a]">
                                ✓ Sedang Disewa
                            </span>
                        @elseif (! $stage1Done)
                            <span class="rounded-[4px] border border-[#ffdad6] bg-[#ffdad6] px-2.5 py-1 text-[10px] font-bold text-[#ba1a1a]">
                                Terkunci
                            </span>
                        @else
                            <span class="rounded-[4px] border border-neutral-300 bg-[#d9d9d9] px-2.5 py-1 text-[10px] font-bold text-[#000000]">
                                Menunggu Serah Terima
                            </span>
                        @endif
                    </div>

                    @if (! $stage1Done)
                        <div class="mt-4 rounded-[4px] border border-[#ffdad6] bg-[#ffdad6] p-3 text-xs font-semibold text-[#ba1a1a]">
                            🔒 <strong>Guard Aturan:</strong> Tahap 2 tidak dapat diselesaikan apabila status pembayaran belum lunas. Selesaikan Tahap 1 terlebih dahulu.
                        </div>
                    @elseif ($stage2Done)
                        <div class="mt-4 space-y-3 rounded-[4px] bg-[#ffffff] p-4 border border-neutral-200 text-xs">
                            <div class="grid gap-3 sm:grid-cols-2">
                                <div>
                                    <span class="text-[10px] font-semibold uppercase text-neutral-400">Waktu Serah Terima Aktual</span>
                                    <div class="mt-1 font-semibold text-black">{{ $active['actual_handover_at'] ?? '-' }}</div>
                                </div>
                                <div>
                                    <span class="text-[10px] font-semibold uppercase text-neutral-400">Tenggat Waktu Pengembalian (SRS 6.4)</span>
                                    <div class="mt-1 font-bold text-[#B45309]">{{ $active['return_deadline'] ?? '-' }}</div>
                                </div>
                            </div>
                            @if ($active['foto_kondisi_awal'])
                                <div>
                                    <span class="text-[10px] font-semibold uppercase text-neutral-400">Dokumentasi Kondisi Awal Unit</span>
                                    <div class="mt-1.5">
                                        <a href="{{ $active['foto_kondisi_awal'] }}" target="_blank" class="inline-block">
                                            <img src="{{ $active['foto_kondisi_awal'] }}" alt="Kondisi Awal" class="h-20 w-28 rounded-[4px] object-cover border border-neutral-300">
                                        </a>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @else
                        <!-- Form Checklist Serah Terima Bertahap -->
                        <div class="mt-4 space-y-4 rounded-[4px] bg-[#ffffff] p-4 border border-neutral-200 text-xs">
                            <!-- 1. Checkbox Verifikasi Identitas & Preview KTP -->
                            <div class="space-y-2 rounded-[4px] bg-[#f9f9f9] p-3 border border-neutral-200">
                                <label class="flex items-start gap-2.5 cursor-pointer">
                                    <input type="checkbox" wire:model="checklistIdentitasOk" class="mt-0.5 rounded-[4px] border-neutral-300 text-black focus:ring-black">
                                    <span class="font-bold text-black">
                                        Identitas asli pelanggan cocok dengan data KTP terverifikasi pada sistem
                                    </span>
                                </label>
                                @error('checklistIdentitasOk') <span class="block text-[11px] font-semibold text-[#ba1a1a]">{{ $message }}</span> @enderror

                                <div class="mt-2 flex items-center gap-3 border-t border-neutral-200 pt-2 text-[11px] text-neutral-600">
                                    <span class="font-bold text-black">Pratinjau KTP:</span>
                                    <span>NIK: <strong>{{ $active['nik_ktp'] }}</strong> ({{ $active['status_ktp'] ?? 'terverifikasi' }})</span>
                                    @if ($active['url_foto_ktp'])
                                        <a href="{{ $active['url_foto_ktp'] }}" target="_blank" class="font-bold underline text-black hover:text-neutral-700">Lihat Foto KTP</a>
                                    @endif
                                </div>
                            </div>

                            <!-- 2. Checkbox Kondisi Fisik Awal -->
                            <div class="space-y-1 rounded-[4px] bg-[#f9f9f9] p-3 border border-neutral-200">
                                <label class="flex items-start gap-2.5 cursor-pointer">
                                    <input type="checkbox" wire:model="checklistKondisiAwalOk" class="mt-0.5 rounded-[4px] border-neutral-300 text-black focus:ring-black">
                                    <span class="font-bold text-black">
                                        Kelengkapan unit (body kamera, lensa, baterai, charger, tas) lengkap dan berfungsi normal
                                    </span>
                                </label>
                                @error('checklistKondisiAwalOk') <span class="block text-[11px] font-semibold text-[#ba1a1a]">{{ $message }}</span> @enderror
                            </div>

                            <!-- 3. Unggah Foto Kondisi Awal Unit -->
                            <div class="space-y-2">
                                <label class="block font-bold text-neutral-700">
                                    Unggah Foto Kondisi Awal Unit (Dokumentasi Serah Terima)
                                </label>
                                <input type="file" wire:model="fotoKondisiAwal" accept="image/*" class="w-full text-xs text-neutral-500 file:mr-3 file:rounded-[4px] file:border-0 file:bg-[#d9d9d9] file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-black hover:file:bg-neutral-300">
                                @error('fotoKondisiAwal') <span class="block text-[11px] font-semibold text-[#ba1a1a]">{{ $message }}</span> @enderror

                                @if ($fotoKondisiAwal)
                                    <div class="mt-2 flex items-center gap-3">
                                        <img src="{{ $fotoKondisiAwal->temporaryUrl() }}" alt="Pratinjau Kondisi Awal" class="h-20 w-28 rounded-[4px] object-cover border border-neutral-300">
                                        <span class="text-[11px] font-medium text-neutral-500">Pratinjau foto dokumentasi sebelum serah terima</span>
                                    </div>
                                @endif
                            </div>

                            <!-- 4. Tombol Konfirmasi Serah Terima -->
                            <button type="button" wire:click="confirmHandover({{ $active['id'] }})" class="rounded-[4px] bg-[#000000] px-4 py-2.5 text-xs font-bold text-[#ffffff] hover:bg-neutral-800 transition">
                                Konfirmasi & Tandai Barang Digunakan
                            </button>
                        </div>
                    @endif
                </div>

                <!-- TAHAP 3 BOX: CEKLIST PENANDA PEMINJAMAN SELESAI / PENGEMBALIAN -->
                <div class="rounded-[4px] border border-neutral-200 bg-[#f9f9f9] p-5">
                    <div class="flex items-center justify-between border-b border-neutral-200 pb-3">
                        <div class="flex items-center gap-2">
                            <span class="flex h-5 w-5 items-center justify-center rounded-[4px] bg-black text-[10px] font-bold text-white">3</span>
                            <h3 class="text-sm font-bold text-[#000000]">Tahap 3: Ceklist Penanda Peminjaman Selesai (SRS FR-TEN-04, UC-13 & UC-14)</h3>
                        </div>
                        @if ($stage3Done)
                            @if ($active['is_disputed'])
                                <span class="rounded-[4px] border border-[#ffdad6] bg-[#ffdad6] px-2.5 py-1 text-[10px] font-bold text-[#ba1a1a]">
                                    ⚠ Disputed / Sengketa Kerusakan
                                </span>
                            @else
                                <span class="rounded-[4px] border border-[#fff700] bg-[#fff700] px-2.5 py-1 text-[10px] font-bold text-[#000000]">
                                    ✓ Peminjaman Selesai
                                </span>
                            @endif
                        @elseif (! $stage2Done)
                            <span class="rounded-[4px] border border-[#ffdad6] bg-[#ffdad6] px-2.5 py-1 text-[10px] font-bold text-[#ba1a1a]">
                                Terkunci
                            </span>
                        @else
                            <span class="rounded-[4px] border border-[#bbf7d0] bg-[#dcfce7] px-2.5 py-1 text-[10px] font-bold text-[#16a34a]">
                                Siap Diperiksa
                            </span>
                        @endif
                    </div>

                    @if (! $stage2Done)
                        <div class="mt-4 rounded-[4px] border border-[#ffdad6] bg-[#ffdad6] p-3 text-xs font-semibold text-[#ba1a1a]">
                            🔒 <strong>Guard Aturan:</strong> Tahap 3 hanya dapat dilakukan apabila pesanan sudah berstatus sedang disewa (in_use). Selesaikan Tahap 2 terlebih dahulu.
                        </div>
                    @elseif ($stage3Done)
                        <div class="mt-4 space-y-3 rounded-[4px] bg-[#ffffff] p-4 border border-neutral-200 text-xs">
                            <div class="grid gap-3 sm:grid-cols-2">
                                <div>
                                    <span class="text-[10px] font-semibold uppercase text-neutral-400">Waktu Pengembalian Aktual</span>
                                    <div class="mt-1 font-semibold text-black">{{ $active['actual_returned_at'] ?? '-' }}</div>
                                </div>
                                <div>
                                    <span class="text-[10px] font-semibold uppercase text-neutral-400">Denda Keterlambatan Otomatis</span>
                                    <div class="mt-1 font-bold {{ (float) $active['denda_keterlambatan'] > 0 ? 'text-[#ba1a1a]' : 'text-[#16a34a]' }}">
                                        {{ $this->rupiah($active['denda_keterlambatan']) }}
                                    </div>
                                </div>
                            </div>
                            @if ($active['kondisi_barang_kembali'] === 'rusak')
                                <div class="rounded-[4px] border border-[#ffdad6] bg-[#ffdad6]/40 p-3">
                                    <div class="font-bold text-[#ba1a1a]">Detail Klaim Kerusakan (Diteruskan ke Pusat Aduan SuperAdmin):</div>
                                    <p class="mt-1 text-neutral-700">{{ $active['deskripsi_kerusakan'] ?: '-' }}</p>
                                    <div class="mt-1 font-bold text-[#ba1a1a]">Estimasi Klaim: {{ $this->rupiah($active['estimasi_biaya_kerusakan']) }}</div>
                                </div>
                            @endif
                            @if ($active['foto_kondisi_akhir'])
                                <div>
                                    <span class="text-[10px] font-semibold uppercase text-neutral-400">Dokumentasi Kondisi Akhir Unit</span>
                                    <div class="mt-1.5">
                                        <a href="{{ $active['foto_kondisi_akhir'] }}" target="_blank" class="inline-block">
                                            <img src="{{ $active['foto_kondisi_akhir'] }}" alt="Kondisi Akhir" class="h-20 w-28 rounded-[4px] object-cover border border-neutral-300">
                                        </a>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="mt-4 space-y-4 rounded-[4px] bg-[#ffffff] p-4 border border-neutral-200 text-xs">
                            <!-- Info Waktu Pengembalian & Denda Otomatis (UC-14) -->
                            <div class="rounded-[4px] border border-neutral-200 bg-[#f9f9f9] p-3 space-y-1">
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-neutral-700">Tenggat Waktu Pengembalian:</span>
                                    <span class="font-bold text-[#B45309]">{{ $active['return_deadline'] }}</span>
                                </div>
                                @if ($active['is_overdue'])
                                    <div class="text-[#ba1a1a] font-bold">
                                        ⚠ Waktu sewa telah melewati batas tenggat. Sistem menghitung denda keterlambatan otomatis:
                                        <span class="text-sm font-extrabold text-[#ba1a1a]">{{ $this->rupiah($active['denda_otomatis']) }}</span>
                                    </div>
                                @else
                                    <div class="text-[#16a34a] font-bold">
                                        ✓ Pengembalian unit masih dalam batas waktu sewa (Rp 0 denda).
                                    </div>
                                @endif
                            </div>

                            <!-- Checkbox Kondisi Fisik Akhir -->
                            <div class="space-y-1 rounded-[4px] bg-[#f9f9f9] p-3 border border-neutral-200">
                                <label class="flex items-start gap-2.5 cursor-pointer">
                                    <input type="checkbox" wire:model="checklistKondisiAkhirOk" class="mt-0.5 rounded-[4px] border-neutral-300 text-black focus:ring-black">
                                    <span class="font-bold text-black">
                                        Seluruh unit dan aksesori telah dikembalikan lengkap sesuai checklist awal
                                    </span>
                                </label>
                                @error('checklistKondisiAkhirOk') <span class="block text-[11px] font-semibold text-[#ba1a1a]">{{ $message }}</span> @enderror
                            </div>

                            <!-- Upload Foto Kondisi Akhir -->
                            <div class="space-y-2">
                                <label class="block font-bold text-neutral-700">
                                    Unggah Foto Kondisi Akhir Unit (Dokumentasi Pengembalian)
                                </label>
                                <input type="file" wire:model="fotoKondisiAkhir" accept="image/*" class="w-full text-xs text-neutral-500 file:mr-3 file:rounded-[4px] file:border-0 file:bg-[#d9d9d9] file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-black hover:file:bg-neutral-300">
                                @error('fotoKondisiAkhir') <span class="block text-[11px] font-semibold text-[#ba1a1a]">{{ $message }}</span> @enderror

                                @if ($fotoKondisiAkhir)
                                    <div class="mt-2 flex items-center gap-3">
                                        <img src="{{ $fotoKondisiAkhir->temporaryUrl() }}" alt="Pratinjau Kondisi Akhir" class="h-20 w-28 rounded-[4px] object-cover border border-neutral-300">
                                        <span class="text-[11px] font-medium text-neutral-500">Pratinjau foto dokumentasi pengembalian</span>
                                    </div>
                                @endif
                            </div>

                            <!-- Opsi Kondisi Kerusakan -->
                            <div class="space-y-3 rounded-[4px] bg-[#f9f9f9] p-3 border border-neutral-200">
                                <label class="block font-bold text-black">Opsi Kondisi Unit:</label>
                                <div class="flex items-center gap-6">
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="radio" wire:model.live="kondisiKerusakan" value="baik" class="rounded-[4px] text-black focus:ring-black">
                                        <span class="font-bold text-black">Unit Baik / Tidak Ada Kerusakan</span>
                                    </label>
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="radio" wire:model.live="kondisiKerusakan" value="rusak" class="rounded-[4px] text-[#ba1a1a] focus:ring-[#ba1a1a]">
                                        <span class="font-bold text-[#ba1a1a]">Ditemukan Kerusakan</span>
                                    </label>
                                </div>

                                @if ($kondisiKerusakan === 'rusak')
                                    <div class="mt-3 space-y-3 border-t border-neutral-200 pt-3">
                                        <div>
                                            <label class="block text-[11px] font-bold text-neutral-700">Deskripsi Kerusakan Unit</label>
                                            <textarea wire:model="deskripsiKerusakan" rows="2" placeholder="Jelaskan bagian yang rusak, lecet, pecah, atau malfungsi..." class="mt-1 w-full rounded-[4px] border border-neutral-300 bg-white p-2 text-xs text-black outline-none focus:border-black"></textarea>
                                            @error('deskripsiKerusakan') <span class="block text-[11px] font-semibold text-[#ba1a1a]">{{ $message }}</span> @enderror
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-bold text-neutral-700">Estimasi Biaya Klaim Kerusakan (Rp)</label>
                                            <input type="number" wire:model="estimasiBiayaKerusakan" min="0" placeholder="0" class="mt-1 w-full rounded-[4px] border border-neutral-300 bg-white p-2 text-xs text-black outline-none focus:border-black">
                                            @error('estimasiBiayaKerusakan') <span class="block text-[11px] font-semibold text-[#ba1a1a]">{{ $message }}</span> @enderror
                                        </div>
                                        <p class="text-[11px] font-medium text-[#ba1a1a]">
                                            * Catatan: Pesanan dengan kerusakan akan dialihkan ke status Sengketa (Disputed) untuk diteruskan ke Pusat Aduan SuperAdmin.
                                        </p>
                                    </div>
                                @endif
                            </div>

                            <button type="button" wire:click="confirmReturn({{ $active['id'] }})" class="rounded-[4px] bg-[#000000] px-4 py-2.5 text-xs font-bold text-[#ffffff] hover:bg-neutral-800 transition">
                                Tandai Peminjaman Selesai
                            </button>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="flex items-center justify-end border-t border-neutral-200 bg-[#f9f9f9] px-6 py-4">
                <button type="button" wire:click="closeChecklistModal" class="rounded-[4px] bg-[#d9d9d9] px-4 py-2 text-xs font-bold text-[#000000] hover:bg-neutral-300 transition">
                    Tutup
                </button>
            </div>
        </div>
    </div>
@endif
