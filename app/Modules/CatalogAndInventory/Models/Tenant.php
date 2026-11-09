<?php

namespace App\Modules\CatalogAndInventory\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id_tenant
 * @property string $google_id_token
 * @property int $id_akun_pemilik
 * @property string $nama_toko
 * @property string|null $bio_deskripsi
 * @property string|null $url_logo
 * @property string $whatsapp_resmi
 * @property string $nama_penanggungjawab
 * @property string $jabatan_penanggungjawab
 * @property string $email_resmi
 * @property string $alamat_toko
 * @property string|null $koordinat_lokasi
 * @property string $status_mitra
 * @property string|null $kota
 * @property string|null $badge_text
 * @property float|null $rating
 * @property int|null $unit_ready
 */
class Tenant extends Model
{
    protected $table = 'tenants';
    protected $primaryKey = 'id_tenant';

    protected $fillable = [
        'id_akun_pemilik',
        'google_id_token',
        'nama_toko',
        'bio_deskripsi',
        'url_logo',
        'whatsapp_resmi',
        'nama_penanggungjawab',
        'jabatan_penanggungjawab',
        'email_resmi',
        'alamat_toko',
        'koordinat_lokasi',
        'status_mitra',
        'kota',
        'badge_text',
        'rating',
        'unit_ready',
    ];

    /**
     * Products owned by this tenant
     *
     * @return HasMany<Product, $this>
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'id_tenant', 'id_tenant');
    }

    /**
     * Owner account relation
     *
     * @return BelongsTo<User, $this>
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_akun_pemilik', 'id_akun');
    }

    /**
     * Get tenant city/location name
     */
    public function getKotaAttribute(?string $value): string
    {
        if (! empty($value)) {
            return $value;
        }

        // Extract from alamat_toko if available
        if (! empty($this->alamat_toko)) {
            $parts = explode(',', $this->alamat_toko);
            return trim(end($parts));
        }

        return 'Indonesia';
    }

    /**
     * Get official partner badge label
     */
    public function getBadgeTextAttribute(?string $value): string
    {
        return $value ?: 'OFFICIAL PARTNER';
    }

    /**
     * Check if tenant is verified / official
     */
    public function getIsOfficialAttribute(): bool
    {
        return strtolower($this->status_mitra ?? 'aktif') === 'aktif' ||
            strtolower($this->status_mitra ?? '') === 'official';
    }
}
