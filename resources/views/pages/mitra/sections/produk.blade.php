                    <section class="overflow-hidden rounded-xl border border-neutral-200 bg-white shadow-[0_1px_2px_rgba(0,0,0,0.02)]">
                        <div class="flex flex-col gap-4 border-b border-neutral-200 px-5 py-5 sm:flex-row sm:items-center sm:justify-between md:px-6">
                            <div>
                                <h1 class="text-lg font-bold tracking-tight text-black">Kelola Produk</h1>
                                <p class="mt-1 text-xs text-neutral-500">Pantau katalog inventaris gear dan ketersediaan unit Anda.</p>
                            </div>

                            <div class="flex flex-col gap-2.5 sm:flex-row sm:items-center">
                                <div class="relative w-full sm:w-64">
                                    <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-neutral-400">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 21l-4.35-4.35M17 10.5a6.5 6.5 0 11-13 0 6.5 6.5 0 0113 0z" />
                                        </svg>
                                    </span>
                                    <input type="search" wire:model.live.debounce.300ms="productSearch" autocomplete="off" placeholder="Cari produk, kategori, status..." class="w-full rounded-md border border-neutral-300 bg-white py-2.5 pl-9 pr-3 text-xs text-black outline-none transition placeholder:text-neutral-400 focus:border-black focus:ring-1 focus:ring-black">
                                </div>

                                <button type="button" wire:click="openProductModal" class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md bg-black px-4 py-2.5 text-xs font-semibold text-white transition hover:bg-neutral-800">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m6-6H6" />
                                    </svg>
                                    <span>Tambah Produk</span>
                                </button>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full min-w-[680px] text-left text-xs">
                                <thead class="bg-neutral-50 text-[10px] uppercase tracking-wider text-neutral-500">
                                    <tr>
                                        <th class="px-6 py-3 font-semibold">Produk</th>
                                        <th class="px-6 py-3 font-semibold">Kategori</th>
                                        <th class="px-6 py-3 font-semibold">Harga Sewa</th>
                                        <th class="px-6 py-3 font-semibold">Stok</th>
                                        <th class="px-6 py-3 font-semibold">Status</th>
                                        <th class="px-6 py-3 text-right font-semibold">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-neutral-100">
                                    @forelse ($dashboard['products'] as $product)
                                        <tr class="transition hover:bg-neutral-50">
                                            <td class="px-6 py-4">
                                                <div class="flex items-center gap-3">
                                                    <img src="{{ $product['foto'] }}" alt="{{ $product['nama'] }}" loading="lazy" class="h-10 w-10 shrink-0 rounded-md border border-neutral-200 object-cover" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1516035069371-29a1b244cc32?auto=format&fit=crop&w=200&q=80';">
                                                    <span class="font-semibold text-black">{{ $product['nama'] }}</span>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 text-neutral-500">{{ $product['kategori'] }}</td>
                                            <td class="px-6 py-4 font-medium text-black">{{ $this->rupiah($product['harga']) }}</td>
                                            <td class="px-6 py-4 text-neutral-500">{{ $product['stok'] }} unit</td>
                                            <td class="px-6 py-4">
                                                <span class="inline-flex rounded-full border px-2.5 py-1 text-[10px] font-semibold {{ $this->statusBadgeClasses($product['status']) }}">{{ $product['status'] }}</span>
                                            </td>
                                            <td class="px-6 py-4 text-right">
                                                <button type="button" wire:click="editProduct({{ $product['id'] }})" class="rounded-md border border-neutral-300 px-3 py-1.5 text-[10px] font-semibold text-black hover:bg-neutral-100">Edit</button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-6 py-12 text-center text-xs text-neutral-500">
                                                @if (trim($productSearch) !== '')
                                                    Tidak ada produk yang cocok dengan pencarian "{{ $productSearch }}".
                                                @else
                                                    Belum ada produk pada katalog Anda.
                                                @endif
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </section>
