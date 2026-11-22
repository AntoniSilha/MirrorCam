<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DetailPemesananTenant extends Model
{
    protected $table = 'detail_pemesanan_tenants';

    protected $primaryKey = 'id_pemesanan';

    protected $guarded = [];

    protected $casts = [
        'waktu_ambil_booking' => 'datetime',
        'waktu_kembali_booking' => 'datetime',
        'actual_handover_at' => 'datetime',
        'return_deadline' => 'datetime',
        'actual_returned_at' => 'datetime',
        'payment_verified_at' => 'datetime',
        'harga_sewa_booking' => 'decimal:2',
        'total_harga' => 'decimal:2',
        'nominal_dp' => 'decimal:2',
        'sisa_tagihan' => 'decimal:2',
        'estimasi_biaya_kerusakan' => 'decimal:2',
        'denda_keterlambatan' => 'decimal:2',
        'is_payment_verified' => 'boolean',
        'checklist_data' => 'array',
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

    public function pengaduan(): HasMany
    {
        return $this->hasMany(PengaduanPelanggan::class, 'id_pemesanan', 'id_pemesanan');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class, 'auditable_id', 'id_pemesanan')
            ->where('auditable_type', self::class);
    }

    public function isPaymentComplete(): bool
    {
        if ($this->is_payment_verified) {
            return true;
        }

        $status = strtolower((string) ($this->status_harga ?? ''));
        if (in_array($status, ['lunas', 'settled', 'paid', 'confirmed'], true)) {
            return true;
        }

        return (float) ($this->sisa_tagihan ?? 0) <= 0 && (float) ($this->total_harga ?? 0) > 0;
    }

    public function isInUse(): bool
    {
        $status = strtolower((string) ($this->status_sewa ?? ''));

        return in_array($status, ['sedang disewa', 'in_use', 'disewa', 'aktif'], true);
    }

    public function isCompleted(): bool
    {
        $status = strtolower((string) ($this->status_sewa ?? ''));

        return in_array($status, ['selesai', 'completed'], true);
    }

    public function isDisputed(): bool
    {
        $status = strtolower((string) ($this->status_sewa ?? ''));

        return in_array($status, ['disputed', 'sengketa', 'rusak'], true);
    }

    public function calculateAutomaticLateFine(?Carbon $asOf = null): float
    {
        $now = $asOf ?? Carbon::now();
        $deadline = $this->return_deadline ?? $this->waktu_kembali_booking;

        if (! $deadline || $now->lessThanOrEqualTo($deadline)) {
            return 0.0;
        }

        $lateMinutes = $deadline->diffInMinutes($now);
        $lateDays = max(1, (int) ceil($lateMinutes / 1440));

        $dailyRate = (float) $this->harga_sewa_booking > 0
            ? (float) $this->harga_sewa_booking
            : ((float) $this->total_harga / max(1, (int) ($this->durasi_sewa_hari ?: 1)));

        return round($lateDays * $dailyRate, 2);
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
        return $this->sub_order_eksternal ?? 'MC-'.str_pad((string) $this->id_pemesanan, 4, '0', STR_PAD_LEFT);
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

    public function getReturnDeadlineFormattedAttribute(): string
    {
        $deadline = $this->return_deadline ?? $this->waktu_kembali_booking;

        return $deadline
            ? Carbon::parse($deadline)->translatedFormat('d M Y (Maks. H:i)')
            : '-';
    }

    public function getReturnDeadlineAttribute(): mixed
    {
        if (isset($this->attributes['return_deadline']) && $this->attributes['return_deadline']) {
            return Carbon::parse($this->attributes['return_deadline']);
        }

        return $this->waktu_kembali_booking;
    }

    public function getFormattedTotalAttribute(): string
    {
        return 'Rp '.number_format((float) ($this->total_harga ?? 0), 0, ',', '.');
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

        if (str_contains($status, 'sedang') || str_contains($status, 'aktif') || str_contains($status, 'in_use')) {
            return [
                'bg' => '#dcfce7',
                'text' => '#16a34a',
                'border' => '#bbf7d0',
                'label' => 'Sedang Disewa',
            ];
        }

        if (str_contains($status, 'selesai') || str_contains($status, 'completed')) {
            return [
                'bg' => '#fff700',
                'text' => '#000000',
                'border' => '#facc15',
                'label' => 'Selesai',
            ];
        }

        if (str_contains($status, 'terlambat') || str_contains($status, 'disputed') || str_contains($status, 'sengketa')) {
            return [
                'bg' => '#ffdad6',
                'text' => '#ba1a1a',
                'border' => '#fca5a5',
                'label' => $status === 'disputed' || $status === 'sengketa' ? 'Sengketa' : 'Terlambat',
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
