<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Services\SuperAdmin\SuperAdminDashboardService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        protected SuperAdminDashboardService $dashboardService
    ) {}

    /**
     * Display the main SuperAdmin summary dashboard + complaint center
     */
    public function index(Request $request): View
    {
        $kpi = $this->dashboardService->getKpiMetrics();
        $tenantGrowthChart = $this->dashboardService->getTenantGrowthChartData();
        $commissionChart = $this->dashboardService->getCommissionChartData();

        $complaintFilters = [
            'status' => $request->query('status', 'semua'),
        ];
        $complaintsData = $this->dashboardService->getComplaints($complaintFilters, 5);

        return view('superadmin.dashboard.index', [
            'kpi' => $kpi,
            'tenantGrowthChart' => $tenantGrowthChart,
            'commissionChart' => $commissionChart,
            'complaints' => $complaintsData['complaints'],
            'activeComplaintTab' => $complaintsData['activeTab'],
            'complaintCounts' => $complaintsData['counts'],
        ]);
    }

    /**
     * Resolve customer dispute / complaint (SRS UC-25)
     */
    public function resolveComplaint(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'justifikasi' => ['required', 'string', 'min:5'],
            'keputusan' => ['nullable', 'string', 'max:100'],
        ]);

        $this->dashboardService->resolveComplaint(
            id: $id,
            justifikasi: $validated['justifikasi'],
            keputusan: $validated['keputusan'] ?? 'Selesai & Kasus Ditutup'
        );

        return redirect()->route('superadmin.dashboard')
            ->with('status', 'Sengketa aduan berhasil diselesaikan dan dicatat ke log audit platform.');
    }
}
