<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Services\TenantDirectoryService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class TenantController extends Controller
{
    public function __construct(
        protected TenantDirectoryService $tenantDirectoryService
    ) {}

    /**
     * Display the tenant directory exploration page (Eksplorasi Tenant)
     */
    public function index(Request $request): View
    {
        $filters = [
            'search' => $request->query('search'),
            'rating' => $request->query('rating'),
            'per_page' => $request->query('per_page', 9),
        ];

        $directoryData = $this->tenantDirectoryService->getTenants($filters);

        return view('customer.tenant.index', [
            'tenants' => $directoryData['tenants'],
            'totalTenants' => $directoryData['total'],
            'displayedTenants' => $directoryData['displayed'],
            'remainingTenants' => $directoryData['remaining'],
            'perPage' => $directoryData['perPage'],
            'activeRating' => $directoryData['rating'],
            'searchKeyword' => $directoryData['search'],
        ]);
    }

    /**
     * Display a single tenant's catalog and profile page
     */
    public function show(Tenant $tenant): View
    {
        $tenant->loadMissing([
            'detailOperasional',
            'produks.kategori',
            'produks.galeri',
            'reviews.penyewa',
        ]);

        return view('customer.tenant.show', [
            'tenant' => $tenant,
        ]);
    }
}
