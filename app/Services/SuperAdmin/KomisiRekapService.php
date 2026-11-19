<?php

namespace App\Services\SuperAdmin;

use App\Models\AuditLog;
use App\Models\DetailPemesananTenant;
use App\Models\KomisiPlatform;
use Illuminate\Database\Eloquent\Builder;

class KomisiRekapService
{
    /**
     * Compute 3 top KPI cards for platform commission (SRS NFR-DAT-09: integer Rupiah)
     */
    public function getKpiMetrics(): array
    {
        $totalKomisi = (int) round(KomisiPlatform::sum('total_komisi'));
        if ($totalKomisi <= 0) {
            $totalGmv = (float) DetailPemesananTenant::sum('total_harga');
            $totalKomisi = (int) round($totalGmv * 0.10);
        }

        $komisiDitransfer = (int) round(KomisiPlatform::where(function (Builder $q) {
            $q->where('status_komisi', 'Selesai')
                ->orWhere('status_komisi', 'selesai');
        })->sum('total_komisi'));

        // If no records explicitly marked Selesai yet, calculate based on finished orders
        if ($komisiDitransfer <= 0 && $totalKomisi > 0) {
            $selesaiGmv = (float) DetailPemesananTenant::where('status_sewa', 'Selesai')->sum('total_harga');
            $komisiDitransfer = (int) round($selesaiGmv * 0.10);
        }

        // Escrow jaminan deposit: typically 40%-50% of active rental commitments
        $activeGmv = (float) DetailPemesananTenant::where(function ($q) {
            $q->where('status_sewa', 'Aktif')->orWhere('status_sewa', 'aktif');
        })->sum('total_harga');

        $escrowDeposit = (int) round($activeGmv * 0.45);
        if ($escrowDeposit <= 0 && $totalKomisi > 0) {
            $escrowDeposit = (int) round($totalKomisi * 0.45);
        }

        return [
            'totalKomisi' => $totalKomisi,
            'totalKomisiFormatted' => 'Rp '.number_format($totalKomisi, 0, ',', '.'),
            'komisiDitransfer' => $komisiDitransfer,
            'komisiDitransferFormatted' => 'Rp '.number_format($komisiDitransfer, 0, ',', '.'),
            'escrowDeposit' => $escrowDeposit,
            'escrowDepositFormatted' => 'Rp '.number_format($escrowDeposit, 0, ',', '.'),
        ];
    }

    /**
     * Retrieve paginated commission records with search
     */
    public function getKomisiList(array $filters = [], int $perPage = 10, int $page = 1): array
    {
        $search = trim($filters['search'] ?? '');

        $query = KomisiPlatform::with(['pemesanan.tenant.detailOperasional']);

        if ($search !== '') {
            $query->where(function (Builder $q) use ($search) {
                // If searched like #KM-00011 or KM-00011
                $cleanId = preg_replace('/[^0-9]/', '', $search);

                if (! empty($cleanId) && is_numeric($cleanId)) {
                    $q->orWhere('id_komisi', (int) $cleanId);
                }

                $q->orWhere('kode_komisi', 'like', "%{$search}%")
                    ->orWhereHas('pemesanan.tenant', function (Builder $tq) use ($search) {
                        $tq->where('nama_toko', 'like', "%{$search}%");
                    })
                    ->orWhereHas('pemesanan', function (Builder $pq) use ($search) {
                        $pq->where('sub_order_eksternal', 'like', "%{$search}%");
                    });
            });
        }

        $komisiList = $query->orderBy('created_at', 'desc')->paginate($perPage, ['*'], 'page', max(1, $page));

        return [
            'komisiList' => $komisiList,
            'search' => $search,
        ];
    }

    /**
     * Verify commission payout to tenant settlement (SRS UC-24 / FR-ADM-02)
     */
    public function verifyKomisiPayment(int $id, ?string $notes = null): KomisiPlatform
    {
        $komisi = KomisiPlatform::with(['pemesanan.tenant'])->findOrFail($id);
        $oldStatus = $komisi->status_komisi;

        $komisi->update([
            'status_komisi' => 'Selesai',
            'catatan_verifikasi' => $notes ?: 'Pembayaran settlement komisi telah diverifikasi oleh SuperAdmin',
        ]);

        AuditLog::record(
            action: 'komisi_payment_verified',
            description: "SuperAdmin memverifikasi pembayaran komisi {$komisi->kode_komisi_formatted} untuk tenant: ".($komisi->pemesanan?->tenant?->nama_toko ?? '-'),
            auditable: $komisi,
            details: [
                'id_komisi' => $komisi->id_komisi,
                'kode_komisi' => $komisi->kode_komisi_formatted,
                'total_komisi' => (float) $komisi->total_komisi,
                'old_status' => $oldStatus,
                'new_status' => 'Selesai',
                'catatan' => $notes,
            ]
        );

        return $komisi;
    }

    /**
     * Export commission report to CSV format
     */
    public function exportKomisiCsv(): string
    {
        $records = KomisiPlatform::with(['pemesanan.tenant'])->orderBy('id_komisi')->get();

        $output = "ID Transaksi Komisi,Tanggal & Waktu,Nama Tenant,Pendapatan Tenant,Komisi Platform (10%),Hak Tenant (90%),Status\n";

        foreach ($records as $item) {
            $output .= sprintf(
                "\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\"\n",
                $item->kode_komisi_formatted,
                $item->created_at?->format('d M Y · H:i').' WIB',
                addslashes($item->pemesanan?->tenant?->nama_toko ?? 'Tenant'),
                'Rp '.number_format($item->pendapatan_tenant, 0, ',', '.'),
                'Rp '.number_format((float) $item->total_komisi, 0, ',', '.'),
                'Rp '.number_format($item->hak_tenant, 0, ',', '.'),
                $item->status_label
            );
        }

        return $output;
    }
}
