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
                                    <a href="#qris-pembayaran" class="inline-flex items-center justify-center whitespace-nowrap rounded-md bg-[#e9e9e9] px-5 py-3 text-xs font-semibold text-black transition hover:bg-[#dedede]">
                                        Bayar Sekarang
                                    </a>
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
                                            <img src="{{ $billing['qris_data_uri'] }}" alt="QRIS Pembayaran Komisi" class="h-44 w-44 rounded-md bg-white p-2 shadow-xs">
                                            <p class="text-sm font-bold text-black">QRIS siap dipindai</p>
                                            <p class="text-center text-[11px] leading-relaxed text-neutral-500">
                                                Nominal {{ $this->rupiah($billing['amount']) }} &middot; {{ $billing['periode'] }}<br>
                                                Ref. {{ $billing['reference'] }}
                                            </p>
                                        </div>
                                    @else
                                        <div class="flex flex-col items-center gap-3 py-3 text-center">
                                            <span class="flex h-11 w-11 items-center justify-center rounded-lg bg-[#e9e9e9] text-neutral-500">
                                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M4 4h6v6H4V4zm10 0h6v6h-6V4zM4 14h6v6H4v-6zM14 14h2v2h-2v-2zm4 0h2v2h-2v-2zm-4 4h2v2h-2v-2zm4 0h2v2h-2v-2z" />
                                                </svg>
                                            </span>
                                            <p class="text-sm font-bold text-black">QRIS belum tersedia</p>
                                            <p class="text-[11px] leading-relaxed text-neutral-500">QRIS tersedia setelah tagihan diterbitkan.<br>Belum ada kode pembayaran yang dapat dipindai.</p>
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
                    </section>
