    @if ($showProductModal)
        <div class="fixed inset-0 z-[60] flex items-center justify-center bg-black/50 px-4 py-6" wire:click.self="closeProductModal">
            <div class="flex max-h-[92vh] w-full max-w-2xl flex-col overflow-hidden rounded-xl bg-white shadow-xl">
                <div class="flex items-start justify-between border-b border-neutral-200 px-6 py-4">
                    <div>
                        <h3 class="text-base font-bold text-black">Tambah Produk Baru</h3>
                        <p class="mt-1 text-[11px] text-neutral-500">Lengkapi data inventaris gear yang akan disewakan.</p>
                    </div>
                    <button type="button" wire:click="closeProductModal" class="rounded-md p-1 text-neutral-500 transition hover:bg-neutral-100 hover:text-black">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form wire:submit="saveProduct" class="max-h-[calc(92vh-73px)] space-y-4 overflow-y-auto px-6 py-5">
                    <div>
                        <label class="mb-1.5 block text-[11px] font-semibold text-neutral-700">Nama Produk</label>
                        <input type="text" wire:model="nama_produk" placeholder="Contoh: Sony FX3 Cinema Line" class="w-full rounded-md border border-neutral-300 bg-white px-3 py-2.5 text-xs text-black outline-none transition focus:border-black focus:ring-1 focus:ring-black">
                        @error('nama_produk') <p class="mt-1 text-[10px] text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid gap-4">
                        <div>
                            <label class="mb-1.5 block text-[11px] font-semibold text-neutral-700">Kategori</label>
                            <select wire:model="id_kategori" class="w-full rounded-md border border-neutral-300 bg-white px-3 py-2.5 text-xs text-black outline-none transition focus:border-black focus:ring-1 focus:ring-black">
                                <option value="">Pilih kategori</option>
                                @foreach ($this->categories as $category)
                                    <option value="{{ $category->id_kategori }}">{{ $category->nama_kategori }}</option>
                                @endforeach
                            </select>
                            @error('id_kategori') <p class="mt-1 text-[10px] text-red-600">{{ $message }}</p> @enderror
                        </div>

                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-[11px] font-semibold text-neutral-700">Total Stok Unit</label>
                            <input type="number" min="0" wire:model="total_stock_unit" class="w-full rounded-md border border-neutral-300 bg-white px-3 py-2.5 text-xs text-black outline-none transition focus:border-black focus:ring-1 focus:ring-black">
                            @error('total_stock_unit') <p class="mt-1 text-[10px] text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-1.5 block text-[11px] font-semibold text-neutral-700">Galeri Foto</label>
                            <input type="file" wire:model="fotos" multiple accept="image/png,image/jpeg,image/webp" class="block w-full cursor-pointer text-[11px] text-neutral-600 file:mr-3 file:cursor-pointer file:rounded-md file:border-0 file:bg-black file:px-3 file:py-2 file:text-[11px] file:font-semibold file:text-white hover:file:bg-neutral-800">
                            <p class="mt-1 text-[10px] text-neutral-400">JPG/PNG/WEBP, maks. 4MB per foto (maks. 8 foto).</p>
                            @error('fotos.*') <p class="mt-1 text-[10px] text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div wire:loading wire:target="fotos" class="text-[10px] font-semibold text-neutral-500">Mengunggah foto...</div>

                    @if (! empty($fotos))
                        <div class="grid grid-cols-3 gap-2 sm:grid-cols-4">
                            @foreach ($fotos as $index => $foto)
                                <div wire:key="foto-{{ $index }}" class="group relative aspect-square overflow-hidden rounded-md border border-neutral-200 bg-neutral-50">
                                    @if ($foto)
                                        <img src="{{ $foto->temporaryUrl() }}" alt="Pratinjau foto {{ $index + 1 }}" class="h-full w-full object-cover">
                                    @endif
                                    <button type="button" wire:click="removeFoto({{ $index }})" title="Hapus foto" class="absolute right-1 top-1 flex h-5 w-5 items-center justify-center rounded-full bg-black/70 text-[10px] font-bold text-white transition hover:bg-black">
                                        &times;
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <div>
                        <label class="mb-1.5 block text-[11px] font-semibold text-neutral-700">Warna Tersedia</label>
                        @php($colorSwatches = $this->colorSwatches())
                        <div class="flex flex-wrap gap-2">
                            @foreach ($this->colorOptions() as $color)
                                <label class="cursor-pointer" title="{{ $color }}">
                                    <input type="checkbox" wire:model="color_produk" value="{{ $color }}" class="peer sr-only">
                                    <span class="inline-flex flex-col items-center gap-1 rounded-md border border-neutral-300 bg-white p-1.5 text-[9px] font-semibold text-neutral-700 transition peer-checked:border-black peer-checked:ring-2 peer-checked:ring-black peer-checked:ring-offset-1">
                                        <span class="h-7 w-7 rounded-full border border-black/20" style="background: {{ $colorSwatches[$color] }}"></span>
                                        {{ $color }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        @error('color_produk.*') <p class="mt-1 text-[10px] text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-1.5 block text-[11px] font-semibold text-neutral-700">Pilihan Durasi & Harga Sewa</label>
                        <p class="mb-2 text-[10px] text-neutral-400">Centang durasi yang tersedia, lalu isi harga sewanya.</p>
                        <div class="space-y-2">
                            @foreach ($this->durasiOptions() as $value => $label)
                                <div class="flex flex-col gap-2 rounded-md border border-neutral-200 px-3 py-2 sm:flex-row sm:items-center sm:justify-between">
                                    <label class="flex cursor-pointer items-center gap-2 text-[11px] font-semibold text-neutral-700">
                                        <input type="checkbox" wire:model="durasi_terpilih" value="{{ $value }}" class="h-4 w-4 rounded border-neutral-300 text-black focus:ring-black">
                                        {{ $label }}
                                    </label>
                                    <div class="relative w-full sm:w-48">
                                        <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-[11px] text-neutral-400">Rp</span>
                                        <input type="number" min="0" wire:model="harga_durasi.{{ $value }}" placeholder="Harga sewa" class="w-full rounded-md border border-neutral-300 bg-white py-2 pl-9 pr-3 text-xs text-black outline-none transition placeholder:text-neutral-400 focus:border-black focus:ring-1 focus:ring-black">
                                    </div>
                                </div>
                                @error("harga_durasi.$value") <p class="text-[10px] text-red-600">{{ $message }}</p> @enderror
                            @endforeach
                        </div>
                        @error('durasi_terpilih') <p class="mt-1 text-[10px] text-red-600">{{ $message }}</p> @enderror
                        @error('durasi_terpilih.*') <p class="mt-1 text-[10px] text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-1.5 block text-[11px] font-semibold text-neutral-700">Deskripsi Lengkap</label>
                        <textarea rows="3" wire:model="deskripsi_lengkap" placeholder="Spesifikasi singkat dan kelengkapan unit" class="w-full resize-none rounded-md border border-neutral-300 bg-white px-3 py-2.5 text-xs text-black outline-none transition focus:border-black focus:ring-1 focus:ring-black"></textarea>
                        @error('deskripsi_lengkap') <p class="mt-1 text-[10px] text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex items-center justify-end gap-3 border-t border-neutral-100 pt-4">
                        <button type="button" wire:click="closeProductModal" class="rounded-md border border-neutral-300 bg-white px-4 py-2.5 text-xs font-semibold text-black transition hover:bg-neutral-50">Batal</button>
                        <button type="submit" wire:loading.attr="disabled" wire:target="saveProduct, fotos" class="rounded-md bg-black px-4 py-2.5 text-xs font-semibold text-white transition hover:bg-neutral-800 disabled:opacity-60">Simpan Produk</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
