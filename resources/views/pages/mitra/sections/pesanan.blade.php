<section class="overflow-hidden rounded-[4px] border border-neutral-200 bg-[#ffffff] shadow-[0_1px_2px_rgba(0,0,0,0.02)] font-['Montserrat',sans-serif]">
    <div class="flex flex-col gap-4 border-b border-neutral-200 px-5 py-5 sm:flex-row sm:items-center sm:justify-between md:px-6">
        <div>
            <h1 class="text-lg font-bold tracking-tight text-[#000000]">Kelola Pesanan Sewa</h1>
            <p class="mt-1 text-xs text-neutral-500">Daftar pesanan terbaru beserta status penyewaan unit & alur checklist operasional.</p>
        </div>

        <div class="relative w-full sm:w-72">
            <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-neutral-400">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 21l-4.35-4.35M17 10.5a6.5 6.5 0 11-13 0 6.5 6.5 0 0113 0z" />
                </svg>
            </span>
            <input type="search" wire:model.live.debounce.300ms="bookingSearch" autocomplete="off" placeholder="Cari kode, produk, penyewa, status..." class="w-full rounded-[4px] border border-neutral-300 bg-[#ffffff] py-2.5 pl-9 pr-3 text-xs text-[#000000] outline-none transition placeholder:text-neutral-400 focus:border-black focus:ring-1 focus:ring-black">
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs sm:min-w-[900px]">
            <thead class="bg-neutral-50 text-[10px] uppercase tracking-wider text-neutral-500">
                <tr>
                    <th class="px-5 py-3 font-bold">Kode</th>
                    <th class="px-5 py-3 font-bold">Produk</th>
                    <th class="hidden px-5 py-3 font-bold md:table-cell">Penyewa</th>
                    <th class="hidden px-5 py-3 font-bold sm:table-cell">Periode & Tenggat</th>
                    <th class="px-5 py-3 font-bold">Status Sewa</th>
                    <th class="px-5 py-3 font-bold">Pembayaran</th>
                    <th class="px-5 py-3 font-bold">Total</th>
                    <th class="px-5 py-3 font-bold text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-100">
                @forelse ($dashboard['bookings'] as $booking)
                    <tr class="border-b border-neutral-100 transition hover:bg-neutral-50/75">
                        <td class="whitespace-nowrap px-5 py-4 font-bold text-[#000000]">{{ $booking['kode'] }}</td>
                        <td class="px-5 py-4">
                            <div class="font-bold text-black">{{ $booking['produk'] }}</div>
                        </td>
                        <td class="hidden px-5 py-4 text-neutral-600 md:table-cell">
                            <div class="font-bold text-black">{{ $booking['penyewa'] }}</div>
                            <div class="text-[11px] text-neutral-400">NIK: {{ $booking['nik_ktp'] }}</div>
                        </td>
                        <td class="hidden px-5 py-4 sm:table-cell">
                            <div class="font-semibold text-neutral-800">{{ $booking['mulai'] }} - {{ $booking['selesai'] }}</div>
                            @if (! empty($booking['return_deadline']))
                                <div class="text-[11px] font-bold text-[#B45309]">
                                    Tenggat: {{ $booking['return_deadline'] }}
                                </div>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            <span class="inline-flex items-center border px-2 py-0.5 text-[10px] font-bold {{ $this->statusBadgeClasses($booking['status']) }}">
                                {{ $booking['status'] }}
                            </span>
                        </td>
                        <td class="px-5 py-4">
                            @if (! empty($booking['is_payment_verified']))
                                <span class="inline-flex items-center rounded-[4px] border border-[#fff700] bg-[#fff700] px-2 py-0.5 text-[10px] font-bold text-[#000000]">
                                    LUNAS
                                </span>
                            @else
                                <span class="inline-flex items-center rounded-[4px] border border-[#ffdad6] bg-[#ffdad6] px-2 py-0.5 text-[10px] font-bold text-[#ba1a1a]">
                                    {{ strtoupper($booking['status_harga']) }} (Sisa: {{ $this->rupiah($booking['sisa_tagihan']) }})
                                </span>
                            @endif
                        </td>
                        <td class="px-5 py-4 font-bold text-[#000000]">{{ $this->rupiah($booking['total']) }}</td>
                        <td class="px-5 py-4 text-right">
                            <button type="button" wire:click="openChecklistModal({{ $booking['id'] }})" class="inline-flex items-center gap-1.5 rounded-[4px] bg-[#000000] px-3 py-1.5 text-[11px] font-bold text-[#ffffff] shadow-xs transition hover:bg-neutral-800">
                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2M9 14l2 2 4-4" />
                                </svg>
                                <span>Checklist Alur</span>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-xs text-neutral-500">
                            @if (trim($bookingSearch) !== '')
                                Tidak ada pesanan yang cocok dengan pencarian "{{ $bookingSearch }}".
                            @else
                                Belum ada pesanan sewa.
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
