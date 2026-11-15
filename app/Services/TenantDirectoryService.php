<?php

namespace App\Services;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class TenantDirectoryService
{
    /**
     * Get active tenants with eager loading to prevent N+1 queries.
     * Only retrieves tenants with status 'aktif' per SRS UC-02 & UC-23.
     *
     * @param array{
     *     search?: ?string,
     *     rating?: ?string,
     *     per_page?: ?int
     * } $filters
     * @return array{
     *     tenants: Collection<int, Tenant>,
     *     total: int,
     *     displayed: int,
     *     remaining: int,
     *     perPage: int,
     *     rating: string,
     *     search: string
     * }
     */
    public function getTenants(array $filters = [], int $perPage = 9): array
    {
        $search = trim($filters['search'] ?? '');
        $rating = trim($filters['rating'] ?? '');
        $requestedPerPage = max(1, (int) ($filters['per_page'] ?? $perPage));

        $stockColumn = Schema::hasColumn('produks', 'total_stok_unit')
            ? 'total_stok_unit'
            : 'total_stock_unit';

        $baseQuery = Tenant::query()
            ->where(function (Builder $q) {
                $q->where('status_mitra', 'aktif')
                    ->orWhere('status_mitra', 'Aktif');
            })
            ->with([
                'detailOperasional',
                'produks.kategori',
            ])
            ->withAvg('reviews as avg_rating', 'rating_nilai')
            ->withCount('reviews')
            ->withSum('produks as total_unit_ready', $stockColumn)
            ->withCount('produks');

        // Filter: Search across store name, bio, address, or products
        if ($search !== '') {
            $baseQuery->where(function (Builder $q) use ($search) {
                $q->where('nama_toko', 'like', "%{$search}%")
                    ->orWhere('bio_deskripsi', 'like', "%{$search}%")
                    ->orWhere('alamat_toko', 'like', "%{$search}%")
                    ->orWhereHas('produks', function (Builder $pq) use ($search) {
                        $pq->where('nama_produk', 'like', "%{$search}%");
                    });
            });
        }

        // Filter: Rating pill filter (All, 5.0, 4.0, 3.0, 2.0, 1.0)
        // Uses cross-database compatible subquery in WHERE clause (works on both MySQL & SQLite)
        if ($rating !== '' && $rating !== 'all') {
            $avgSubquery = '(select avg(rating_nilai) from review_ulasan_tenants where tenants.id_tenant = review_ulasan_tenants.id_tenant)';
            $ratingValue = (float) $rating;
            if ($ratingValue >= 5.0) {
                $baseQuery->whereRaw("{$avgSubquery} >= 4.95");
            } elseif ($ratingValue >= 4.0) {
                $baseQuery->whereRaw("{$avgSubquery} >= 3.95 AND {$avgSubquery} < 4.95");
            } elseif ($ratingValue >= 3.0) {
                $baseQuery->whereRaw("{$avgSubquery} >= 2.95 AND {$avgSubquery} < 3.95");
            } elseif ($ratingValue >= 2.0) {
                $baseQuery->whereRaw("{$avgSubquery} >= 1.95 AND {$avgSubquery} < 2.95");
            } elseif ($ratingValue >= 1.0) {
                $baseQuery->whereRaw("{$avgSubquery} >= 0.95 AND {$avgSubquery} < 1.95");
            }
        }

        // Get total matching records
        // When using havingRaw with aggregate subqueries, fetching all IDs or get() gives the accurate total
        $allMatching = (clone $baseQuery)->orderBy('nama_toko', 'asc')->get();
        $totalCount = $allMatching->count();

        // Slice for pagination / load more
        $tenants = $allMatching->slice(0, $requestedPerPage)->values();
        $displayedCount = $tenants->count();
        $remainingCount = max(0, $totalCount - $displayedCount);

        return [
            'tenants' => $tenants,
            'total' => $totalCount,
            'displayed' => $displayedCount,
            'remaining' => $remainingCount,
            'perPage' => $requestedPerPage,
            'rating' => $rating ?: 'all',
            'search' => $search,
        ];
    }

    /**
     * Retrieve a single active tenant by slug or ID with full relations loaded
     */
    public function getTenantBySlug(string $slug): ?Tenant
    {
        $stockColumn = Schema::hasColumn('produks', 'total_stok_unit')
            ? 'total_stok_unit'
            : 'total_stock_unit';

        $tenants = Tenant::query()
            ->where(function (Builder $q) {
                $q->where('status_mitra', 'aktif')
                    ->orWhere('status_mitra', 'Aktif');
            })
            ->with([
                'detailOperasional',
                'produks.kategori',
                'produks.galeri',
                'reviews.penyewa',
            ])
            ->withAvg('reviews as avg_rating', 'rating_nilai')
            ->withCount('reviews')
            ->withSum('produks as total_unit_ready', $stockColumn)
            ->withCount('produks')
            ->get();

        return $tenants->first(function (Tenant $tenant) use ($slug) {
            return $tenant->slug === $slug || (string) $tenant->id_tenant === (string) $slug;
        });
    }
}
