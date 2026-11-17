<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Services\SuperAdmin\TenantApprovalService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TenantManagementController extends Controller
{
    public function __construct(
        protected TenantApprovalService $tenantApprovalService
    ) {}

    /**
     * Display tenant partner management page
     */
    public function index(Request $request): View
    {
        $kpi = $this->tenantApprovalService->getKpiMetrics();

        $filters = [
            'tab' => $request->query('tab', 'all'),
            'search' => $request->query('search', ''),
            'city' => $request->query('city', ''),
        ];

        $tenantData = $this->tenantApprovalService->getTenants($filters, 10);

        return view('superadmin.tenant.index', [
            'kpi' => $kpi,
            'tenants' => $tenantData['tenants'],
            'activeTab' => $tenantData['activeTab'],
            'search' => $tenantData['search'],
            'selectedCity' => $tenantData['selectedCity'],
            'counts' => $tenantData['counts'],
            'cities' => $tenantData['cities'],
        ]);
    }

    /**
     * Approve / activate a tenant partner (SRS UC-23)
     */
    public function approve(int $id): RedirectResponse
    {
        $tenant = $this->tenantApprovalService->approveTenant($id);

        return redirect()->back()
            ->with('status', "Mitra #TNT-{$tenant->id_tenant} ({$tenant->nama_toko}) berhasil disetujui dan berstatus aktif.");
    }

    /**
     * Suspend a tenant partner (SRS UC-23)
     */
    public function suspend(Request $request, int $id): RedirectResponse
    {
        $reason = $request->input('alasan');
        $tenant = $this->tenantApprovalService->suspendTenant($id, $reason);

        return redirect()->back()
            ->with('status', "Mitra #TNT-{$tenant->id_tenant} ({$tenant->nama_toko}) telah ditangguhkan.");
    }

    /**
     * Onboard manual tenant (SRS UC-23 / FR-ADM-01)
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_toko' => ['required', 'string', 'max:150'],
            'nama_penanggungjawab' => ['required', 'string', 'max:100'],
            'jabatan_penanggungjawab' => ['nullable', 'string', 'max:100'],
            'whatsapp_resmi' => ['required', 'string', 'max:25'],
            'email_resmi' => ['required', 'email', 'max:150'],
            'alamat_toko' => ['required', 'string'],
            'kota' => ['required', 'string', 'max:100'],
            'nama_bank' => ['nullable', 'string', 'max:50'],
            'nomor_rekening' => ['nullable', 'string', 'max:50'],
            'nama_pemilik_rekening' => ['nullable', 'string', 'max:100'],
            'status_mitra' => ['nullable', 'string', 'in:aktif,verified,noverified,Menunggu Verifikasi'],
        ]);

        $tenant = $this->tenantApprovalService->createManualTenant($validated);

        return redirect()->route('superadmin.tenant.index')
            ->with('status', "Mitra baru {$tenant->nama_toko} (#TNT-{$tenant->id_tenant}) berhasil ditambahkan dan dicatat ke log audit.");
    }

    /**
     * Export tenants to CSV / XLSX download
     */
    public function export(): Response
    {
        $csvData = $this->tenantApprovalService->exportTenantsCsv();
        $fileName = 'rekap_mitra_tenant_'.date('Ymd_His').'.csv';

        return response($csvData, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ]);
    }
}
