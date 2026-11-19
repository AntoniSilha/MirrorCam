<?php

namespace App\Services\SuperAdmin;

use App\Models\AuditLog;
use App\Models\DetailPemesananTenant;
use App\Models\KomisiPlatform;
use App\Models\PengaduanPelanggan;
use App\Models\Tenant;
use Carbon\Carbon;

class SuperAdminDashboardService
{
    /**
     * Compute 3 top KPI cards dynamically from database
     */
    public function getKpiMetrics(): array
    {
        $now = Carbon::now();
        $startThisMonth = (clone $now)->startOfMonth();
        $startLastMonth = (clone $now)->subMonth()->startOfMonth();
        $endLastMonth = (clone $now)->subMonth()->endOfMonth();

        // 1. Total Tenant Mitra (status aktif/terverifikasi pada skema enum saat ini)
        $activeStatuses = ['verified', 'aktif', 'Aktif', 'official'];

        $totalTenant = Tenant::whereIn('status_mitra', $activeStatuses)->count();

        $thisMonthTenant = Tenant::whereIn('status_mitra', $activeStatuses)
            ->where('created_at', '>=', $startThisMonth)
            ->count();

        $lastMonthTenant = Tenant::whereIn('status_mitra', $activeStatuses)
            ->whereBetween('created_at', [$startLastMonth, $endLastMonth])
            ->count();

        $tenantGrowth = $this->calculatePercentageChange($thisMonthTenant, $lastMonthTenant);

        // 2. Total Pesanan Sewa Lintas Tenant
        $totalOrders = DetailPemesananTenant::count();

        $thisMonthOrders = DetailPemesananTenant::where('created_at', '>=', $startThisMonth)->count();
        $lastMonthOrders = DetailPemesananTenant::whereBetween('created_at', [$startLastMonth, $endLastMonth])->count();

        $ordersGrowth = $this->calculatePercentageChange($thisMonthOrders, $lastMonthOrders);

        // 3. Pendapatan Komisi Platform (10%)
        $totalKomisi = (float) KomisiPlatform::sum('total_komisi');
        if ($totalKomisi <= 0 && $totalOrders > 0) {
            // Fallback estimation 10% GMV if komisi_platforms hasn't been individually generated
            $totalKomisi = (float) DetailPemesananTenant::sum('total_harga') * 0.10;
        }

        $thisMonthKomisi = (float) KomisiPlatform::where('created_at', '>=', $startThisMonth)->sum('total_komisi');
        $lastMonthKomisi = (float) KomisiPlatform::whereBetween('created_at', [$startLastMonth, $endLastMonth])->sum('total_komisi');

        $komisiGrowth = $this->calculatePercentageChange($thisMonthKomisi, $lastMonthKomisi);

        return [
            'totalTenant' => $totalTenant,
            'tenantGrowth' => $tenantGrowth,
            'totalOrders' => $totalOrders,
            'ordersGrowth' => $ordersGrowth,
            'totalKomisi' => $totalKomisi,
            'totalKomisiFormatted' => $this->formatRupiahCompact($totalKomisi),
            'komisiGrowth' => $komisiGrowth,
        ];
    }

    /**
     * Compute 6-month historical tenant registration chart data (Aktif vs Validasi)
     */
    public function getTenantGrowthChartData(): array
    {
        $months = [];
        $indonesianMonths = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun',
            7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
        ];

        // 6 months ending at current month
        for ($i = 5; $i >= 0; $i--) {
            $target = Carbon::now()->subMonths($i);
            $year = $target->year;
            $month = $target->month;
            $label = $indonesianMonths[$month];

            $start = Carbon::create($year, $month, 1)->startOfMonth();
            $end = (clone $start)->endOfMonth();

            $aktifCount = Tenant::whereIn('status_mitra', ['verified', 'aktif', 'Aktif', 'official'])
                ->where('created_at', '<=', $end)
                ->count();

            $validasiCount = Tenant::whereIn('status_mitra', ['noverified'])
                ->whereBetween('created_at', [$start, $end])
                ->count();

            $months[] = [
                'label' => $label,
                'aktif' => $aktifCount,
                'validasi' => $validasiCount,
                'is_current' => ($i === 0),
            ];
        }

        $maxVal = 1;
        foreach ($months as $m) {
            $maxVal = max($maxVal, $m['aktif'], $m['validasi']);
        }

        return [
            'months' => $months,
            'maxValue' => $maxVal,
            'latestDiff' => end($months)['aktif'] ?? 0,
        ];
    }

    /**
     * Compute 6-month commission progress line chart data
     */
    public function getCommissionChartData(): array
    {
        $months = [];
        $indonesianMonths = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun',
            7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
        ];

        $runningSum = 0;
        for ($i = 5; $i >= 0; $i--) {
            $target = Carbon::now()->subMonths($i);
            $year = $target->year;
            $month = $target->month;
            $label = $indonesianMonths[$month];

            $start = Carbon::create($year, $month, 1)->startOfMonth();
            $end = (clone $start)->endOfMonth();

            $monthlyKomisi = (float) KomisiPlatform::whereBetween('created_at', [$start, $end])->sum('total_komisi');
            if ($monthlyKomisi <= 0) {
                $monthlyGmv = (float) DetailPemesananTenant::whereBetween('created_at', [$start, $end])->sum('total_harga');
                $monthlyKomisi = $monthlyGmv * 0.10;
            }

            $runningSum += $monthlyKomisi;

            $months[] = [
                'label' => $label,
                'monthly' => $monthlyKomisi,
                'cumulative' => $runningSum,
                'is_current' => ($i === 0),
            ];
        }

        $maxCumulative = max(1.0, (float) end($months)['cumulative']);

        return [
            'months' => $months,
            'maxCumulative' => $maxCumulative,
            'latestFormatted' => 'Rp '.number_format($maxCumulative, 0, ',', '.'),
        ];
    }

    /**
     * Get complaints directly connected to PengaduanPelanggan model with real-time relations
     */
    public function getComplaints(array $filters = [], int $perPage = 5, int $page = 1): array
    {
        $statusTab = strtolower($filters['status'] ?? 'semua');

        $baseQuery = PengaduanPelanggan::with(['user', 'tenant', 'pemesanan']);

        // Tab Counts
        $totalAll = PengaduanPelanggan::count();
        $totalPerlu = PengaduanPelanggan::where(function ($q) {
            $q->whereNull('status_kasus')
                ->orWhereRaw('LOWER(status_kasus) != ?', ['selesai']);
        })->count();
        $totalSelesai = PengaduanPelanggan::whereRaw('LOWER(status_kasus) = ?', ['selesai'])->count();

        // Apply Tab Filter
        if ($statusTab === 'perlu_ditangani' || $statusTab === 'perlu') {
            $baseQuery->where(function ($q) {
                $q->whereNull('status_kasus')
                    ->orWhereRaw('LOWER(status_kasus) != ?', ['selesai']);
            });
        } elseif ($statusTab === 'selesai') {
            $baseQuery->whereRaw('LOWER(status_kasus) = ?', ['selesai']);
        }

        $complaints = $baseQuery->orderBy('created_at', 'desc')->paginate($perPage, ['*'], 'page', max(1, $page));

        return [
            'complaints' => $complaints,
            'activeTab' => $statusTab,
            'counts' => [
                'all' => $totalAll,
                'perlu' => $totalPerlu,
                'selesai' => $totalSelesai,
            ],
        ];
    }

    /**
     * Mark complaint as resolved with justification and record to audit_logs (SRS UC-25 & NFR-AUD-01)
     */
    public function resolveComplaint(int $id, string $justifikasi, ?string $keputusan = null): PengaduanPelanggan
    {
        $complaint = PengaduanPelanggan::findOrFail($id);

        $complaint->update([
            'status_kasus' => 'Selesai',
            'status_klaim' => $keputusan ?: 'Selesai',
            'catatan_resolusi' => $justifikasi,
        ]);

        // Record to audit_logs (append-only)
        AuditLog::record(
            action: 'pengaduan_resolved',
            description: "SuperAdmin menyelesaikan sengketa pengaduan tiket {$complaint->nomor_tiket} dengan justifikasi: {$justifikasi}",
            auditable: $complaint,
            details: [
                'id_pengaduan' => $complaint->id_pengaduan,
                'nomor_tiket' => $complaint->nomor_tiket,
                'keputusan' => $keputusan,
                'catatan_resolusi' => $justifikasi,
            ]
        );

        return $complaint;
    }

    /**
     * Helper to compute percentage growth
     */
    protected function calculatePercentageChange(float|int $current, float|int $previous): string
    {
        if ($previous <= 0) {
            return $current > 0 ? '+100%' : '+0.0%';
        }

        $diff = (($current - $previous) / $previous) * 100;
        $sign = $diff >= 0 ? '+' : '';

        return $sign.number_format($diff, 1).'%';
    }

    /**
     * Format Rupiah compactly (e.g. Rp 142.5jt or Rp 0)
     */
    protected function formatRupiahCompact(float $amount): string
    {
        if ($amount <= 0) {
            return 'Rp 0';
        }

        if ($amount >= 1_000_000_000) {
            return 'Rp '.rtrim(rtrim(number_format($amount / 1_000_000_000, 1, '.', ''), '0'), '.').'M';
        }

        if ($amount >= 1_000_000) {
            return 'Rp '.rtrim(rtrim(number_format($amount / 1_000_000, 1, '.', ''), '0'), '.').'jt';
        }

        return 'Rp '.number_format($amount, 0, ',', '.');
    }
}
