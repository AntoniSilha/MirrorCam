<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PengaduanPelanggan extends Model
{
    protected $table = 'pengaduan_pelanggans';

    protected $primaryKey = 'id_pengaduan';

    protected $guarded = [];

    protected $casts = [
        'waktu_kejadian' => 'datetime',
        'sla_tenggat' => 'datetime',
        'estimasi_klaim' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Pelanggan / User relation
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_akun', 'id_akun');
    }

    /**
     * Tenant Mitra relation
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'id_tenant', 'id_tenant');
    }

    /**
     * Pemesanan / DetailPemesananTenant relation
     */
    public function pemesanan(): BelongsTo
    {
        return $this->belongsTo(DetailPemesananTenant::class, 'id_pemesanan', 'id_pemesanan');
    }

    /**
     * Alias for SRS Multi-Tenant requirement (TenantBooking)
     */
    public function tenantBooking(): BelongsTo
    {
        return $this->pemesanan();
    }

    /**
     * Alias for booking relation
     */
    public function booking(): BelongsTo
    {
        return $this->pemesanan();
    }

    /**
     * Get or generate standard MirrorCam Ticket ID (e.g. AMC-2025-0891)
     */
    public function getNomorTiketAttribute(?string $value): string
    {
        if (! empty($value)) {
            return $value;
        }

        $year = $this->created_at ? $this->created_at->format('Y') : date('Y');
        $idPart = str_pad((string) ($this->id_pengaduan ?? 1), 4, '0', STR_PAD_LEFT);

        return "AMC-{$year}-{$idPart}";
    }

    /**
     * Normalized case status (aktif, selesai, terlambat)
     */
    public function getStatusNormalizedAttribute(): string
    {
        $status = strtolower(trim((string) $this->status_kasus));

        if (in_array($status, ['selesai', 'ditutup', 'disetujui', 'refund selesai', 'completed', 'closed'])) {
            return 'selesai';
        }

        if (in_array($status, ['terlambat', 'eskalasi', 'perlu tindakan segera', 'overdue', 'escalated', 'warning'])) {
            return 'terlambat';
        }

        return 'aktif';
    }

    /**
     * Styling badge based on MirrorCam design guidelines
     */
    public function getStatusBadgeAttribute(): array
    {
        return match ($this->status_normalized) {
            'selesai' => [
                'label' => 'Selesai',
                'bg' => '#fff700',
                'text' => '#000000',
                'border' => '#eab308',
            ],
            'terlambat' => [
                'label' => 'Terlambat',
                'bg' => '#ffdad6',
                'text' => '#ba1a1a',
                'border' => '#fca5a5',
            ],
            default => [
                'label' => 'Aktif',
                'bg' => '#dcfce7',
                'text' => '#16a34a',
                'border' => '#86efac',
            ],
        };
    }

    /**
     * Parse uploaded proof files (supports JSON, comma-separated, single string)
     *
     * @return array<int, array{name: string, url: string, is_image: bool, is_video: bool}>
     */
    public function getBuktiFilesAttribute(): array
    {
        if (empty($this->url_bukti_dokumen)) {
            return [];
        }

        $raw = $this->url_bukti_dokumen;
        $files = [];

        // Check if JSON array
        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            $files = $decoded;
        } elseif (str_contains($raw, ',')) {
            $files = array_map('trim', explode(',', $raw));
        } else {
            $files = [trim($raw)];
        }

        $result = [];
        foreach ($files as $path) {
            if (empty($path)) continue;

            $filename = basename($path);
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

            $url = str_starts_with($path, 'http')
                ? $path
                : (str_starts_with($path, '/storage') ? $path : asset('storage/' . ltrim($path, '/')));

            $result[] = [
                'name' => $filename,
                'path' => $path,
                'url' => $url,
                'is_image' => in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'raw']),
                'is_video' => in_array($ext, ['mp4', 'mov', 'avi', 'mkv']),
                'extension' => $ext ?: 'file',
            ];
        }

        return $result;
    }

    /**
     * Formatted claim estimate (e.g. "Rp 1.500.000")
     */
    public function getFormattedEstimasiKlaimAttribute(): ?string
    {
        if ($this->estimasi_klaim === null || $this->estimasi_klaim <= 0) {
            return null;
        }

        return 'Rp ' . number_format((float) $this->estimasi_klaim, 0, ',', '.');
    }

    /**
     * SLA remaining countdown or human-readable deadline
     */
    public function getSlaRemainingAttribute(): ?string
    {
        if (! $this->sla_tenggat) {
            return null;
        }

        $now = Carbon::now();
        if ($now->greaterThan($this->sla_tenggat)) {
            $diffHours = $now->diffInHours($this->sla_tenggat);
            return "Terlewat {$diffHours} Jam";
        }

        $diffHours = $now->diffInHours($this->sla_tenggat);
        $diffMinutes = $now->copy()->addHours($diffHours)->diffInMinutes($this->sla_tenggat);

        return sprintf('%02d Jam %02d Menit', $diffHours, $diffMinutes);
    }
}
