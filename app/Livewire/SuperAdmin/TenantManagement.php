<?php

namespace App\Livewire\SuperAdmin;

use App\Services\SuperAdmin\TenantApprovalService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class TenantManagement extends Component
{
    use WithPagination;

    #[Url(as: 'tab')]
    public string $tab = 'all';

    #[Url(as: 'search')]
    public string $search = '';

    #[Url(as: 'city')]
    public string $city = '';

    public bool $createModalOpen = false;

    public bool $suspendModalOpen = false;

    public ?int $suspendTenantId = null;

    public string $suspendTenantName = '';

    public string $suspendReason = '';

    public string $nama_toko = '';

    public string $nama_penanggungjawab = '';

    public string $whatsapp_resmi = '';

    public string $email_resmi = '';

    public string $kota = '';

    public string $alamat_toko = '';

    public string $status_mitra = 'verified';

    public string $nama_bank = '';

    public string $nomor_rekening = '';

    public string $nama_pemilik_rekening = '';

    /**
     * @return array<string, mixed>
     */
    public function kpi(): array
    {
        return app(TenantApprovalService::class)->getKpiMetrics();
    }

    /**
     * @return array<string, mixed>
     */
    public function tenantData(): array
    {
        return app(TenantApprovalService::class)->getTenants([
            'tab' => $this->tab,
            'search' => $this->search,
            'city' => $this->city,
        ], 10, $this->getPage());
    }

    public function setTab(string $tab): void
    {
        if (! in_array($tab, ['all', 'menunggu_validasi', 'mitra_aktif', 'ditangguhkan'], true)) {
            return;
        }

        $this->tab = $tab;
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedCity(): void
    {
        $this->resetPage();
    }

    public function approve(int $id): void
    {
        $tenant = app(TenantApprovalService::class)->approveTenant($id);
        $this->refreshData();

        session()->flash('status', "Mitra #TNT-{$tenant->id_tenant} ({$tenant->nama_toko}) berhasil disetujui dan berstatus aktif.");
    }

    public function openSuspend(int $id, string $name): void
    {
        $this->resetValidation();
        $this->suspendTenantId = $id;
        $this->suspendTenantName = $name;
        $this->suspendReason = '';
        $this->suspendModalOpen = true;
    }

    public function closeSuspend(): void
    {
        $this->suspendModalOpen = false;
        $this->resetValidation();
    }

    public function suspend(): void
    {
        $validated = $this->validate([
            'suspendTenantId' => ['required', 'integer'],
            'suspendReason' => ['nullable', 'string', 'max:1000'],
        ]);

        $tenant = app(TenantApprovalService::class)->suspendTenant(
            (int) $validated['suspendTenantId'],
            $validated['suspendReason'] ?: null
        );

        $this->suspendModalOpen = false;
        $this->suspendTenantId = null;
        $this->suspendTenantName = '';
        $this->suspendReason = '';
        $this->resetValidation();
        $this->refreshData();

        session()->flash('status', "Mitra #TNT-{$tenant->id_tenant} ({$tenant->nama_toko}) telah ditangguhkan.");
    }

    public function openCreate(): void
    {
        $this->resetValidation();
        $this->resetCreateForm();
        $this->createModalOpen = true;
    }

    public function closeCreate(): void
    {
        $this->createModalOpen = false;
        $this->resetValidation();
    }

    public function createTenant(): void
    {
        $validated = $this->validate([
            'nama_toko' => ['required', 'string', 'max:150'],
            'nama_penanggungjawab' => ['required', 'string', 'max:100'],
            'whatsapp_resmi' => ['required', 'string', 'max:25'],
            'email_resmi' => ['required', 'email', 'max:150'],
            'alamat_toko' => ['required', 'string'],
            'kota' => ['required', 'string', 'max:100'],
            'nama_bank' => ['nullable', 'string', 'max:50'],
            'nomor_rekening' => ['nullable', 'string', 'max:50'],
            'nama_pemilik_rekening' => ['nullable', 'string', 'max:100'],
            'status_mitra' => ['nullable', 'string', 'in:verified,noverified'],
        ]);

        $tenant = app(TenantApprovalService::class)->createManualTenant($validated);

        $this->createModalOpen = false;
        $this->resetCreateForm();
        $this->resetValidation();
        $this->refreshData();

        session()->flash('status', "Mitra baru {$tenant->nama_toko} (#TNT-{$tenant->id_tenant}) berhasil ditambahkan dan dicatat ke log audit.");
    }

    protected function refreshData(): void {}

    protected function resetCreateForm(): void
    {
        $this->nama_toko = '';
        $this->nama_penanggungjawab = '';
        $this->whatsapp_resmi = '';
        $this->email_resmi = '';
        $this->kota = '';
        $this->alamat_toko = '';
        $this->status_mitra = 'verified';
        $this->nama_bank = '';
        $this->nomor_rekening = '';
        $this->nama_pemilik_rekening = '';
    }

    public function render(): View
    {
        $tenantData = $this->tenantData();

        return view('superadmin.tenant.index', [
            'kpi' => $this->kpi(),
            'tenants' => $tenantData['tenants'],
            'activeTab' => $tenantData['activeTab'],
            'search' => $tenantData['search'],
            'selectedCity' => $tenantData['selectedCity'],
            'counts' => $tenantData['counts'],
            'cities' => $tenantData['cities'],
        ])->extends('superadmin.layouts.app')
            ->title('Manajemen Tenant Mitra — SuperAdmin MirrorCam');
    }
}
