<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Produk extends Model
{
    protected $table = 'produks';

    protected $primaryKey = 'id_produk';

    protected $guarded = ['id_produk'];

    protected $appends = ['primary_image'];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class, 'id_tenant', 'id_tenant');
    }

    public function kategori()
    {
        return $this->belongsTo(KategoriProduk::class, 'id_kategori', 'id_kategori');
    }

    public function galeri()
    {
        return $this->hasMany(GaleriFotoProduk::class, 'id_produk', 'id_produk');
    }

    public function durasi(): HasMany
    {
        return $this->hasMany(DurasiProduk::class, 'id_produk', 'id_produk');
    }

    public function getPrimaryImageAttribute(): string
    {
        $foto = $this->galeri->first()?->url_foto_produk;

        if (! $foto) {
            return 'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?auto=format&fit=crop&w=800&q=80';
        }

        if (str_starts_with($foto, 'http://') || str_starts_with($foto, 'https://')) {
            return $foto;
        }

        return asset('storage/'.ltrim($foto, '/'));
    }

    public function getTotalStokUnitAttribute(): int
    {
        return (int) ($this->attributes['total_stok_unit'] ?? $this->attributes['total_stock_unit'] ?? 0);
    }

    public function getTotalStockUnitAttribute(): int
    {
        return (int) ($this->attributes['total_stock_unit'] ?? $this->attributes['total_stok_unit'] ?? 0);
    }
}
