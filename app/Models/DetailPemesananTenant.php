<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailPemesananTenant extends Model
{
    protected $table = 'detail_pemesanan_tenants';

    protected $primaryKey = 'id_pemesanan';

    protected $guarded = [];

    protected $casts = [
        'waktu_ambil_booking' => 'datetime',
        'waktu_kembali_booking' => 'datetime',
        'harga_sewa_booking' => 'decimal:2',
        'total_harga' => 'decimal:2',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'id_tenant', 'id_tenant');
    }

    public function produk(): BelongsTo
    {
        return $this->belongsTo(Produk::class, 'id_produk', 'id_produk');
    }

    public function verifikasiKtp(): BelongsTo
    {
        return $this->belongsTo(VerifikasiKtp::class, 'id_ktp', 'id_ktp');
    }

    public function getProductNameAttribute(): string
    {
        return $this->produk?->nama_produk ?? 'Kamera & Lensa MirrorCam';
    }

    public function getTenantNameAttribute(): string
    {
        return $this->tenant?->nama_toko ?? 'Mitra Resmi MirrorCam';
    }

    public function getTenantLocationAttribute(): string
    {
        $address = $this->tenant?->alamat_toko ?? 'Indonesia';
        $parts = explode(',', $address);
        return trim(end($parts));
    }

    public function getTenantDisplayAttribute(): string
    {
        return "{$this->tenant_name}, {$this->tenant_location}";
    }

    public function getBookingCodeAttribute(): string
    {
        return $this->sub_order_eksternal ?? "MC-" . str_pad((string) $this->id_pemesanan, 4, '0', STR_PAD_LEFT);
    }

    public function getStartDateAttribute(): string
    {
        return $this->waktu_ambil_booking
            ? $this->waktu_ambil_booking->translatedFormat('d M Y')
            : '24 Okt 2025';
    }

    public function getEndDateAttribute(): string
    {
        return $this->waktu_kembali_booking
            ? $this->waktu_kembali_booking->translatedFormat('d M Y')
            : '27 Okt 2025';
    }

    public function getReturnDeadlineAttribute(): string
    {
        return $this->waktu_kembali_booking
            ? $this->waktu_kembali_booking->translatedFormat('d M Y') . ' (Maks. ' . $this->waktu_kembali_booking->format('H:i') . ')'
            : '27 Okt 2025 (Maks. 18:00)';
    }

    public function getFormattedTotalAttribute(): string
    {
        return 'Rp ' . number_format((float) ($this->total_harga ?? 0), 0, ',', '.');
    }

    public function getGearImageAttribute(): string
    {
        return $this->produk?->primary_image
            ?? 'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?auto=format&fit=crop&w=600&q=80';
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->status_sewa ?? 'Sedang Disewa';
    }

    public function getStatusStyleAttribute(): array
    {
        $status = strtolower($this->status_sewa ?? '');

        if (str_contains($status, 'sedang') || str_contains($status, 'aktif')) {
            return [
                'bg' => '#dcfce7',
                'text' => '#16a34a',
                'border' => '#bbf7d0',
                'label' => 'Sedang Disewa',
            ];
        }

        if (str_contains($status, 'selesai')) {
            return [
                'bg' => '#fff700',
                'text' => '#000000',
                'border' => '#facc15',
                'label' => 'Selesai',
            ];
        }

        if (str_contains($status, 'terlambat')) {
            return [
                'bg' => '#ffdad6',
                'text' => '#ba1a1a',
                'border' => '#fca5a5',
                'label' => 'Terlambat',
            ];
        }

        return [
            'bg' => '#ffedd5',
            'text' => '#c2410c',
            'border' => '#fed7aa',
            'label' => $this->status_sewa ?? 'Sedang Disewa',
        ];
    }
}
