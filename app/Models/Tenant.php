<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Tenant extends Model
{
    protected $table = 'tenants';

    protected $primaryKey = 'id_tenant';

    protected $guarded = ['id_tenant'];

    protected function casts(): array
    {
        return [
            'mode_libur' => 'boolean',
        ];
    }

    /**
     * Relationship: Products owned by this tenant
     */
    public function produks(): HasMany
    {
        return $this->hasMany(Produk::class, 'id_tenant', 'id_tenant');
    }

    /**
     * Relationship: Operational details of this tenant
     */
    public function detailOperasional(): HasOne
    {
        return $this->hasOne(DetailOperasionalTenant::class, 'id_tenant', 'id_tenant');
    }

    /**
     * Relationship: Reviews / ratings of this tenant
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(ReviewUlasanTenant::class, 'id_tenant', 'id_tenant');
    }

    /**
     * Relationship: Settlement bank account of this tenant
     */
    public function rekeningSettlement(): HasOne
    {
        return $this->hasOne(RekeningSettlementTenant::class, 'id_tenant', 'id_tenant');
    }

    /**
     * Relationship: Bookings made at this tenant
     */
    public function pemesanans(): HasMany
    {
        return $this->hasMany(DetailPemesananTenant::class, 'id_tenant', 'id_tenant');
    }

    /**
     * Accessor: Formatted Tenant ID (e.g. #TNT-001)
     */
    public function getIdMitraFormattedAttribute(): string
    {
        return '#TNT-'.str_pad((string) $this->id_tenant, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Relationship: Owner user account
     */
    public function pemilik(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_akun_pemilik', 'id_akun');
    }

    /**
     * Accessor: SEO & URL friendly slug
     */
    public function getSlugAttribute(): string
    {
        $slug = Str::slug($this->nama_toko ?? '');

        return ! empty($slug) ? $slug : 'tenant-'.$this->id_tenant;
    }

    /**
     * Accessor: 2-letter uppercase initials for avatar box
     */
    public function getInitialsAttribute(): string
    {
        $name = trim($this->nama_toko ?? 'Tenant');
        $words = preg_split('/\s+/', $name);

        if (count($words) >= 2) {
            return strtoupper(mb_substr($words[0], 0, 1).mb_substr($words[1], 0, 1));
        }

        return strtoupper(mb_substr($name, 0, 2));
    }

    /**
     * Accessor: Dynamic service category subtitle
     * (Rental Kamera or Rental Kamera & Studio)
     */
    public function getServiceCategoryAttribute(): string
    {
        $hasStudio = false;

        if ($this->relationLoaded('produks')) {
            $hasStudio = $this->produks->contains(function ($item) {
                $catName = strtolower($item->kategori?->nama_kategori ?? '');
                $prodName = strtolower($item->nama_produk ?? '');

                return str_contains($catName, 'studio') || str_contains($prodName, 'studio');
            });
        }

        return $hasStudio ? 'Rental Kamera & Studio' : 'Rental Kamera';
    }

    /**
     * Accessor: Tenant city / location
     */
    public function getKotaAttribute(): string
    {
        if (! empty($this->detailOperasional?->kota)) {
            return $this->detailOperasional->kota;
        }

        if (! empty($this->alamat_toko)) {
            $parts = explode(',', $this->alamat_toko);

            return trim(end($parts));
        }

        return 'Indonesia';
    }

    /**
     * Accessor: Check if tenant is verified / official partner
     */
    public function getIsOfficialAttribute(): bool
    {
        $status = strtolower($this->status_mitra ?? '');

        return in_array($status, ['verified', 'aktif', 'official'], true);
    }

    /**
     * Accessor: Formatted average rating (e.g. 4.9, 5.0, or 0.0)
     */
    public function getFormattedRatingAttribute(): string
    {
        $average = $this->attributes['avg_rating']
            ?? $this->attributes['reviews_avg_rating_nilai']
            ?? null;

        if ($average !== null) {
            return number_format((float) $average, 1, '.', '');
        }

        if ($this->relationLoaded('reviews') && $this->reviews->isNotEmpty()) {
            return number_format((float) $this->reviews->avg('rating_nilai'), 1, '.', '');
        }

        return '0.0';
    }

    /**
     * Accessor: Formatted review count label (e.g. 1.2k+ Review, 850+ Review, or Belum ada ulasan)
     */
    public function getReviewCountTextAttribute(): string
    {
        $count = (int) ($this->reviews_count ?? ($this->relationLoaded('reviews') ? $this->reviews->count() : 0));

        if ($count <= 0) {
            return 'Belum ada ulasan';
        }

        if ($count >= 1000) {
            $formatted = rtrim(rtrim(number_format($count / 1000, 1, '.', ''), '0'), '.');

            return "{$formatted}k+ Review";
        }

        return "{$count}+ Review";
    }

    /**
     * Accessor: Ready units total (Unit Siap Pakai)
     */
    public function getUnitSiapPakaiAttribute(): int
    {
        if (isset($this->attributes['total_unit_ready']) && $this->attributes['total_unit_ready'] !== null) {
            return (int) $this->attributes['total_unit_ready'];
        }

        if ($this->relationLoaded('produks')) {
            return (int) $this->produks->sum(function ($p) {
                return $p->total_stock_unit ?? $p->total_stok_unit ?? 0;
            });
        }

        return 0;
    }

    /**
     * Custom route model binding resolution to support {tenant:slug}
     */
    public function resolveRouteBinding($value, $field = null)
    {
        if ($field === 'slug' || $field === null) {
            if (is_numeric($value)) {
                $tenant = $this->newQuery()->where('id_tenant', $value)->first();

                if ($tenant) {
                    return $tenant;
                }
            }

            $tenants = $this->newQuery()->get();

            $matched = $tenants->first(function ($t) use ($value) {
                return $t->slug === $value || (string) $t->id_tenant === (string) $value;
            });

            if ($matched) {
                return $matched;
            }

            abort(404, 'Tenant tidak ditemukan.');
        }

        return parent::resolveRouteBinding($value, $field);
    }
}
