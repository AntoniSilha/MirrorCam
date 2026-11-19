<?php

namespace App\Modules\TenantManagement\Concerns;

use App\Models\DurasiProduk;
use App\Models\GaleriFotoProduk;
use App\Models\KategoriProduk;
use App\Models\Produk;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

trait ManagesTenantProducts
{
    public bool $showProductModal = false;

    public bool $showEditProductModal = false;

    public ?int $editingProductId = null;

    public string $editNamaProduk = '';

    /** @var array<int, string> */
    public array $editColorProduk = [];

    public string $editStokProduk = '0';

    /** @var array<string, string> */
    public array $editHargaDurasi = [];

    /** @var array<int, TemporaryUploadedFile> */
    public array $editFotos = [];

    public string $nama_produk = '';

    public string $id_kategori = '';

    public string $total_stock_unit = '1';

    public string $deskripsi_lengkap = '';

    /** @var array<int, string> */
    public array $color_produk = [];

    /** @var array<int, string> */
    public array $durasi_terpilih = ['1_hari'];

    /** @var array<string, string> */
    public array $harga_durasi = [];

    /** @var array<int, TemporaryUploadedFile> */
    public array $fotos = [];

    #[Computed]
    public function categories()
    {
        return KategoriProduk::query()->orderBy('nama_kategori')->get();
    }

    /**
     * @return array<int, string>
     */
    public function colorOptions(): array
    {
        return ['Black', 'White', 'Grey', 'Silver', 'Red', 'Maroon', 'Orange', 'Yellow', 'Gold', 'Green', 'Olive', 'Teal', 'Cyan', 'Blue', 'Navy', 'Purple', 'Violet', 'Pink', 'Brown', 'Beige', 'Cream', 'Rose Gold'];
    }

    /** @return array<string, string> */
    public function colorSwatches(): array
    {
        return [
            'Black' => '#111827', 'White' => '#ffffff', 'Grey' => '#9ca3af', 'Silver' => '#d1d5db',
            'Red' => '#ef4444', 'Maroon' => '#7f1d1d', 'Orange' => '#f97316', 'Yellow' => '#facc15',
            'Gold' => '#d4af37', 'Green' => '#22c55e', 'Olive' => '#808000', 'Teal' => '#14b8a6',
            'Cyan' => '#06b6d4', 'Blue' => '#3b82f6', 'Navy' => '#1e3a8a', 'Purple' => '#8b5cf6',
            'Violet' => '#7c3aed', 'Pink' => '#ec4899', 'Brown' => '#92400e', 'Beige' => '#d6c4a1',
            'Cream' => '#fff7d6', 'Rose Gold' => '#b76e79',
        ];
    }

    public function editProduct(int $productId): void
    {
        $tenant = $this->tenant();
        $product = Produk::query()->where('id_tenant', $tenant?->id_tenant)->findOrFail($productId);
        $product->load('durasi');

        $this->resetValidation();
        $this->editingProductId = $product->id_produk;
        $this->editNamaProduk = $product->nama_produk;
        $existingColors = array_filter(array_map('trim', explode(',', (string) $product->color_produk)));
        $this->editColorProduk = array_values(array_filter(array_map(
            fn ($color) => collect($this->colorOptions())->first(fn ($option) => strtolower($option) === strtolower($color)),
            $existingColors
        )));
        $this->editStokProduk = (string) $product->total_stock_unit;
        $this->editHargaDurasi = $this->emptyHargaDurasi();
        foreach ($product->durasi as $durasi) {
            if (array_key_exists($durasi->durasi, $this->editHargaDurasi)) {
                $this->editHargaDurasi[$durasi->durasi] = (string) $durasi->harga;
            }
        }
        $this->editFotos = [];
        $this->showEditProductModal = true;
    }

    public function updateProduct(): void
    {
        $validated = $this->validate([
            'editingProductId' => ['required', 'integer'],
            'editNamaProduk' => ['required', 'string', 'max:120'],
            'editColorProduk' => ['array'],
            'editColorProduk.*' => ['string', Rule::in($this->colorOptions())],
            'editStokProduk' => ['required', 'integer', 'min:0'],
            'editHargaDurasi' => ['required', 'array'],
            'editHargaDurasi.*' => ['nullable', 'numeric', 'min:0'],
            'editFotos' => ['array', 'max:8'],
            'editFotos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $tenant = $this->tenant();
        $product = Produk::query()->where('id_tenant', $tenant?->id_tenant)->findOrFail($validated['editingProductId']);
        $prices = array_filter($validated['editHargaDurasi'], fn ($price) => $price !== null && $price !== '');
        if ($prices === []) {
            $this->addError('editHargaDurasi', 'Isi harga untuk minimal satu durasi.');

            return;
        }
        DB::transaction(function () use ($product, $validated, $prices): void {
            $product->update([
                'nama_produk' => $validated['editNamaProduk'],
                'color_produk' => ! empty($validated['editColorProduk']) ? implode(', ', $validated['editColorProduk']) : null,
                'total_stock_unit' => (int) $validated['editStokProduk'],
            ]);

            $product->durasi()->delete();
            foreach ($prices as $code => $price) {
                DurasiProduk::query()->create([
                    'id_produk' => $product->id_produk,
                    'durasi' => $code,
                    'nama_durasi' => $this->durasiOptions()[$code],
                    'harga' => $price,
                ]);
            }

            if ($this->editFotos !== []) {
                $product->galeri()->delete();

                foreach ($this->editFotos as $foto) {
                    $path = $foto->store('produk', 'public');
                    if ($path) {
                        GaleriFotoProduk::query()->create([
                            'id_produk' => $product->id_produk,
                            'url_foto_produk' => Storage::disk('public')->url($path),
                        ]);
                    }
                }
            }
        });

        $this->showEditProductModal = false;
        $this->editingProductId = null;
        $this->editFotos = [];
        unset($this->dashboard, $this->chart);
        session()->flash('status', 'Produk berhasil diperbarui.');
    }

    /**
     * @return array<string, string>
     */
    public function durasiOptions(): array
    {
        return [
            '6_jam' => '6 Jam',
            '12_jam' => '12 Jam',
            '1_hari' => '1 Hari',
            '3_hari' => '3 Hari',
            '7_hari' => '7 Hari',
            '15_hari' => '15 Hari',
            '1_bulan' => '1 Bulan',
            '2_bulan' => '2 Bulan',
            '3_bulan' => '3 Bulan',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function emptyHargaDurasi(): array
    {
        return array_fill_keys(array_keys($this->durasiOptions()), '');
    }

    public function openProductModal(): void
    {
        $this->resetValidation();
        $this->nama_produk = '';
        $this->id_kategori = '';
        $this->total_stock_unit = '1';
        $this->deskripsi_lengkap = '';
        $this->color_produk = [];
        $this->durasi_terpilih = ['1_hari'];
        $this->harga_durasi = $this->emptyHargaDurasi();
        $this->fotos = [];
        $this->showProductModal = true;
    }

    public function removeFoto(int $index): void
    {
        unset($this->fotos[$index]);
        $this->fotos = array_values($this->fotos);
    }

    public function closeProductModal(): void
    {
        $this->showProductModal = false;
        $this->resetValidation();
    }

    public function saveProduct(): void
    {
        $tenant = $this->tenant();

        if (! $tenant) {
            $this->addError('nama_produk', 'Lengkapi profil tenant terlebih dahulu sebelum menambahkan produk.');

            return;
        }

        $rules = [
            'nama_produk' => ['required', 'string', 'max:120'],
            'id_kategori' => ['required', 'exists:kategori_produk,id_kategori'],
            'total_stock_unit' => ['required', 'integer', 'min:0'],
            'deskripsi_lengkap' => ['nullable', 'string', 'max:1000'],
            'color_produk' => ['array'],
            'color_produk.*' => ['string', 'max:30'],
            'durasi_terpilih' => ['required', 'array', 'min:1'],
            'durasi_terpilih.*' => ['string', Rule::in(array_keys($this->durasiOptions()))],
            'harga_durasi' => ['array'],
            'fotos' => ['array', 'max:8'],
            'fotos.*' => ['image', 'max:4096'],
        ];

        foreach ($this->durasi_terpilih as $kodeDurasi) {
            $rules["harga_durasi.{$kodeDurasi}"] = ['required', 'numeric', 'min:0'];
        }

        $validated = $this->validate($rules, [
            'durasi_terpilih.required' => 'Pilih minimal satu durasi sewa.',
            'durasi_terpilih.min' => 'Pilih minimal satu durasi sewa.',
            'harga_durasi.*.required' => 'Harga untuk durasi yang dipilih wajib diisi.',
            'harga_durasi.*.numeric' => 'Harga durasi harus berupa angka.',
            'harga_durasi.*.min' => 'Harga durasi tidak boleh negatif.',
        ]);

        $warnaProduk = array_values(array_unique($validated['color_produk'] ?? []));
        DB::transaction(function () use ($tenant, $validated, $warnaProduk): void {
            $produk = Produk::query()->create([
                'id_tenant' => $tenant->id_tenant,
                'id_kategori' => (int) $validated['id_kategori'],
                'nama_produk' => $validated['nama_produk'],
                'color_produk' => $warnaProduk === [] ? null : implode(', ', $warnaProduk),
                'deskripsi_lengkap' => $validated['deskripsi_lengkap'] ?: '-',
                'total_stock_unit' => (int) $validated['total_stock_unit'],
            ]);

            foreach ($validated['durasi_terpilih'] as $kodeDurasi) {
                DurasiProduk::query()->create([
                    'id_produk' => $produk->id_produk,
                    'durasi' => $kodeDurasi,
                    'nama_durasi' => $this->durasiOptions()[$kodeDurasi],
                    'harga' => (float) $validated['harga_durasi'][$kodeDurasi],
                ]);
            }

            foreach ($this->fotos as $foto) {
                $path = $foto->store('produk', 'public');

                if (! $path) {
                    continue;
                }

                GaleriFotoProduk::query()->create([
                    'id_produk' => $produk->id_produk,
                    'url_foto_produk' => Storage::disk('public')->url($path),
                ]);
            }
        });

        unset($this->dashboard, $this->chart);

        $this->showProductModal = false;
        $this->resetValidation();
        $this->nama_produk = '';
        $this->id_kategori = '';
        $this->total_stock_unit = '1';
        $this->deskripsi_lengkap = '';
        $this->color_produk = [];
        $this->durasi_terpilih = ['1_hari'];
        $this->harga_durasi = [];
        $this->fotos = [];

        session()->flash('status', 'Produk baru berhasil ditambahkan.');
    }
}
