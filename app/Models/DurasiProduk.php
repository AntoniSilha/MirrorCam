<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DurasiProduk extends Model
{
    protected $table = 'durasi_produks';

    protected $primaryKey = 'id_durasi';

    protected $guarded = [];

    protected $casts = [
        'harga' => 'decimal:2',
    ];

    public function produk(): BelongsTo
    {
        return $this->belongsTo(Produk::class, 'id_produk', 'id_produk');
    }
}
