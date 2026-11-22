@php
    $billing = $dashboard['commission_billing'];
    $bank = config('mirrorcam.platform_bank');
@endphp

<section class="space-y-5">
    <div class="rounded-xl border border-neutral-200 bg-white p-6 shadow-[0_1px_2px_rgba(0,0,0,0.02)] md:p-7">
        <h1 class="text-[25px] font-bold leading-tight tracking-tight text-black md:text-[28px]">Pembayaran Komisi</h1>
        <p class="mt-2 text-xs leading-relaxed text-neutral-500 md:text-sm">Kelola tanggungan komisi {{ $tenant['nama_toko'] }} kepada PT. MirrorCam.</p>
    </div>

    <div class="rounded-xl border border-neutral-200 bg-white p-6 shadow-[0_1px_2px_rgba(0,0,0,0.02)] md:p-7">
        <h2 class="text-base font-bold tracking-tight text-black md:text-lg">Tanggungan Komisi ke MirrorCam</h2>

        <div class="mt-5 flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-[10px] font-semibold uppercase tracking-[0.1em] text-neutral-500">JUMLAH TANGGUNGAN</p>
                @if ($billing['has_bill'])
                    <p class="mt-2 text-[30px] font-bold leading-tight tracking-tight text-black md:text-[36px]">{{ $this->rupiah($billing['amount']) }}</p>
                @else
                    <p class="mt-2 text-[30px] font-bold leading-tight tracking-tight text-neutral-700 md:text-[36px]">Belum tersedia</p>
                @endif
            </div>

            @if ($billing['has_bill'])
                <button type="button" wire:click="generateCommissionQris"
                    class="inline-flex items-center justify-center whitespace-nowrap rounded-md bg-black px-5 py-3 text-xs font-semibold text-white transition hover:bg-neutral-800">
                    Bayar Sekarang
                </button>
            @else
                <button type="button" disabled class="inline-flex cursor-not-allowed items-center justify-center whitespace-nowrap rounded-md bg-[#e9e9e9] px-5 py-3 text-xs font-semibold text-neutral-400">
                    Bayar Sekarang
                </button>
            @endif
        </div>

        <div class="mt-6 grid gap-4 border-t border-neutral-200 pt-5 sm:grid-cols-3">
            <div>
                <p class="text-[10px] font-semibold uppercase tracking-[0.1em] text-neutral-500">PENYEDIA LAYANAN</p>
                <p class="mt-1.5 text-sm font-semibold text-black">PT. MirrorCam</p>
            </div>
            <div>
                <p class="text-[10px] font-semibold uppercase tracking-[0.1em] text-neutral-500">PERIODE TAGIHAN</p>
                <p class="mt-1.5 text-sm font-semibold text-black">{{ $billing['periode'] ?? 'Belum tersedia' }}</p>
            </div>
            <div>
                <p class="text-[10px] font-semibold uppercase tracking-[0.1em] text-neutral-500">JATUH TEMPO</p>
                <p class="mt-1.5 text-sm font-semibold text-black">{{ $billing['jatuh_tempo'] ?? 'Belum tersedia' }}</p>
            </div>
        </div>
    </div>

    <div class="grid gap-5 lg:grid-cols-2">
        <div id="qris-pembayaran" class="scroll-mt-24 rounded-xl border border-neutral-200 bg-white p-6 shadow-[0_1px_2px_rgba(0,0,0,0.02)] md:p-7">
            <div class="flex items-start justify-between gap-3">
                <h2 class="text-base font-bold tracking-tight text-black md:text-lg">Pembayaran via QRIS</h2>
                <span class="text-sm font-bold tracking-wide text-black">QRIS</span>
            </div>
            <p class="mt-3 text-xs leading-relaxed text-neutral-500 md:text-sm">Bayar komisi dengan memindai QRIS melalui aplikasi bank atau dompet digital yang mendukung QRIS.</p>

            <div class="mt-5 rounded-lg border border-neutral-200 bg-[#f4f4f5] px-5 py-6">
                @if ($billing['has_bill'] && $billing['qris_data_uri'])
                    <div class="flex flex-col items-center gap-3">
                        <div class="rounded-xl border border-neutral-200 bg-white p-3 shadow-xs">
                            <img src="{{ $billing['qris_data_uri'] }}" alt="QRIS Pembayaran Komisi" class="h-44 w-44">
                        </div>
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-2.5 py-0.5 text-[11px] font-semibold text-emerald-700">
                            <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-emerald-600"></span>
                            QRIS Aktif &amp; Siap Dipindai
                        </span>
                        <p class="text-center text-xs leading-relaxed text-neutral-600">
                            Nominal <strong class="text-black">{{ $this->rupiah($billing['amount']) }}</strong> &middot; {{ $billing['periode'] }}<br>
                            <span class="font-mono text-[11px] text-neutral-500">Ref. {{ $billing['reference'] }}</span>
                        </p>

                        <button type="button" wire:click="confirmCommissionQrisPayment" wire:loading.attr="disabled"
                            class="mt-1 inline-flex items-center gap-2 rounded-lg bg-black px-4 py-2 text-xs font-semibold text-white transition hover:bg-neutral-800 disabled:opacity-60">
                            <span wire:loading.remove wire:target="confirmCommissionQrisPayment">Konfirmasi Pembayaran Otomatis</span>
                            <span wire:loading wire:target="confirmCommissionQrisPayment">Memverifikasi Pembayaran...</span>
                        </button>
                    </div>
                @else
                    <div class="flex flex-col items-center gap-3 py-3 text-center">
                        <span class="flex h-11 w-11 items-center justify-center rounded-lg bg-[#e9e9e9] text-neutral-500">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M4 4h6v6H4V4zm10 0h6v6h-6V4zM4 14h6v6H4v-6zM14 14h2v2h-2v-2zm4 0h2v2h-2v-2zm-4 4h2v2h-2v-2zm4 0h2v2h-2v-2z" />
                            </svg>
                        </span>
                        <p class="text-sm font-bold text-black">
                            {{ ! empty($billing['paid_history']) ? 'Semua Tagihan Komisi Lunas' : 'QRIS belum tersedia' }}
                        </p>
                        <p class="text-[11px] leading-relaxed text-neutral-500">
                            {{ ! empty($billing['paid_history']) ? 'Tidak ada tanggungan komisi yang belum dibayar saat ini.' : 'QRIS tersedia setelah tagihan diterbitkan. Belum ada kode pembayaran yang dapat dipindai.' }}
                        </p>
                    </div>
                @endif
            </div>
        </div>

        <div class="rounded-xl border border-neutral-200 bg-white p-6 shadow-[0_1px_2px_rgba(0,0,0,0.02)] md:p-7">
            <div class="flex items-center gap-2.5">
                <span class="flex h-6 w-6 items-center justify-center text-black">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M3 21h18M5 21V9l7-5 7 5v12M9 21v-6h6v6M3 9h18" />
                    </svg>
                </span>
                <h2 class="text-base font-bold tracking-tight text-black md:text-lg">Informasi Rekening Tujuan</h2>
            </div>
            <p class="mt-3 text-xs leading-relaxed text-neutral-500 md:text-sm">Informasi rekening penyedia layanan untuk pembayaran komisi kepada PT. MirrorCam.</p>

            <dl class="mt-4 divide-y divide-neutral-200">
                <div class="flex items-center justify-between gap-4 py-3.5">
                    <dt class="text-xs text-neutral-500 md:text-sm">Atas nama</dt>
                    <dd class="text-right text-xs font-semibold text-black md:text-sm">{{ $bank['atas_nama'] }}</dd>
                </div>
                <div class="flex items-center justify-between gap-4 py-3.5">
                    <dt class="text-xs text-neutral-500 md:text-sm">Bank</dt>
                    <dd class="text-right text-xs font-semibold text-black md:text-sm">{{ $bank['nama_bank'] }}</dd>
                </div>
                <div class="flex items-center justify-between gap-4 py-3.5">
                    <dt class="text-xs text-neutral-500 md:text-sm">Nomor rekening</dt>
                    <dd class="text-right text-xs font-semibold text-black md:text-sm">{{ $bank['nomor_rekening'] }}</dd>
                </div>
            </dl>
        </div>
    </div>

    <div class="rounded-xl border border-neutral-200 bg-white p-6 shadow-[0_1px_2px_rgba(0,0,0,0.02)] md:p-7">
        <h2 class="text-base font-bold tracking-tight text-black md:text-lg">Cara Membayar Komisi</h2>

        <div class="mt-5 grid gap-5 sm:grid-cols-3">
            <div class="flex items-start gap-3">
                <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-md bg-[#f1f1f1] text-[11px] font-bold text-black">1</span>
                <div>
                    <p class="text-xs font-bold text-black md:text-sm">Periksa tagihan</p>
                    <p class="mt-1 text-[11px] leading-relaxed text-neutral-500">Setelah diterbitkan, cek jumlah tanggungan, periode, dan jatuh tempo.</p>
                </div>
            </div>
            <div class="flex items-start gap-3">
                <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-md bg-[#f1f1f1] text-[11px] font-bold text-black">2</span>
                <div>
                    <p class="text-xs font-bold text-black md:text-sm">Pilih Bayar Sekarang</p>
                    <p class="mt-1 text-[11px] leading-relaxed text-neutral-500">QRIS pembayaran akan tersedia setelah tagihan diterbitkan.</p>
                </div>
            </div>
            <div class="flex items-start gap-3">
                <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-md bg-[#f1f1f1] text-[11px] font-bold text-black">3</span>
                <div>
                    <p class="text-xs font-bold text-black md:text-sm">Pindai dan bayar</p>
                    <p class="mt-1 text-[11px] leading-relaxed text-neutral-500">Periksa penerima serta nominal di aplikasi, lalu simpan bukti pembayaran.</p>
                </div>
            </div>
        </div>
    </div>

    @if (! empty($billing['paid_history']))
        <div class="rounded-xl border border-neutral-200 bg-white p-6 shadow-[0_1px_2px_rgba(0,0,0,0.02)] md:p-7">
            <h2 class="text-base font-bold tracking-tight text-black md:text-lg">Riwayat Pelunasan Komisi</h2>
            <p class="mt-1 text-xs text-neutral-500">Daftar pembayaran komisi platform yang telah diverifikasi otomatis melalui QRIS.</p>

            <div class="mt-4 overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="border-b border-neutral-200 text-[11px] font-semibold uppercase text-neutral-500">
                        <tr>
                            <th class="py-2.5 pr-4">Kode</th>
                            <th class="py-2.5 pr-4">Tanggal Bayar</th>
                            <th class="py-2.5 pr-4">Metode</th>
                            <th class="py-2.5 pr-4">Referensi</th>
                            <th class="py-2.5 text-right">Nominal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-100">
                        @foreach ($billing['paid_history'] as $history)
                            <tr>
                                <td class="py-3 pr-4 font-mono font-semibold text-black">{{ $history['kode'] }}</td>
                                <td class="py-3 pr-4 text-neutral-600">{{ $history['tanggal_bayar'] }}</td>
                                <td class="py-3 pr-4">
                                    <span class="rounded-full bg-emerald-50 px-2.5 py-0.5 text-[11px] font-semibold text-emerald-700">{{ $history['metode'] }}</span>
                                </td>
                                <td class="py-3 pr-4 font-mono text-[11px] text-neutral-500">{{ $history['referensi'] }}</td>
                                <td class="py-3 text-right font-semibold text-black">{{ $this->rupiah($history['jumlah']) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</section>
