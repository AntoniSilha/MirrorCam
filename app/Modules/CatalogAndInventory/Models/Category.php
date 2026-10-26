<?php

namespace App\Modules\CatalogAndInventory\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * @property int $id_kategori
 * @property string $nama_kategori
 * @property string|null $slug
 * @property string|null $icon
 * @property string|null $deskripsi
 * @property int $total_produk
 */
class Category extends Model
{
    protected $table = 'kategori_produk';
    protected $primaryKey = 'id_kategori';

    protected $fillable = [
        'nama_kategori',
        'slug',
        'icon',
        'deskripsi',
    ];

    /**
     * Get products in this category
     *
     * @return HasMany<Product, $this>
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'id_kategori', 'id_kategori');
    }

    /**
     * Slug attribute accessor
     */
    public function getSlugAttribute(?string $value): string
    {
        return $value ?: Str::slug($this->nama_kategori ?? 'kategori');
    }

    /**
     * Product count accessor
     */
    public function getTotalProdukAttribute(): int
    {
        return $this->relationLoaded('products') ? $this->products->count() : 0;
    }
}
