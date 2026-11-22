<?php

namespace App\Services\SuperAdmin;

use App\Models\AccountGoogle;
use App\Models\AuditLog;
use App\Models\DetailOperasionalTenant;
use App\Models\Produk;
use App\Models\RekeningSettlementTenant;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TenantApprovalService
{
    /**
     * Compute 3 top KPI cards for tenant management
     */
    public function getKpiMetrics(): array
    {
        $totalToko = Tenant::count();

        $antreanVerifikasi = Tenant::where(function (Builder $q) {
            $q->where('status_mitra', 'noverified')
                ->orWhere('status_mitra', 'like', '%verifikasi%')
                ->orWhere('status_mitra', 'like', '%validasi%')
                ->orWhere('status_mitra', 'like', '%pending%')
                ->orWhere('status_mitra', 'Menunggu Verifikasi');
        })->count();

        $stockColumn = Schema::hasColumn('produks', 'total_stok_unit')
            ? 'total_stok_unit'
            : (Schema::hasColumn('produks', 'total_stock_unit') ? 'total_stock_unit' : null);

        $alatDisewa = $stockColumn ? (int) Produk::sum($stockColumn) : 0;

        return [
            'totalToko' => $totalToko,
            'antreanVerifikasi' => $antreanVerifikasi,
            'alatDisewa' => $alatDisewa,
        ];
    }

    /**
     * Retrieve paginated tenants with filtering and tab counts
     */
    public function getTenants(array $filters = [], int $perPage = 10, int $page = 1): array
    {
        $activeTab = strtolower($filters['tab'] ?? 'all');
        $search = trim($filters['search'] ?? '');
        $city = trim($filters['city'] ?? '');

        // Tab counts
        $totalAll = Tenant::count();
        $totalMenunggu = Tenant::where(function (Builder $q) {
            $q->where('status_mitra', 'noverified')
                ->orWhere('status_mitra', 'like', '%verifikasi%')
                ->orWhere('status_mitra', 'like', '%validasi%')
                ->orWhere('status_mitra', 'like', '%pending%')
                ->orWhere('status_mitra', 'Menunggu Verifikasi');
        })->count();

        $totalAktif = Tenant::where(function (Builder $q) {
            $q->where('status_mitra', 'verified')
                ->orWhere('status_mitra', 'aktif')
                ->orWhere('status_mitra', 'Aktif')
                ->orWhere('status_mitra', 'official');
        })->count();

        $totalSuspend = Tenant::where(function (Builder $q) {
            $q->where('status_mitra', 'like', '%tangguh%')
                ->orWhere('status_mitra', 'like', '%suspend%')
                ->orWhere('status_mitra', 'nonaktif');
        })->count();

        $query = Tenant::with([
            'detailOperasional',
            'rekeningSettlement',
            'produks',
        ])->withAvg('reviews as avg_rating', 'rating_nilai');

        // Apply Tab Filter
        if ($activeTab === 'menunggu_validasi' || $activeTab === 'menunggu') {
            $query->where(function (Builder $q) {
                $q->where('status_mitra', 'noverified')
                    ->orWhere('status_mitra', 'like', '%verifikasi%')
                    ->orWhere('status_mitra', 'like', '%validasi%')
                    ->orWhere('status_mitra', 'like', '%pending%')
                    ->orWhere('status_mitra', 'Menunggu Verifikasi');
            });
        } elseif ($activeTab === 'mitra_aktif' || $activeTab === 'aktif') {
            $query->where(function (Builder $q) {
                $q->where('status_mitra', 'verified')
                    ->orWhere('status_mitra', 'aktif')
                    ->orWhere('status_mitra', 'Aktif')
                    ->orWhere('status_mitra', 'official');
            });
        } elseif ($activeTab === 'ditangguhkan' || $activeTab === 'suspend') {
            $query->where(function (Builder $q) {
                $q->where('status_mitra', 'like', '%tangguh%')
                    ->orWhere('status_mitra', 'like', '%suspend%')
                    ->orWhere('status_mitra', 'nonaktif');
            });
        }

        // Apply Search Filter
        if ($search !== '') {
            $query->where(function (Builder $q) use ($search) {
                // Remove TNT prefix if searched like #TNT-001 or TNT-001
                $cleanId = preg_replace('/[^0-9]/', '', $search);

                if (! empty($cleanId) && is_numeric($cleanId)) {
                    $q->orWhere('id_tenant', (int) $cleanId);
                }

                $q->orWhere('nama_toko', 'like', "%{$search}%")
                    ->orWhere('nama_penanggungjawab', 'like', "%{$search}%")
                    ->orWhere('email_resmi', 'like', "%{$search}%")
                    ->orWhere('whatsapp_resmi', 'like', "%{$search}%")
                    ->orWhere('alamat_toko', 'like', "%{$search}%");
            });
        }

        // Apply City Filter
        if ($city !== '' && strtolower($city) !== 'semua wilayah' && strtolower($city) !== 'all') {
            $query->where('alamat_toko', 'like', "%{$city}%");
        }

        $tenants = $query->orderBy('created_at', 'desc')->paginate($perPage, ['*'], 'page', max(1, $page));

        return [
            'tenants' => $tenants,
            'activeTab' => $activeTab,
            'search' => $search,
            'selectedCity' => $city,
            'counts' => [
                'all' => $totalAll,
                'menunggu' => $totalMenunggu,
                'aktif' => $totalAktif,
                'suspend' => $totalSuspend,
            ],
            'cities' => $this->getAllCities(),
        ];
    }

    /**
     * Retrieve distinct list of cities
     */
    public function getAllCities(): array
    {
        $addresses = Tenant::pluck('alamat_toko');
        $cities = [];

        foreach ($addresses as $addr) {
            if (! empty($addr)) {
                $parts = explode(',', $addr);
                $cityName = trim(end($parts));
                if (! empty($cityName) && ! in_array($cityName, $cities, true)) {
                    $cities[] = $cityName;
                }
            }
        }

        sort($cities);

        return $cities;
    }

    /**
     * Approve / activate a tenant partner (SRS UC-23)
     */
    public function approveTenant(int $id): Tenant
    {
        $tenant = Tenant::findOrFail($id);
        $oldStatus = $tenant->status_mitra;

        $newStatus = 'verified';
        $tenant->update(['status_mitra' => $newStatus]);

        AuditLog::record(
            action: 'tenant_approved',
            description: "SuperAdmin menyetujui dan mengaktifkan tenant ID #TNT-{$tenant->id_tenant} ({$tenant->nama_toko}). Status sebelumnya: {$oldStatus}",
            auditable: $tenant,
            details: [
                'id_tenant' => $tenant->id_tenant,
                'nama_toko' => $tenant->nama_toko,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
            ]
        );

        return $tenant;
    }

    /**
     * Suspend / de-activate a tenant partner (SRS UC-23)
     */
    public function suspendTenant(int $id, ?string $reason = null): Tenant
    {
        $tenant = Tenant::findOrFail($id);
        $oldStatus = $tenant->status_mitra;
        $justification = $reason ?: 'Pelanggaran ketentuan operasional atau penangguhan administratif platform';

        $newStatus = 'noverified';
        $tenant->update(['status_mitra' => $newStatus]);

        AuditLog::record(
            action: 'tenant_suspended',
            description: "SuperAdmin menangguhkan tenant ID #TNT-{$tenant->id_tenant} ({$tenant->nama_toko}). Alasan: {$justification}",
            auditable: $tenant,
            details: [
                'id_tenant' => $tenant->id_tenant,
                'nama_toko' => $tenant->nama_toko,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'alasan' => $justification,
            ]
        );

        return $tenant;
    }

    /**
     * Onboard a manual tenant partner (SRS UC-23 / FR-ADM-01)
     */
    public function createManualTenant(array $data): Tenant
    {
        return DB::transaction(function () use ($data) {
            // Find or create owner user
            $email = strtolower(trim($data['email_resmi'] ?? ''));
            $user = User::where('email', $email)->first();

            if (! $user) {
                $googleId = 'manual-owner-'.uniqid();
                AccountGoogle::firstOrCreate([
                    'id_user_google' => $googleId,
                ], [
                    'email' => $email ?: ($googleId.'@mirrorcam.test'),
                ]);

                $user = User::create([
                    'id_user_google' => $googleId,
                    'role' => 'mitra',
                    'nama_lengkap_ktp' => $data['nama_penanggungjawab'] ?? 'PIC Tenant',
                    'nik_ktp' => $data['nik'] ?? ((string) random_int(1000000000000000, 9999999999999999)),
                    'no_whatsapp' => $data['whatsapp_resmi'] ?? '081234567890',
                ]);
            } else {
                $user->update(['role' => 'mitra']);
            }

            $city = trim($data['kota'] ?? 'Jakarta');
            $fullAddress = trim($data['alamat_toko'] ?? '');
            if (! str_contains(strtolower($fullAddress), strtolower($city))) {
                $fullAddress .= ', '.$city;
            }

            $rawStatus = strtolower($data['status_mitra'] ?? 'verified');
            $statusMitra = in_array($rawStatus, ['aktif', 'verified', 'official'], true) ? 'verified' : 'noverified';

            $tenant = Tenant::create([
                'google_id_token' => $user->id_user_google,
                'id_akun_pemilik' => $user->id_akun,
                'nama_toko' => $data['nama_toko'],
                'bio_deskripsi' => $data['bio_deskripsi'] ?? 'Penyedia gear bioskop dan multimedia terverifikasi.',
                'whatsapp_resmi' => $data['whatsapp_resmi'],
                'nama_penanggungjawab' => $data['nama_penanggungjawab'],
                'jabatan_penanggungjawab' => $data['jabatan_penanggungjawab'] ?? 'Manager Operasional',
                'email_resmi' => $email,
                'alamat_toko' => $fullAddress,
                'status_mitra' => $statusMitra,
            ]);

            DetailOperasionalTenant::create([
                'id_tenant' => $tenant->id_tenant,
                'jam_buka' => $data['jam_buka'] ?? '08:00:00',
                'jam_tutup' => $data['jam_tutup'] ?? '22:00:00',
                'hari_operasional' => $data['hari_operasional'] ?? 'Senin - Minggu',
            ]);

            if (! empty($data['nama_bank']) && ! empty($data['nomor_rekening'])) {
                RekeningSettlementTenant::create([
                    'id_tenant' => $tenant->id_tenant,
                    'nama_bank' => $data['nama_bank'],
                    'nomor_rekening' => $data['nomor_rekening'],
                    'nama_pemilik_rekening' => $data['nama_pemilik_rekening'] ?? $data['nama_penanggungjawab'],
                ]);
            }

            AuditLog::record(
                action: 'tenant_created_manual',
                description: "SuperAdmin menambahkan mitra tenant manual baru: {$tenant->nama_toko} (#TNT-{$tenant->id_tenant})",
                auditable: $tenant,
                details: [
                    'id_tenant' => $tenant->id_tenant,
                    'nama_toko' => $tenant->nama_toko,
                    'status_mitra' => $tenant->status_mitra,
                ]
            );

            return $tenant;
        });
    }

    /**
     * Generate CSV export for tenants
     */
    public function exportTenantsCsv(): string
    {
        $tenants = Tenant::with(['detailOperasional', 'rekeningSettlement'])->orderBy('id_tenant')->get();

        $output = "ID Tenant,Nama Toko,PIC,Email,WhatsApp,Alamat,Kota,Bank,Nomor Rekening,Status,Terdaftar\n";

        foreach ($tenants as $t) {
            $output .= sprintf(
                "\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\"\n",
                $t->id_mitra_formatted,
                addslashes($t->nama_toko),
                addslashes($t->nama_penanggungjawab),
                $t->email_resmi,
                $t->whatsapp_resmi,
                addslashes($t->alamat_toko),
                addslashes($t->kota),
                $t->rekeningSettlement->nama_bank ?? '-',
                $t->rekeningSettlement->nomor_rekening ?? '-',
                $t->status_mitra,
                $t->created_at?->format('Y-m-d H:i') ?? '-'
            );
        }

        return $output;
    }
}
