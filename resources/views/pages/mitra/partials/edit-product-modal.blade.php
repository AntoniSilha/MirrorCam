    @if ($showEditProductModal)
        <div class="fixed inset-0 z-[70] flex items-center justify-center bg-black/50 px-4 py-6" wire:click.self="$set('showEditProductModal', false)">
            <form wire:submit="updateProduct" class="max-h-[90vh] w-full max-w-4xl space-y-4 overflow-y-auto rounded-xl bg-white p-5 shadow-xl sm:p-6">
                <div class="flex items-center justify-between">
                    <h3 class="text-base font-bold text-black">Edit Produk</h3>
                    <button type="button" wire:click="$set('showEditProductModal', false)" class="text-xl text-neutral-500">&times;</button>
                </div>
                <div class="grid gap-5 md:grid-cols-2">
                    <div class="space-y-4">
                        <div>
                            <label class="mb-1.5 block text-[11px] font-semibold text-neutral-700">Nama Produk</label>
                            <input type="text" wire:model="editNamaProduk" class="w-full rounded-md border border-neutral-300 px-3 py-2.5 text-xs focus:border-black focus:ring-1 focus:ring-black">
                            @error('editNamaProduk') <p class="mt-1 text-[10px] text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-1.5 block text-[11px] font-semibold text-neutral-700">Stok Unit</label>
                            <input type="number" min="0" wire:model="editStokProduk" class="w-full rounded-md border border-neutral-300 px-3 py-2.5 text-xs focus:border-black focus:ring-1 focus:ring-black">
                            @error('editStokProduk') <p class="mt-1 text-[10px] text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-2 block text-[11px] font-semibold text-neutral-700">Harga Sewa per Durasi</label>
                            <div class="grid gap-2 sm:grid-cols-2">
                                @foreach ($this->durasiOptions() as $code => $label)
                                    <label class="flex items-center justify-between gap-2 rounded-md border border-neutral-200 px-3 py-2 text-[11px] text-neutral-700">
                                        <span>{{ $label }}</span>
                                        <input type="number" min="0" wire:model="editHargaDurasi.{{ $code }}" placeholder="Tidak tersedia" class="w-28 rounded border border-neutral-300 px-2 py-1 text-right text-xs text-black">
                                    </label>
                                @endforeach
                            </div>
                            <p class="mt-1 text-[10px] text-neutral-400">Kosongkan durasi yang tidak disewakan.</p>
                            @error('editHargaDurasi') <p class="mt-1 text-[10px] text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <label class="mb-2 block text-[11px] font-semibold text-neutral-700">Warna Produk</label>
                            @php($editColorSwatches = $this->colorSwatches())
                            <div class="flex flex-wrap gap-2">
                                @foreach ($this->colorOptions() as $color)
                                    <label class="cursor-pointer" title="{{ $color }}">
                                        <input type="checkbox" wire:model="editColorProduk" value="{{ $color }}" class="peer sr-only">
                                        <span class="inline-flex flex-col items-center gap-1 rounded-md border border-neutral-300 bg-white p-1.5 text-[9px] font-semibold text-neutral-700 peer-checked:border-black peer-checked:ring-2 peer-checked:ring-black peer-checked:ring-offset-1">
                                            <span class="h-7 w-7 rounded-full border border-black/20" style="background: {{ $editColorSwatches[$color] }}"></span>{{ $color }}
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                            <p class="mt-1 text-[10px] text-neutral-400">Pilih satu atau beberapa warna.</p>
                            @error('editColorProduk.*') <p class="mt-1 text-[10px] text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-1.5 block text-[11px] font-semibold text-neutral-700">Gambar Produk</label>
                            <input type="file" wire:model="editFotos" multiple accept="image/png,image/jpeg,image/webp" class="block w-full text-[11px] text-neutral-600">
                            <p class="mt-1 text-[10px] text-neutral-400">Unggah gambar baru untuk mengganti galeri saat ini (maks. 8 gambar, 4MB per gambar).</p>
                            @error('editFotos.*') <p class="mt-1 text-[10px] text-red-600">{{ $message }}</p> @enderror
                        </div>
                        @if ($editFotos)
                            <div class="grid grid-cols-4 gap-2">
                                @foreach ($editFotos as $foto)
                                    <img src="{{ $foto->temporaryUrl() }}" alt="Pratinjau gambar" class="aspect-square w-full rounded-md object-cover">
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
                <div class="flex justify-end gap-2 border-t border-neutral-100 pt-4">
                    <button type="button" wire:click="$set('showEditProductModal', false)" class="rounded-md border border-neutral-300 px-4 py-2 text-xs font-semibold">Batal</button>
                    <button type="submit" wire:loading.attr="disabled" wire:target="updateProduct, editFotos" class="rounded-md bg-black px-4 py-2 text-xs font-semibold text-white disabled:opacity-60">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    @endif
