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
     * Get popular equipment categories from database
     *
     * @return Collection<int, mixed>
     */
    public function getCategories(): Collection
    {
        if (Schema::hasTable('kategori_produk')) {
            return Category::withCount('products')->get();
        }

        return collect();
    }

    /**
     * Get active official tenant partners from database
     *
     * @return Collection<int, mixed>
     */
    public function getTenants(): Collection
    {
        if (Schema::hasTable('tenants')) {
            return Tenant::withCount('products')
                ->where(function ($q) {
                    $q->where('status_mitra', 'aktif')
                      ->orWhere('status_mitra', 'official');
                })
                ->get();
        }

        return collect();
    }

    /**
     * Get verified recommended products from database
     *
     * @return Collection<int, mixed>
     */
    public function getFeaturedProducts(): Collection
    {
        if (Schema::hasTable('produks')) {
            return Product::with(['tenant', 'category', 'gallery'])->get();
        }

        return collect();
    }
}
