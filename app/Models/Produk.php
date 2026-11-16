<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

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

    public function getHargaMulaiAttribute(): float
    {
        if ($this->relationLoaded('durasi')) {
            return (float) ($this->durasi->min('harga') ?? 0);
        }

        return (float) $this->durasi()->min('harga');
    }

    public function pemesanans(): HasMany
    {
        return $this->hasMany(DetailPemesananTenant::class, 'id_produk', 'id_produk');
    }

    public function reviews(): HasManyThrough
    {
        return $this->hasManyThrough(
            ReviewUlasanTenant::class,
            DetailPemesananTenant::class,
            'id_produk',
            'id_pemesanan',
            'id_produk',
            'id_pemesanan'
        );
    }

    public function getFormattedRatingAttribute(): string
    {
        $average = $this->attributes['avg_rating']
            ?? $this->attributes['reviews_avg_rating_nilai']
            ?? null;

        if ($average === null && $this->relationLoaded('reviews')) {
            $average = $this->reviews->avg('rating_nilai');
        }

        return number_format((float) ($average ?? 0), 1, '.', '');
    }

    public function getReviewCountTextAttribute(): string
    {
        $count = (int) ($this->reviews_count
            ?? ($this->relationLoaded('reviews') ? $this->reviews->count() : 0));

        return $count > 0 ? "{$count} ulasan" : 'Belum ada ulasan';
    }

    public function getBookingCountAttribute(): int
    {
        return (int) ($this->pemesanans_count ?? 0);
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
