                    <section class="overflow-hidden rounded-xl border border-neutral-200 bg-white shadow-[0_1px_2px_rgba(0,0,0,0.02)]">
                        <div class="flex flex-col gap-4 border-b border-neutral-200 px-5 py-5 sm:flex-row sm:items-center sm:justify-between md:px-6">
                            <div>
                                <h1 class="text-lg font-bold tracking-tight text-black">Kelola Pesanan Sewa</h1>
                                <p class="mt-1 text-xs text-neutral-500">Daftar pesanan terbaru beserta status penyewaan unit.</p>
                            </div>

                            <div class="relative w-full sm:w-72">
                                <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-neutral-400">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 21l-4.35-4.35M17 10.5a6.5 6.5 0 11-13 0 6.5 6.5 0 0113 0z" />
                                    </svg>
                                </span>
                                <input type="search" wire:model.live.debounce.300ms="bookingSearch" autocomplete="off" placeholder="Cari kode, produk, penyewa, status..." class="w-full rounded-md border border-neutral-300 bg-white py-2.5 pl-9 pr-3 text-xs text-black outline-none transition placeholder:text-neutral-400 focus:border-black focus:ring-1 focus:ring-black">
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full min-w-[860px] text-left text-xs">
                                <thead class="bg-neutral-50 text-[10px] uppercase tracking-wider text-neutral-500">
                                    <tr>
                                        <th class="px-6 py-3 font-semibold">Kode</th>
                                        <th class="px-6 py-3 font-semibold">Produk</th>
                                        <th class="px-6 py-3 font-semibold">Penyewa</th>
                                        <th class="px-6 py-3 font-semibold">Periode</th>
                                        <th class="px-6 py-3 font-semibold">Status</th>
                                        <th class="px-6 py-3 font-semibold">Total</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-neutral-100">
                                    @forelse ($dashboard['bookings'] as $booking)
                                        <tr class="transition hover:bg-neutral-50">
                                            <td class="px-6 py-4 font-semibold text-black">{{ $booking['kode'] }}</td>
                                            <td class="px-6 py-4 text-neutral-600">{{ $booking['produk'] }}</td>
                                            <td class="px-6 py-4 text-neutral-600">{{ $booking['penyewa'] }}</td>
                                            <td class="px-6 py-4 text-neutral-500">{{ $booking['mulai'] }} - {{ $booking['selesai'] }}</td>
                                            <td class="px-6 py-4">
                                                <span class="inline-flex rounded-full border px-2.5 py-1 text-[10px] font-semibold {{ $this->statusBadgeClasses($booking['status']) }}">{{ $booking['status'] }}</span>
                                            </td>
                                            <td class="px-6 py-4 font-medium text-black">{{ $this->rupiah($booking['total']) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-6 py-12 text-center text-xs text-neutral-500">
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
