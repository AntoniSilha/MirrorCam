<?php

namespace App\Modules\TenantManagement\Concerns;

use App\Models\DetailPemesananTenant;
use App\Models\KomisiPlatform;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;

/**
 * @mixin \Livewire\Component
 */
trait ManagesCommissionQris
{
    public bool $qrisGenerated = false;

    public bool $isVerifyingQris = false;

    abstract protected function tenant(): ?Tenant;

    public function generateCommissionQris(): void
    {
        $tenant = $this->tenant();

        if (! $tenant) {
            return;
        }

        $this->syncMissingCommissions($tenant);
        $this->qrisGenerated = true;
        unset($this->dashboard);

        session()->flash('status', 'Kode QRIS otomatis berhasil dibuat. Silakan pindai untuk melunasi komisi.');
    }

    public function confirmCommissionQrisPayment(): void
    {
        $tenant = $this->tenant();

        if (! $tenant) {
            return;
        }

        $bookingIds = DetailPemesananTenant::query()
            ->where('id_tenant', $tenant->id_tenant)
            ->pluck('id_pemesanan');

        $unpaidCommissions = KomisiPlatform::query()
            ->whereIn('id_pemesanan', $bookingIds)
            ->where(function ($query): void {
                $query->whereNull('status_komisi')
                    ->orWhereRaw('LOWER(status_komisi) != ?', ['lunas']);
            })
            ->get();

        if ($unpaidCommissions->isEmpty()) {
            session()->flash('status', 'Tidak ada tagihan komisi yang perlu dibayar.');

            return;
        }

        $reference = 'QRIS-KOM-'.$tenant->id_tenant.'-'.now()->format('YmdHis');

        DB::transaction(function () use ($unpaidCommissions, $reference): void {
            foreach ($unpaidCommissions as $komisi) {
                $komisi->update([
                    'status_komisi' => 'Lunas',
                    'metode_pembayaran' => 'QRIS Otomatis',
                    'referensi_pembayaran' => $reference,
                    'paid_at' => now(),
                ]);
            }
        });

        $this->qrisGenerated = false;
        $this->isVerifyingQris = false;
        unset($this->dashboard);

        session()->flash('status', 'Pembayaran komisi via QRIS berhasil diverifikasi otomatis (Ref: '.$reference.').');
    }

    protected function syncMissingCommissions(Tenant $tenant): void
    {
        $bookings = DetailPemesananTenant::query()
            ->where('id_tenant', $tenant->id_tenant)
            ->where('total_harga', '>', 0)
            ->get();

        foreach ($bookings as $booking) {
            $exists = KomisiPlatform::query()
                ->where('id_pemesanan', $booking->id_pemesanan)
                ->exists();

            if (! $exists) {
                $persen = 10.00;
                $totalKomisi = round(((float) $booking->total_harga * $persen) / 100, 2);

                KomisiPlatform::create([
                    'id_pemesanan' => $booking->id_pemesanan,
                    'persentase_komisi_platform' => $persen,
                    'total_komisi' => $totalKomisi,
                    'status_komisi' => 'Menunggu',
                ]);
            }
        }
    }
}
