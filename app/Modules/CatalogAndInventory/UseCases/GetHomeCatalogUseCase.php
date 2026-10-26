<?php

namespace App\Modules\CatalogAndInventory\UseCases;

use App\Modules\CatalogAndInventory\Models\Category;
use App\Modules\CatalogAndInventory\Models\Product;
use App\Modules\CatalogAndInventory\Models\Tenant;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class GetHomeCatalogUseCase
{
    /**
     * Execute use case to get all home catalog data.
     *
     * @return array{
     *     categories: Collection<int, mixed>,
     *     tenants: Collection<int, mixed>,
     *     featuredProducts: Collection<int, mixed>
     * }
     */
    public function execute(): array
    {
        return [
            'categories' => $this->getCategories(),
            'tenants' => $this->getTenants(),
            'featuredProducts' => $this->getFeaturedProducts(),
        ];
    }

    /**
     * Get popular equipment categories
     *
     * @return Collection<int, mixed>
     */
    public function getCategories(): Collection
    {
        if (Schema::hasTable('kategori_produk')) {
            $dbCategories = Category::withCount('products')->get();
            if ($dbCategories->isNotEmpty()) {
                return $dbCategories;
            }
        }

        return $this->getDefaultCategories();
    }

    /**
     * Get active official tenant partners
     *
     * @return Collection<int, mixed>
     */
    public function getTenants(): Collection
    {
        if (Schema::hasTable('tenants')) {
            $dbTenants = Tenant::withCount('products')
                ->where('status_mitra', 'aktif')
                ->orWhere('status_mitra', 'official')
                ->get();

            if ($dbTenants->isNotEmpty()) {
                return $dbTenants;
            }
        }

        return $this->getDefaultTenants();
    }

    /**
     * Get verified recommended products
     *
     * @return Collection<int, mixed>
     */
    public function getFeaturedProducts(): Collection
    {
        if (Schema::hasTable('produks')) {
            $dbProducts = Product::with(['tenant', 'category', 'gallery'])->get();
            if ($dbProducts->isNotEmpty()) {
                return $dbProducts;
            }
        }

        return $this->getDefaultFeaturedProducts();
    }

    /**
     * Default popular categories conforming to HomePage mockup
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function getDefaultCategories(): Collection
    {
        return collect([
            [
                'id_kategori' => 1,
                'nama_kategori' => 'Kamera Cinema & Mirrorless',
                'slug' => 'kamera-cinema-mirrorless',
                'icon' => 'camera',
                'deskripsi' => 'Kamera profesional untuk produksi film & fotografi',
                'total_produk' => 42,
            ],
            [
                'id_kategori' => 2,
                'nama_kategori' => 'Lensa Prime & Cine',
                'slug' => 'lensa-prime-cine',
                'icon' => 'lens',
                'deskripsi' => 'Lensa tajam dengan aperture lebar untuk hasil sinematik',
                'total_produk' => 68,
            ],
            [
                'id_kategori' => 3,
                'nama_kategori' => 'Lighting & Rigging',
                'slug' => 'lighting-rigging',
                'icon' => 'lighting',
                'deskripsi' => 'Lampu continuous, softbox, stand, dan c-stand',
                'total_produk' => 35,
            ],
            [
                'id_kategori' => 4,
                'nama_kategori' => 'Audio & Monitor',
                'slug' => 'audio-monitor',
                'icon' => 'audio',
                'deskripsi' => 'Mic wireless lavalier, shotgun, dan monitor sutradara',
                'total_produk' => 29,
            ],
            [
                'id_kategori' => 5,
                'nama_kategori' => 'Drone & Gimbal',
                'slug' => 'drone-gimbal',
                'icon' => 'drone',
                'deskripsi' => 'Stabilizer 3-axis dan drone aerial 4K HDR',
                'total_produk' => 24,
            ],
            [
                'id_kategori' => 6,
                'nama_kategori' => 'Studio',
                'slug' => 'studio',
                'icon' => 'studio',
                'deskripsi' => 'Ruang studio foto, podcast, dan syuting ber-AC',
                'total_produk' => 16,
            ],
        ]);
    }

    /**
     * Default tenant partners conforming to HomePage mockup
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function getDefaultTenants(): Collection
    {
        return collect([
            [
                'id_tenant' => 1,
                'nama_toko' => 'Indonesia Battery',
                'category_label' => 'Rental & Service',
                'badge_text' => 'OFFICIAL PARTNER',
                'bio_deskripsi' => 'Penyedia gear shooting terpercaya di Jawa Timur, spesialis cinema camera Sony FX series & Zeiss lenses.',
                'rating' => 4.9,
                'unit_ready' => 48,
                'kota' => 'Banyuwangi',
                'status_mitra' => 'aktif',
                'is_official' => true,
                'whatsapp_resmi' => '6281234567890',
            ],
            [
                'id_tenant' => 2,
                'nama_toko' => 'Studio Proyek ID',
                'category_label' => 'Master Rental',
                'badge_text' => 'OFFICIAL PARTNER',
                'bio_deskripsi' => 'Pusat sewa kamera mirrorless, rigging stabilizer, & sound recorder profesional untuk kebutuhan video komersial.',
                'rating' => 4.9,
                'unit_ready' => 55,
                'kota' => 'Surabaya',
                'status_mitra' => 'aktif',
                'is_official' => true,
                'whatsapp_resmi' => '6281987654321',
            ],
            [
                'id_tenant' => 3,
                'nama_toko' => 'Koo Gallery',
                'category_label' => 'Rental Kamera & Studio',
                'badge_text' => 'OFFICIAL PARTNER',
                'bio_deskripsi' => 'Rental kamera & studio modern terlengkap di Solo dan sekitarnya. Menyediakan paket lighting studio, flash wireless, dan kamera pro.',
                'rating' => 4.8,
                'unit_ready' => 36,
                'kota' => 'Bandung',
                'status_mitra' => 'aktif',
                'is_official' => true,
                'whatsapp_resmi' => '6281122334455',
            ],
        ]);
    }

    /**
     * Default featured products conforming to HomePage mockup (8 items)
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function getDefaultFeaturedProducts(): Collection
    {
        return collect([
            [
                'id_produk' => 1,
                'nama_produk' => 'Sony FX30 Cinema Line Full...',
                'tenant_name' => 'Indonesia Battery',
                'category_name' => 'Kamera Cinema & Mirrorless',
                'harga_sewa' => 550000,
                'price_unit' => 'hari',
                'formatted_price' => 'Rp 550.000 / hari',
                'rating' => 4.9,
                'review_count' => 32,
                'total_stok_unit' => 5,
                'image' => 'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?auto=format&fit=crop&w=600&q=80',
                'is_available' => true,
            ],
            [
                'id_produk' => 2,
                'nama_produk' => 'Sony FE 24–70 mm f/2.8 GM II',
                'tenant_name' => 'Lensa Prima Surabaya',
                'category_name' => 'Lensa Prime & Cine',
                'harga_sewa' => 250000,
                'price_unit' => 'hari',
                'formatted_price' => 'Rp 250.000 / hari',
                'rating' => 4.9,
                'review_count' => 45,
                'total_stok_unit' => 8,
                'image' => 'https://images.unsplash.com/photo-1617005082133-548c4dd27f35?auto=format&fit=crop&w=600&q=80',
                'is_available' => true,
            ],
            [
                'id_produk' => 3,
                'nama_produk' => 'Canon EOS R6 Mark II (Body)',
                'tenant_name' => 'Koo Gallery Solo',
                'category_name' => 'Kamera Cinema & Mirrorless',
                'harga_sewa' => 400000,
                'price_unit' => 'hari',
                'formatted_price' => 'Rp 400.000 / hari',
                'rating' => 4.8,
                'review_count' => 19,
                'total_stok_unit' => 4,
                'image' => 'https://images.unsplash.com/photo-1502920917128-1aa500764cbd?auto=format&fit=crop&w=600&q=80',
                'is_available' => true,
            ],
            [
                'id_produk' => 4,
                'nama_produk' => 'Sony Alpha 7 IV (A7 IV) Body',
                'tenant_name' => 'Studio Proyek ID',
                'category_name' => 'Kamera Cinema & Mirrorless',
                'harga_sewa' => 350000,
                'price_unit' => 'hari',
                'formatted_price' => 'Rp 350.000 / hari',
                'rating' => 4.9,
                'review_count' => 54,
                'total_stok_unit' => 6,
                'image' => 'https://images.unsplash.com/photo-1512790182412-b19e6d62bc39?auto=format&fit=crop&w=600&q=80',
                'is_available' => true,
            ],
            [
                'id_produk' => 5,
                'nama_produk' => 'Sewa Studio Sun Gallery',
                'tenant_name' => 'Flash Studio',
                'category_name' => 'Studio',
                'harga_sewa' => 150000,
                'price_unit' => 'jam',
                'formatted_price' => 'Rp 150.000 / jam',
                'rating' => 5.0,
                'review_count' => 12,
                'total_stok_unit' => 2,
                'image' => 'https://images.unsplash.com/photo-1598488035139-bdbb2231ce04?auto=format&fit=crop&w=600&q=80',
                'is_available' => true,
            ],
            [
                'id_produk' => 6,
                'nama_produk' => 'Aputure LS 600d Pro + Light...',
                'tenant_name' => 'Lighting Rental',
                'category_name' => 'Lighting & Rigging',
                'harga_sewa' => 375000,
                'price_unit' => 'hari',
                'formatted_price' => 'Rp 375.000 / hari',
                'rating' => 4.9,
                'review_count' => 28,
                'total_stok_unit' => 3,
                'image' => 'https://images.unsplash.com/photo-1524368535928-5b5e00ddc76b?auto=format&fit=crop&w=600&q=80',
                'is_available' => true,
            ],
            [
                'id_produk' => 7,
                'nama_produk' => 'DJI Ronin RS 3 Pro Gimbal...',
                'tenant_name' => 'Rigging Surabaya',
                'category_name' => 'Drone & Gimbal',
                'harga_sewa' => 220000,
                'price_unit' => 'hari',
                'formatted_price' => 'Rp 220.000 / hari',
                'rating' => 4.8,
                'review_count' => 37,
                'total_stok_unit' => 5,
                'image' => 'https://images.unsplash.com/photo-1574717024653-61fd2cf4d44d?auto=format&fit=crop&w=600&q=80',
                'is_available' => true,
            ],
            [
                'id_produk' => 8,
                'nama_produk' => 'Canon RF 50mm f/1.2L USM...',
                'tenant_name' => 'Koo Gallery',
                'category_name' => 'Lensa Prime & Cine',
                'harga_sewa' => 280000,
                'price_unit' => 'hari',
                'formatted_price' => 'Rp 280.000 / hari',
                'rating' => 4.9,
                'review_count' => 41,
                'total_stok_unit' => 7,
                'image' => 'https://images.unsplash.com/photo-1622818451877-c999331f4a9b?auto=format&fit=crop&w=600&q=80',
                'is_available' => true,
            ],
        ]);
    }
}
