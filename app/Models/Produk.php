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
