<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Services\SuperAdmin\KomisiRekapService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class KomisiPlatformController extends Controller
{
    public function __construct(
        protected KomisiRekapService $komisiRekapService
    ) {}

    /**
     * Display commission reconciliation page
     */
    public function index(Request $request): View
    {
        $kpi = $this->komisiRekapService->getKpiMetrics();

        $filters = [
            'search' => $request->query('search', ''),
        ];

        $listData = $this->komisiRekapService->getKomisiList($filters, 10);

        return view('superadmin.komisi.index', [
            'kpi' => $kpi,
            'komisiList' => $listData['komisiList'],
            'search' => $listData['search'],
        ]);
    }

    /**
     * Verify commission payout settlement (SRS UC-24 / FR-ADM-02)
     */
    public function verify(Request $request, int $id): RedirectResponse
    {
        $notes = $request->input('catatan');
        $komisi = $this->komisiRekapService->verifyKomisiPayment($id, $notes);

        return redirect()->back()
            ->with('status', "Pembayaran komisi transaksi {$komisi->kode_komisi_formatted} berhasil diverifikasi dan dicatat ke log audit.");
    }

    /**
     * Export commission report to CSV / XLSX
     */
    public function export(): Response
    {
        $csvData = $this->komisiRekapService->exportKomisiCsv();
        $fileName = 'rekap_komisi_platform_'.date('Ymd_His').'.csv';

        return response($csvData, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ]);
    }
}
