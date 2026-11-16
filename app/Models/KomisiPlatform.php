<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KomisiPlatform extends Model
{
    protected $table = 'komisi_platforms';

    protected $primaryKey = 'id_komisi';

    protected $guarded = [];

    protected $attributes = [
        'persentase_komisi_platform' => 10.00,
        'status_komisi' => 'Menunggu',
    ];

    protected $casts = [
        'persentase_komisi_platform' => 'decimal:2',
        'total_komisi' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Associated Booking / Order
     */
    public function pemesanan(): BelongsTo
    {
        return $this->belongsTo(DetailPemesananTenant::class, 'id_pemesanan', 'id_pemesanan');
    }

    /**
     * Formatted Commission ID code (e.g. #KM-00011)
     */
    public function getKodeKomisiFormattedAttribute(): string
    {
        if (! empty($this->kode_komisi)) {
            return str_starts_with($this->kode_komisi, '#') ? $this->kode_komisi : '#'.$this->kode_komisi;
        }

        return '#KM-'.str_pad((string) $this->id_komisi, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Tenant Gross Revenue (Subtotal / Pendapatan Tenant)
     */
    public function getPendapatanTenantAttribute(): float
    {
        if ($this->pemesanan && $this->pemesanan->total_harga > 0) {
            return (float) $this->pemesanan->total_harga;
        }

        $persen = (float) ($this->persentase_komisi_platform ?: 10.0);
        if ($persen > 0) {
            return (float) round(($this->total_komisi / $persen) * 100);
        }

        return (float) $this->total_komisi * 10;
    }

    /**
     * Net Tenant Share (Hak Tenant 90%)
     */
    public function getHakTenantAttribute(): float
    {
        return max(0, $this->pendapatan_tenant - (float) $this->total_komisi);
    }

    /**
     * Status label (Aktif, Selesai, Terlambat)
     */
    public function getStatusLabelAttribute(): string
    {
        if (! empty($this->status_komisi)) {
            return ucfirst(strtolower($this->status_komisi));
        }

        return ucfirst(strtolower($this->pemesanan?->status_sewa ?: 'Aktif'));
    }
}
