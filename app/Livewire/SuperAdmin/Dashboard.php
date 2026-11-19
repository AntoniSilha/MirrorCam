<?php

namespace App\Livewire\SuperAdmin;

use App\Services\SuperAdmin\SuperAdminDashboardService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Dashboard extends Component
{
    use WithPagination;

    #[Url(as: 'status')]
    public string $status = 'semua';

    public bool $resolveModalOpen = false;

    public ?int $selectedComplaintId = null;

    public string $selectedComplaintTicket = '';

    public string $justifikasi = '';

    public string $keputusan = 'Selesai & Kasus Ditutup';

    /**
     * @return array<string, mixed>
     */
    public function kpi(): array
    {
        return app(SuperAdminDashboardService::class)->getKpiMetrics();
    }

    /**
     * @return array<string, mixed>
     */
    public function tenantGrowthChart(): array
    {
        return app(SuperAdminDashboardService::class)->getTenantGrowthChartData();
    }

    /**
     * @return array<string, mixed>
     */
    public function commissionChart(): array
    {
        return app(SuperAdminDashboardService::class)->getCommissionChartData();
    }

    /**
     * @return array<string, mixed>
     */
    public function complaintsData(): array
    {
        return app(SuperAdminDashboardService::class)->getComplaints(
            ['status' => $this->status],
            5,
            $this->getPage()
        );
    }

    public function setTab(string $tab): void
    {
        if (! in_array($tab, ['semua', 'perlu_ditangani', 'selesai'], true)) {
            return;
        }

        $this->status = $tab;
        $this->resetPage();
    }

    public function openResolve(int $id, string $ticket): void
    {
        $this->resetValidation();
        $this->selectedComplaintId = $id;
        $this->selectedComplaintTicket = $ticket;
        $this->justifikasi = '';
        $this->keputusan = 'Selesai & Kasus Ditutup';
        $this->resolveModalOpen = true;
    }

    public function closeResolve(): void
    {
        $this->resolveModalOpen = false;
        $this->resetValidation();
    }

    public function resolveComplaint(): void
    {
        $validated = $this->validate([
            'selectedComplaintId' => ['required', 'integer'],
            'justifikasi' => ['required', 'string', 'min:5'],
            'keputusan' => ['nullable', 'string', 'max:100'],
        ]);

        app(SuperAdminDashboardService::class)->resolveComplaint(
            id: (int) $validated['selectedComplaintId'],
            justifikasi: $validated['justifikasi'],
            keputusan: $validated['keputusan'] ?? 'Selesai & Kasus Ditutup'
        );

        $this->resolveModalOpen = false;
        $this->selectedComplaintId = null;
        $this->selectedComplaintTicket = '';
        $this->justifikasi = '';
        $this->resetValidation();

        session()->flash('status', 'Sengketa aduan berhasil diselesaikan dan dicatat ke log audit platform.');
    }

    public function render(): View
    {
        $complaintsData = $this->complaintsData();

        return view('superadmin.dashboard.index', [
            'kpi' => $this->kpi(),
            'tenantGrowthChart' => $this->tenantGrowthChart(),
            'commissionChart' => $this->commissionChart(),
            'complaints' => $complaintsData['complaints'],
            'activeComplaintTab' => $complaintsData['activeTab'],
            'complaintCounts' => $complaintsData['counts'],
        ])->extends('superadmin.layouts.app')
            ->title('Dashboard & Pusat Aduan — SuperAdmin MirrorCam');
    }
}
