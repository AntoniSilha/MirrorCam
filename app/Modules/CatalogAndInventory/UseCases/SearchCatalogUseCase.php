<?php

namespace App\Modules\CatalogAndInventory\UseCases;

use App\Modules\CatalogAndInventory\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

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

            return $query->get();
        }

        return collect();
    }
}
