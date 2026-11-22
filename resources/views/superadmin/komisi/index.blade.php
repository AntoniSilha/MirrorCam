<div class="space-y-10 sm:space-y-12">
    <!-- 1. Header Halaman & Aksi Atas -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-[#000000] tracking-tight">
                Rekapitulasi Komisi Platform
            </h1>
            <p class="text-xs sm:text-sm text-gray-500 mt-1 font-medium">
                Kelola dan pantau pendapatan komisi dari seluruh transaksi tenant.
            </p>
        </div>

        <div class="flex items-center gap-2.5">
            <a
                href="{{ route('superadmin.komisi.export') }}"
                class="inline-flex items-center gap-2 bg-[#000000] text-[#ffffff] hover:bg-neutral-800 px-4 py-2.5 rounded-[4px] text-xs font-semibold shadow-xs transition"
            >
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                <span>Ekspor Laporan (.XLSX / PDF)</span>
            </a>
        </div>
    </div>

    <!-- 2. Baris 3 Kartu Metrik Komisi (SRS NFR-DAT-09: integer Rupiah tanpa desimal sen) -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-7 sm:gap-8">
        @include('superadmin.components.stat-card', [
            'title' => 'TOTAL KOMISI TERKUMPUL',
            'value' => $kpi['totalKomisiFormatted'],
        ])

        @include('superadmin.components.stat-card', [
            'title' => 'KOMISI TELAH DITRANSFER',
            'value' => $kpi['komisiDitransferFormatted'],
        ])

        @include('superadmin.components.stat-card', [
            'title' => 'ESCROW JAMINAN DEPOSIT',
            'value' => $kpi['escrowDepositFormatted'],
        ])
    </div>

    <!-- 3. Pencarian ID Transaksi -->
    <div class="flex items-center justify-between pt-2">
        <div class="w-full sm:max-w-sm">
            <div class="relative w-full">
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Cari ID Transaksi"
                    class="w-full bg-[#ffffff] text-xs text-black placeholder-gray-400 pl-9 pr-4 py-2.5 border border-gray-300 rounded-[4px] focus:outline-hidden focus:border-black transition shadow-2xs"
                >
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. Tabel Rekapitulasi Komisi Platform -->
    <div class="bg-[#ffffff] border border-[#e5e5e5] rounded-[4px] overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-[#f9f9f9] border-b border-gray-200 text-gray-500 font-bold uppercase tracking-wider text-[10px]">
                        <th class="py-3.5 px-6">ID TRANSAKSI KOMISI</th>
                        <th class="py-3.5 px-6">TENANT</th>
                        <th class="py-3.5 px-6">PENDAPATAN TENANT</th>
                        <th class="py-3.5 px-6">KOMISI (10%)</th>
                        <th class="py-3.5 px-6">HAK TENANT (90%)</th>
                        <th class="py-3.5 px-6">STATUS</th>
                        <th class="py-3.5 px-6 text-center">AKSI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($komisiList as $item)
                        @php
                            $isSelesai = in_array(strtolower($item->status_komisi ?? ''), ['selesai', 'lunas']);
                            $namaTenant = $item->pemesanan?->tenant?->nama_toko ?? 'Mitra Tenant';
                            $lokasiTenant = $item->pemesanan?->tenant?->detailOperasional?->wilayah_hub ?? $item->pemesanan?->tenant?->kota ?? 'Hub Rental';
                            $bankInfo = $item->pemesanan?->tenant?->rekeningSettlement ? ($item->pemesanan->tenant->rekeningSettlement->nama_bank . ' - ' . $item->pemesanan->tenant->rekeningSettlement->nomor_rekening) : 'Belum diisi';
                        @endphp
                        <tr class="hover:bg-[#fafafa] transition">
                            <!-- Kolom 1: ID Transaksi Komisi & Tanggal -->
                            <td class="py-4 px-6 align-top">
                                <span class="font-extrabold text-[#000000] block leading-tight">
                                    {{ $item->kode_komisi_formatted }}
                                </span>
                                <span class="text-[11px] text-gray-400 block mt-0.5">
                                    {{ $item->created_at?->format('d M Y · H:i') ?? '-' }} WIB
                                </span>
                            </td>

                            <!-- Kolom 2: Tenant -->
                            <td class="py-4 px-6 align-top">
                                <span class="font-bold text-black block leading-tight">
                                    {{ $namaTenant }}
                                </span>
                                <span class="text-[11px] text-gray-500 block mt-0.5">
                                    {{ $lokasiTenant }}
                                </span>
                            </td>

                            <!-- Kolom 3: Pendapatan Tenant (Gross) -->
                            <td class="py-4 px-6 align-top">
                                <span class="text-gray-700 font-medium">
                                    Rp {{ number_format($item->pendapatan_tenant, 0, ',', '.') }}
                                </span>
                            </td>

                            <!-- Kolom 4: Komisi Platform (10%) -->
                            <td class="py-4 px-6 align-top">
                                <span class="font-extrabold text-black">
                                    Rp {{ number_format((float) $item->total_komisi, 0, ',', '.') }}
                                </span>
                            </td>

                            <!-- Kolom 5: Hak Tenant (90%) -->
                            <td class="py-4 px-6 align-top">
                                <span class="text-gray-700 font-medium">
                                    Rp {{ number_format($item->hak_tenant, 0, ',', '.') }}
                                </span>
                            </td>

                            <!-- Kolom 6: Status -->
                            <td class="py-4 px-6 align-top">
                                @include('superadmin.components.status-badge', ['status' => $item->status_label])
                            </td>

                            <!-- Kolom 7: Aksi Verifikasi (SRS UC-24 / FR-ADM-02) -->
                            <td class="py-4 px-6 align-top text-center">
                                @if (!$isSelesai)
                                    <button
                                        type="button"
                                        wire:click="openVerify({{ $item->id_komisi }})"
                                        class="px-3 py-1.5 bg-[#000000] text-white hover:bg-neutral-800 rounded-[4px] text-[11px] font-semibold transition cursor-pointer"
                                        title="Verifikasi Pembayaran Komisi"
                                    >
                                        Verifikasi
                                    </button>
                                @else
                                    <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-gray-400">
                                        <svg class="w-3.5 h-3.5 text-[#16a34a]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        <span>Lunas</span>
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 px-6 text-center bg-[#f9f9f9]">
                                <svg class="w-8 h-8 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <rect x="2" y="6" width="20" height="12" rx="2" stroke-width="1.8"/>
                                    <circle cx="12" cy="12" r="2" stroke-width="1.8"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 12h.01M18 12h.01"/>
                                </svg>
                                <h3 class="text-xs font-bold text-black">Belum Ada Rekapitulasi Komisi</h3>
                                <p class="text-[11px] text-gray-500 mt-1 max-w-sm mx-auto">
                                    Data komisi bagi hasil 10% platform dan 90% hak tenant akan otomatis tercatat setiap kali ada transaksi sewa gear yang diselesaikan oleh mitra.
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- 5. Pagination Baris Bawah -->
        <div class="p-4 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-gray-500 bg-[#ffffff]">
            <span>
                Menampilkan {{ $komisiList->firstItem() ?? 0 }}–{{ $komisiList->lastItem() ?? 0 }} dari {{ number_format($komisiList->total(), 0, ',', '.') }} transaksi
            </span>
            <div>
                {{ $komisiList->links('pagination::simple-tailwind') }}
            </div>
        </div>
    </div>

    <!-- Modal Verifikasi Pembayaran Komisi / Settlement (SRS UC-24 / FR-ADM-02) -->
    @if ($verifyModalOpen)
        <div
            wire:click.self="closeVerify"
            class="fixed inset-0 z-50 overflow-y-auto bg-black/60 flex items-center justify-center p-4"
        >
            <div class="bg-[#ffffff] rounded-[4px] border border-[#e5e5e5] max-w-md w-full p-6 shadow-xl space-y-4">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <h3 class="text-sm font-bold text-[#000000] flex items-center gap-2">
                        <svg class="w-4 h-4 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>Verifikasi Settlement Komisi Mitra</span>
                    </h3>
                    <button
                        type="button"
                        wire:click="closeVerify"
                        class="text-gray-400 hover:text-black font-bold text-sm cursor-pointer"
                    >
                        &times;
                    </button>
                </div>

                <div class="bg-[#f9f9f9] border border-gray-200 rounded-[4px] p-3 space-y-2 text-xs">
                    <div class="flex justify-between">
                        <span class="text-gray-500 font-medium">ID Transaksi Komisi:</span>
                        <span class="font-bold text-black">{{ $selectedKode }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500 font-medium">Tenant Mitra:</span>
                        <span class="font-bold text-black">{{ $selectedTenant }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500 font-medium">Hak Tenant (90%):</span>
                        <span class="font-extrabold text-[#16a34a]">{{ $selectedHakTenant }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500 font-medium">Rekening Tujuan:</span>
                        <span class="font-bold text-gray-700">{{ $selectedRekening }}</span>
                    </div>
                </div>

                <form wire:submit="verify" class="space-y-3">
                    <div>
                        <label class="block text-xs font-bold text-black mb-1">
                            Catatan Verifikasi Settlement (Audit Log)
                        </label>
                        <textarea
                            wire:model="catatan"
                            rows="2"
                            placeholder="Contoh: Bukti transfer settlement telah dicek dan dana 90% berhasil diteruskan ke rekening tenant..."
                            class="w-full bg-[#ffffff] text-xs text-black border border-gray-300 rounded-[4px] p-2 focus:border-black focus:outline-hidden"
                        ></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-gray-100">
                        <button
                            type="button"
                            wire:click="closeVerify"
                            class="px-4 py-2 bg-[#d9d9d9] text-[#000000] rounded-[4px] text-xs font-semibold hover:bg-gray-300 transition cursor-pointer"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            class="px-4 py-2 bg-[#000000] text-[#ffffff] rounded-[4px] text-xs font-semibold hover:bg-neutral-800 transition cursor-pointer"
                        >
                            Konfirmasi & Selesaikan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
