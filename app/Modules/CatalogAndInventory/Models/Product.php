<?php

namespace App\Modules\CatalogAndInventory\Models;

use App\Models\GaleriFotoProduk;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id_produk
 * @property int $id_tenant
 * @property int $id_kategori
 * @property string $nama_produk
 * @property string|null $color_produk
 * @property string $deskripsi_lengkap
 * @property float $harga_sewa
 * @property int $total_stok_unit
 * @property string|null $thumbnail_url
 * @property float|null $rating
 * @property int|null $review_count
 * @property-read Tenant|null $tenant
 * @property-read Category|null $category
 * @property-read string $formatted_price
 * @property-read string $primary_image
 */
class Product extends Model
{
    protected $table = 'produks';
    protected $primaryKey = 'id_produk';

    protected $fillable = [
        'id_tenant',
        'id_kategori',
        'nama_produk',
        'color_produk',
        'deskripsi_lengkap',
        'harga_sewa',
        'total_stok_unit',
        'thumbnail_url',
        'rating',
        'review_count',
    ];

    protected $casts = [
        'harga_sewa' => 'decimal:2',
        'total_stok_unit' => 'integer',
        'rating' => 'float',
        'review_count' => 'integer',
    ];

    /**
     * Tenant relation
     *
     * @return BelongsTo<Tenant, $this>
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'id_tenant', 'id_tenant');
    }

    /**
     * Category relation
     *
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'id_kategori', 'id_kategori');
    }

    /**
     * Photo gallery relation
     *
     * @return HasMany<GaleriFotoProduk, $this>
     */
    public function gallery(): HasMany
    {
        return $this->hasMany(GaleriFotoProduk::class, 'id_produk', 'id_produk');
    }

    /**
     * Scope for products with stock
     *
     * @param  Builder<Product>  $query
     * @return Builder<Product>
     */
    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('total_stok_unit', '>', 0);
    }

    /**
     * Formatted rental price (e.g. "Rp 550.000 / hari")
     */
    public function getFormattedPriceAttribute(): string
    {
        return 'Rp ' . number_format((float) $this->harga_sewa, 0, ',', '.') . ' / hari';
    }

    /**
     * Primary image URL
     */
    public function getPrimaryImageAttribute(): string
    {
        if (! empty($this->thumbnail_url)) {
            return $this->thumbnail_url;
        }

        if ($this->relationLoaded('gallery') && $this->gallery->isNotEmpty()) {
            return $this->gallery->first()->url_foto_produk;
        }

        return '/images/gear/placeholder.jpg';
    }

    /**
     * Formatted rating string (e.g. "4.9")
     */
    public function getRatingDisplayAttribute(): string
    {
        return number_format((float) ($this->rating ?? 4.9), 1, '.', '');
    }

    /**
     * Review count display
     */
    public function getReviewCountDisplayAttribute(): int
    {
        return $this->review_count ?? 24;
    }
}
