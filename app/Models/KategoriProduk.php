<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KategoriProduk extends Model
{
    protected $table = 'kategori_produk';

    protected $primaryKey = 'id_kategori';

    protected $guarded = ['id_kategori'];

    public function produks(): HasMany
    {
        return $this->hasMany(Produk::class, 'id_kategori', 'id_kategori');
    }
}
