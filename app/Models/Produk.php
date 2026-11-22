<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Produk extends Model
{
    protected $table = 'produks';

    protected $primaryKey = 'id_produk';

    protected $guarded = [];

    protected static function booted(): void
    {
        static::saving(function (Produk $produk) {
            $val = $produk->attributes['total_stock_unit'] ?? $produk->attributes['total_stok_unit'] ?? 1;
            if (\Illuminate\Support\Facades\Schema::hasColumn('produks', 'total_stok_unit')) {
                $produk->attributes['total_stok_unit'] = $val;
            }
            if (\Illuminate\Support\Facades\Schema::hasColumn('produks', 'total_stock_unit')) {
                $produk->attributes['total_stock_unit'] = $val;
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'id_tenant', 'id_tenant');
    }

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(KategoriProduk::class, 'id_kategori', 'id_kategori');
    }

    public function galeri(): HasMany
    {
        return $this->hasMany(GaleriFotoProduk::class, 'id_produk', 'id_produk');
    }

    public function durasi(): HasMany
    {
        return $this->hasMany(DurasiProduk::class, 'id_produk', 'id_produk');
    }

    public function getTotalStockUnitAttribute(): int
    {
        return (int) ($this->attributes['total_stock_unit'] ?? $this->attributes['total_stok_unit'] ?? 0);
    }

    public function getTotalStokUnitAttribute(): int
    {
        return (int) ($this->attributes['total_stok_unit'] ?? $this->attributes['total_stock_unit'] ?? 0);
    }

    public function getPrimaryImageAttribute(): string
    {
        if ($this->relationLoaded('galeri') && $this->galeri->isNotEmpty()) {
            return $this->galeri->first()->url_foto_produk;
        }

        $firstPhoto = $this->galeri()->first();
        if ($firstPhoto) {
            return $firstPhoto->url_foto_produk;
        }

        return 'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?auto=format&fit=crop&w=600&q=80';
    }
}
