<?php
use App\Models\Tenant;
use App\Modules\TenantManagement\Concerns\BuildsRevenueChart;
use App\Modules\TenantManagement\Concerns\ManagesTenantProducts;
use App\Modules\TenantManagement\Concerns\ManagesTenantProfile;
use App\Modules\TenantManagement\UseCases\GetTenantDashboardMetricsUseCase;
use App\Modules\TenantManagement\Concerns\ManagesCommissionQris;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Symfony\Component\HttpFoundation\StreamedResponse;

new #[Title('Dashboard Mitra - MirrorCam')] #[Layout('components.layouts.mitra')] class extends Component
{
    use BuildsRevenueChart;
    use ManagesTenantProducts;
    use ManagesTenantProfile;
    use WithFileUploads;
    use ManagesCommissionQris;

    public string $section = 'ringkasan';

    public bool $sidebarOpen = false;

    public bool $maintenanceMode = false;

    public string $productSearch = '';

    public string $bookingSearch = '';

    public function mount(): void
    {
        $tenant = $this->tenant();

        $this->maintenanceMode = (bool) ($tenant?->mode_libur ?? false);
        $this->initTenantProfile();
    }

    protected function tenant(): ?Tenant
    {
        return Tenant::query()
            ->where('id_akun_pemilik', Auth::user()->id_akun)
            ->first();
    }

    #[Computed]
    public function dashboard(): array
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        return app(GetTenantDashboardMetricsUseCase::class)->execute($user, $this->productSearch, $this->bookingSearch);
    }

    public function showSection(string $section): void
    {
        if (in_array($section, ['ringkasan', 'produk', 'pesanan', 'profil', 'keamanan', 'komisi'], true)) {
            $this->section = $section;
            $this->sidebarOpen = false;
        }
    }

    public function toggleSidebar(): void
    {
        $this->sidebarOpen = ! $this->sidebarOpen;
    }

    public function toggleMaintenance(): void
    {
        $tenant = $this->tenant();

        if (! $tenant) {
            $this->addError('maintenanceMode', 'Lengkapi profil tenant terlebih dahulu.');

            return;
        }

        $this->maintenanceMode = ! $this->maintenanceMode;

        $tenant->update(['mode_libur' => $this->maintenanceMode]);
        unset($this->dashboard);

        session()->flash(
            'status',
            $this->maintenanceMode
                ? 'Mode libur diaktifkan. Toko ditutup sementara untuk pesanan baru.'
                : 'Mode libur dinonaktifkan. Toko kembali menerima pesanan.'
        );
    }

    public function savePreferences(): void
    {
        $tenant = $this->tenant();

        if (! $tenant) {
            $this->addError('maintenanceMode', 'Lengkapi profil tenant terlebih dahulu.');

            return;
        }

        $tenant->update(['mode_libur' => $this->maintenanceMode]);
        unset($this->dashboard);

        session()->flash('status', 'Preferensi toko berhasil disimpan.');
    }

    public function exportCsv(): StreamedResponse
    {
        $dashboard = $this->dashboard;
        $rows = [
            ['Laporan Pendapatan Sewa - '.$dashboard['tenant']['nama_toko']],
            ['Periode', Carbon::now()->locale('id')->translatedFormat('F Y')],
            ['Dibuat', Carbon::now()->locale('id')->translatedFormat('d F Y H:i')],
            [],
            ['Total Produk', $dashboard['stats']['total_produk']],
            ['Penyewaan Berjalan', $dashboard['stats']['penyewaan_berjalan']],
            ['Pendapatan Tenant', $dashboard['stats']['pendapatan_tenant']],
            ['Komisi Platform', $dashboard['stats']['komisi_platform']],
            [],
            ['Periode', 'Pendapatan Bersih'],
        ];

        foreach ($dashboard['chart']['labels'] as $index => $label) {
            $rows[] = [$label, $dashboard['chart']['values'][$index] ?? 0];
        }

        $rows[] = [];
        $rows[] = ['Kode Pesanan', 'Produk', 'Penyewa', 'Mulai', 'Selesai', 'Status', 'Total'];

        foreach ($dashboard['bookings'] as $booking) {
            $rows[] = [
                $booking['kode'],
                $booking['produk'],
                $booking['penyewa'],
                $booking['mulai'],
                $booking['selesai'],
                $booking['status'],
                $booking['total'],
            ];
        }

        $filename = 'laporan-pendapatan-'.Carbon::now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($rows): void {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            foreach ($rows as $row) {
                fputcsv($handle, $row, ',', '"', '');
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}; ?>

@php
    $dashboard = $this->dashboard;
    $chart = $this->chart;
    $stats = $dashboard['stats'];
    $tenant = $dashboard['tenant'];
    $hasTenant = $dashboard['has_tenant'];
    $chartHasData = collect($dashboard['chart']['values'])->sum() > 0;
    $growth = (float) $stats['tingkat_pertumbuhan'];
    $growthLabel = ($growth >= 0 ? '+' : '').number_format($growth, 1, '.', ',').'%';
@endphp

<div class="flex min-h-screen bg-[#f9f9f9]">
    @if ($sidebarOpen)
        <div class="fixed inset-0 z-40 bg-black/40 md:hidden" wire:click="toggleSidebar"></div>
    @endif

    @include('pages.mitra.partials.sidebar')

    <div class="flex min-h-screen min-w-0 flex-1 flex-col">
        @include('pages.mitra.partials.header')

        <main class="w-full flex-1 px-4 py-5 sm:px-5 sm:py-6 md:px-8 md:py-7">
            <div class="mx-auto w-full max-w-[1240px]">
                @if (session()->has('status'))
                    <div class="mb-5 flex items-center justify-between rounded-lg border border-[#bbf7d0] bg-[#dcfce7] px-4 py-3 text-sm text-[#15803d] shadow-xs">
                        <div class="flex items-center gap-2">
                            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span class="font-medium">{{ session('status') }}</span>
                        </div>
                        <button type="button" onclick="this.parentElement.remove()" class="text-[#15803d] transition hover:opacity-75">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                @endif

                @if ($section === 'ringkasan')
                    @include('pages.mitra.sections.ringkasan')
                @elseif ($section === 'produk')
                    @include('pages.mitra.sections.produk')
                @elseif ($section === 'pesanan')
                    @include('pages.mitra.sections.pesanan')
                @elseif ($section === 'profil')
                    @include('pages.mitra.sections.profil')
                @elseif ($section === 'keamanan')
                    @include('pages.mitra.sections.keamanan')
                @elseif ($section === 'komisi')
                    @include('pages.mitra.sections.komisi')
                @endif
            </div>
        </main>
    </div>

    @include('pages.mitra.partials.product-modal')
    @include('pages.mitra.partials.edit-product-modal')
</div>
