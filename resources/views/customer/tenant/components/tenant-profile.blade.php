@php
    $namaTenant = $tenant->nama_toko ?? $tenant->nama_tenant ?? 'Indonesia Gallery';
    $initials = collect(explode(' ', $namaTenant))
        ->map(fn($w) => strtoupper(substr($w, 0, 1)))
        ->take(2)
        ->implode('');

    $svg = 'w-3.5 h-3.5 shrink-0';
@endphp

<section class="bg-[#ffffff] border border-black/10 rounded-[4px] p-6 mb-6 font-['Montserrat']">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="flex items-start gap-5">
            <div class="w-20 h-20 rounded-[4px] bg-[#000000] text-[#ffffff] flex items-center justify-center text-2xl font-bold shrink-0">
                {{ $initials }}
            </div>

            <div>
                <h1 class="text-2xl font-bold text-[#000000] tracking-tight">{{ $namaTenant }}</h1>
                <p class="text-xs sm:text-sm text-black/60 mt-1">
                    {{ $tenant->tagline ?? $tenant->deskripsi ?? 'Spesialis Kamera Sinema, Lensa Anamorphic & Lighting Kit Pasca-Produksi' }}
                </p>

                <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-black/70 mt-2.5">
                    {{-- map-pin --}}
                    <span class="flex items-center gap-1">
                        <svg class="{{ $svg }} text-black/50" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                        {{ $tenant->alamat ?? 'Kebayoran Baru, Jakarta Selatan' }}
                    </span>

                    {{-- star --}}
                    <span class="flex items-center gap-1 font-semibold text-[#000000]">
                        <svg class="{{ $svg }}" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                        {{ number_format($tenant->rating ?? 4.9, 1) }}
                        <span class="text-black/50 font-normal">({{ $tenant->total_rental ?? 420 }}+ Rental Sukses)</span>
                    </span>
                </div>

                {{-- clock --}}
                <div class="flex items-center gap-1.5 text-xs text-black/60 mt-1">
                    <svg class="{{ $svg }} text-black/50" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    <span>{{ $tenant->jam_operasional ?? '08:00 - 22:00 WIB' }}</span>
                </div>

                <div class="flex flex-wrap items-center gap-2 mt-3.5">
                    {{-- shield-check --}}
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-[4px] bg-[#f9f9f9] border border-black/10 text-[#000000] text-[11px] font-medium">
                        <svg class="{{ $svg }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="m9 12 2 2 4-4"/></svg>
                        99% Kepatuhan BAST
                    </span>

                    {{-- lock --}}
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-[4px] bg-[#f9f9f9] border border-black/10 text-[#000000] text-[11px] font-medium">
                        <svg class="{{ $svg }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        SafeDrop 24 Jam
                    </span>

                    {{-- check-circle-2 --}}
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-[4px] bg-[#f9f9f9] border border-black/10 text-[#000000] text-[11px] font-medium">
                        <svg class="{{ $svg }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>
                        Inspeksi QC Digital
                    </span>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2.5 self-start md:self-center">
            {{-- message-square --}}
            <button type="button" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-[4px] bg-[#000000] hover:bg-black/85 text-[#ffffff] text-xs font-semibold transition">
                <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                Chat Pemilik Tenant
            </button>

            {{-- share-2 --}}
            <button type="button" onclick="navigator.clipboard.writeText(window.location.href); alert('Link katalog tenant berhasil disalin!');"
                    class="p-2.5 rounded-[4px] bg-[#d9d9d9] hover:bg-[#d9d9d9]/80 text-[#000000] transition" title="Bagikan">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" x2="15.42" y1="13.51" y2="17.49"/><line x1="15.41" x2="8.59" y1="6.51" y2="10.49"/></svg>
            </button>
        </div>
    </div>
</section>
