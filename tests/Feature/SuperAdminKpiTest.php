<?php

use App\Models\AccountGoogle;
use App\Models\Tenant;
use App\Models\User;
use App\Services\SuperAdmin\SuperAdminDashboardService;
use App\Services\SuperAdmin\TenantApprovalService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function helperKpiOwner(): User
{
    $googleId = 'google-'.uniqid();

    AccountGoogle::create([
        'id_user_google' => $googleId,
        'email' => 'owner-'.uniqid().'@mirrorcam.test',
    ]);

    return User::create([
        'id_user_google' => $googleId,
        'role' => 'mitra',
        'nama_lengkap_ktp' => 'Owner KPI',
        'nik_ktp' => (string) random_int(1000000000000000, 9999999999999999),
        'no_whatsapp' => '081234567890',
    ]);
}

function helperKpiTenant(User $owner, string $status): Tenant
{
    return Tenant::create([
        'google_id_token' => $owner->id_user_google,
        'id_akun_pemilik' => $owner->id_akun,
        'nama_toko' => 'Tenant KPI '.uniqid(),
        'bio_deskripsi' => 'Tenant untuk pengujian KPI.',
        'whatsapp_resmi' => '081298765432',
        'nama_penanggungjawab' => 'PIC KPI',
        'jabatan_penanggungjawab' => 'Owner',
        'email_resmi' => 'kpi-'.uniqid().'@mirrorcam.test',
        'alamat_toko' => 'Jl. KPI No. 1, Jakarta',
        'status_mitra' => $status,
    ]);
}

test('superadmin dashboard KPI counts verified tenants instead of the legacy aktif status', function () {
    $owner = helperKpiOwner();

    helperKpiTenant($owner, 'verified');
    helperKpiTenant($owner, 'verified');
    helperKpiTenant($owner, 'noverified');

    $service = app(SuperAdminDashboardService::class);
    $kpi = $service->getKpiMetrics();

    expect($kpi['totalTenant'])->toBe(2);

    $chart = $service->getTenantGrowthChartData();
    $currentMonth = collect($chart['months'])->firstWhere('is_current', true);

    expect($currentMonth['aktif'])->toBe(2);
    expect($currentMonth['validasi'])->toBe(1);
});

test('tenant management KPI counts noverified tenants in the verification queue', function () {
    $owner = helperKpiOwner();

    helperKpiTenant($owner, 'verified');
    helperKpiTenant($owner, 'noverified');
    helperKpiTenant($owner, 'noverified');

    $kpi = app(TenantApprovalService::class)->getKpiMetrics();

    expect($kpi['totalToko'])->toBe(3);
    expect($kpi['antreanVerifikasi'])->toBe(2);
});
