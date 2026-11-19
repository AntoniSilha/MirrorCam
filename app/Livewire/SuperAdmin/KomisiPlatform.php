<?php

namespace App\Livewire\SuperAdmin;

use App\Models\DetailPemesananTenant;
use App\Models\KomisiPlatform as KomisiPlatformModel;
use App\Models\RekeningSettlementTenant;
use App\Models\Tenant;
use App\Services\SuperAdmin\KomisiRekapService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class KomisiPlatform extends Component
{
    use WithPagination;

    #[Url(as: 'search')]
    public string $search = '';

    public bool $verifyModalOpen = false;

    public ?int $selectedKomisiId = null;

    public string $selectedKode = '';

    public string $selectedTenant = '';

    public string $selectedHakTenant = '';

    public string $selectedRekening = '';

    public string $catatan = '';

    /**
     * @return array<string, mixed>
     */
    public function kpi(): array
    {
        return app(KomisiRekapService::class)->getKpiMetrics();
    }

    /**
     * @return array<string, mixed>
     */
    public function komisiData(): array
    {
        return app(KomisiRekapService::class)->getKomisiList(
            ['search' => $this->search],
            10,
            $this->getPage()
        );
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function openVerify(int $id): void
    {
        $komisi = KomisiPlatformModel::query()->findOrFail($id);
        $pemesanan = DetailPemesananTenant::query()->find($komisi->id_pemesanan);
        $tenant = $pemesanan ? Tenant::query()->find($pemesanan->id_tenant) : null;
        $rekening = $tenant
            ? RekeningSettlementTenant::query()->where('id_tenant', $tenant->id_tenant)->first()
            : null;

        $this->resetValidation();
        $this->selectedKomisiId = $komisi->id_komisi;
        $this->selectedKode = $komisi->kode_komisi_formatted;
        $this->selectedTenant = $tenant ? $tenant->nama_toko : 'Mitra Tenant';
        $this->selectedHakTenant = 'Rp '.number_format((float) $komisi->hak_tenant, 0, ',', '.');
        $this->selectedRekening = $rekening
            ? $rekening->nama_bank.' - '.$rekening->nomor_rekening
            : 'Belum diisi';
        $this->catatan = '';
        $this->verifyModalOpen = true;
    }

    public function closeVerify(): void
    {
        $this->verifyModalOpen = false;
        $this->resetValidation();
    }

    public function verify(): void
    {
        $validated = $this->validate([
            'selectedKomisiId' => ['required', 'integer'],
            'catatan' => ['nullable', 'string', 'max:1000'],
        ]);

        $komisi = KomisiPlatformModel::query()->findOrFail((int) $validated['selectedKomisiId']);
        $service = app(KomisiRekapService::class);
        $service->verifyKomisiPayment((int) $validated['selectedKomisiId'], $validated['catatan'] ?: null);

        $this->verifyModalOpen = false;
        $this->selectedKomisiId = null;
        $this->catatan = '';
        $this->resetValidation();

        session()->flash('status', "Pembayaran komisi transaksi {$komisi->kode_komisi_formatted} berhasil diverifikasi dan dicatat ke log audit.");
    }

    public function render(): View
    {
        $komisiData = $this->komisiData();

        return view('superadmin.komisi.index', [
            'kpi' => $this->kpi(),
            'komisiList' => $komisiData['komisiList'],
            'search' => $komisiData['search'],
        ])->extends('superadmin.layouts.app')
            ->title('Rekapitulasi Komisi Platform — SuperAdmin MirrorCam');
    }
}
