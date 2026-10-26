<?php

namespace App\Modules\CatalogAndInventory\UseCases;

use App\Modules\CatalogAndInventory\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class SearchCatalogUseCase
{
    public function __construct(
        protected GetHomeCatalogUseCase $homeCatalogUseCase
    ) {}

    /**
     * Search products by keyword, tenant/location, category, and rental dates.
     *
     * @param  array{
     *     keyword?: string|null,
     *     category_id?: mixed,
     *     tenant_id?: mixed,
     *     location?: string|null,
     *     start_date?: string|null,
     *     end_date?: string|null,
     *     type?: string|null
     * } $filters
     * @return Collection<int, mixed>
     */
    public function execute(array $filters): Collection
    {
        $keyword = trim((string) ($filters['keyword'] ?? ''));
        $categoryId = $filters['category_id'] ?? null;
        $tenantId = $filters['tenant_id'] ?? null;
        $location = trim((string) ($filters['location'] ?? ''));
        $type = $filters['type'] ?? 'all';

        if (Schema::hasTable('produks')) {
            $query = Product::with(['tenant', 'category', 'gallery']);

            if (! empty($keyword)) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('nama_produk', 'like', "%{$keyword}%")
                      ->orWhere('deskripsi_lengkap', 'like', "%{$keyword}%")
                      ->orWhereHas('tenant', function ($tq) use ($keyword) {
                          $tq->where('nama_toko', 'like', "%{$keyword}%");
                      });
                });
            }

            if (! empty($categoryId) && $categoryId !== 'all') {
                $query->where('id_kategori', $categoryId);
            }

            if (! empty($tenantId) && $tenantId !== 'all') {
                $query->where('id_tenant', $tenantId);
            }

            if (! empty($location) && $location !== 'all') {
                $query->whereHas('tenant', function ($tq) use ($location) {
                    $tq->where('alamat_toko', 'like', "%{$location}%");
                });
            }

            if ($type === 'studio') {
                $query->whereHas('category', function ($cq) {
                    $cq->where('nama_kategori', 'like', '%Studio%');
                });
            } elseif ($type === 'camera') {
                $query->whereHas('category', function ($cq) {
                    $cq->where('nama_kategori', 'not like', '%Studio%');
                });
            }

            $dbResults = $query->get();
            if ($dbResults->isNotEmpty()) {
                return $dbResults;
            }
        }

        // Collection filtering on default products
        $products = $this->homeCatalogUseCase->getDefaultFeaturedProducts();

        return $products->filter(function ($item) use ($keyword, $categoryId, $tenantId, $location, $type) {
            $productName = is_array($item) ? ($item['nama_produk'] ?? '') : ($item->nama_produk ?? '');
            $tenantName = is_array($item) ? ($item['tenant_name'] ?? '') : ($item->tenant?->nama_toko ?? '');
            $categoryName = is_array($item) ? ($item['category_name'] ?? '') : ($item->category?->nama_kategori ?? '');
            $prodTenantId = is_array($item) ? ($item['id_tenant'] ?? $item['id_produk'] ?? 0) : ($item->id_tenant ?? 0);
            $prodCatId = is_array($item) ? ($item['id_kategori'] ?? 0) : ($item->id_kategori ?? 0);

            // Filter keyword
            if (! empty($keyword)) {
                $haystack = strtolower("{$productName} {$tenantName} {$categoryName}");
                if (! str_contains($haystack, strtolower($keyword))) {
                    return false;
                }
            }

            // Filter category
            if (! empty($categoryId) && $categoryId !== 'all') {
                if (is_numeric($categoryId) && $prodCatId != $categoryId) {
                    return false;
                }
                if (! is_numeric($categoryId) && ! str_contains(strtolower($categoryName), strtolower($categoryId))) {
                    return false;
                }
            }

            // Filter tenant
            if (! empty($tenantId) && $tenantId !== 'all') {
                if (is_numeric($tenantId) && $prodTenantId != $tenantId) {
                    return false;
                }
                if (! is_numeric($tenantId) && ! str_contains(strtolower($tenantName), strtolower($tenantId))) {
                    return false;
                }
            }

            // Filter location
            if (! empty($location) && $location !== 'all') {
                if (! str_contains(strtolower($tenantName), strtolower($location))) {
                    return false;
                }
            }

            // Filter type
            if ($type === 'studio') {
                if (! str_contains(strtolower($categoryName), 'studio')) {
                    return false;
                }
            } elseif ($type === 'camera') {
                if (str_contains(strtolower($categoryName), 'studio')) {
                    return false;
                }
            }

            return true;
        })->values();
    }
}
