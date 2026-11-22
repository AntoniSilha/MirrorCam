<?php
use App\Models\Tenant;
use App\Modules\TenantManagement\Concerns\BuildsRevenueChart;
use App\Modules\TenantManagement\Concerns\ManagesTenantProducts;
use App\Modules\TenantManagement\Concerns\ManagesTenantProfile;
use App\Modules\TenantManagement\UseCases\GetTenantDashboardMetricsUseCase;
use App\Modules\TenantManagement\Concerns\ManagesCommissionQris;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
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

    public bool $showChecklistModal = false;

    public ?int $selectedBookingId = null;

    public string $catatanPembayaran = '';

    public bool $checklistIdentitasOk = false;

    public bool $checklistKondisiAwalOk = false;

    public $fotoKondisiAwal = null;

    public string $catatanSerahTerima = '';

    public bool $checklistKondisiAkhirOk = false;

    public string $kondisiKerusakan = 'baik';

    public string $deskripsiKerusakan = '';

    public string $estimasiBiayaKerusakan = '0';

    public $fotoKondisiAkhir = null;

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

    public function openChecklistModal(int $bookingId): void
    {
        $this->selectedBookingId = $bookingId;
        $this->resetChecklistForm();
        $this->showChecklistModal = true;
    }

    public function closeChecklistModal(): void
    {
        $this->showChecklistModal = false;
        $this->resetChecklistForm();
    }

    public function resetChecklistForm(): void
    {
        $this->resetValidation();
        $this->catatanPembayaran = '';
        $this->checklistIdentitasOk = false;
        $this->checklistKondisiAwalOk = false;
        $this->fotoKondisiAwal = null;
        $this->catatanSerahTerima = '';
        $this->checklistKondisiAkhirOk = false;
        $this->kondisiKerusakan = 'baik';
        $this->deskripsiKerusakan = '';
        $this->estimasiBiayaKerusakan = '0';
        $this->fotoKondisiAkhir = null;
    }

    #[Computed]
    public function selectedBooking(): ?array
    {
        if (! $this->selectedBookingId) {
            return null;
        }

        foreach ($this->dashboard['bookings'] as $booking) {
            if ($booking['id'] === $this->selectedBookingId) {
                return $booking;
            }
        }

        return null;
    }

    public function verifyPayment(int $bookingId): void
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        try {
            app(\App\Services\Mitra\OrderChecklistService::class)->verifyPayment(
                $user,
                $bookingId,
                ['catatan' => $this->catatanPembayaran]
            );

            unset($this->dashboard, $this->chart);
            session()->flash('status', 'Tahap 1 Berhasil: Pelunasan pembayaran telah diverifikasi.');
        } catch (\Throwable $e) {
            $this->addError('checklist_payment', $e->getMessage());
        }
    }

    public function confirmHandover(int $bookingId): void
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $this->validate([
            'checklistIdentitasOk' => ['accepted'],
            'checklistKondisiAwalOk' => ['accepted'],
            'fotoKondisiAwal' => ['nullable', 'image', 'max:5120'],
        ], [
            'checklistIdentitasOk.accepted' => 'Verifikasi kecocokan identitas KTP pelanggan wajib dicentang.',
            'checklistKondisiAwalOk.accepted' => 'Verifikasi kelengkapan unit wajib dicentang.',
            'fotoKondisiAwal.image' => 'File foto serah terima harus berupa gambar.',
        ]);

        $fotoPath = null;
        if ($this->fotoKondisiAwal) {
            $path = $this->fotoKondisiAwal->store('checklist/handover', 'public');
            if ($path) {
                $fotoPath = Storage::disk('public')->url($path);
            }
        }

        try {
            app(\App\Services\Mitra\OrderChecklistService::class)->confirmHandover($user, $bookingId, [
                'identitas_terverifikasi' => $this->checklistIdentitasOk,
                'kondisi_fisik_awal_ok' => $this->checklistKondisiAwalOk,
                'foto_kondisi_awal' => $fotoPath,
                'catatan_serah_terima' => $this->catatanSerahTerima,
            ]);

            unset($this->dashboard, $this->chart);
            $this->fotoKondisiAwal = null;
            session()->flash('status', 'Tahap 2 Berhasil: Serah terima unit telah dikonfirmasi dan status peminjaman aktif.');
        } catch (\Throwable $e) {
            $this->addError('checklist_handover', $e->getMessage());
        }
    }

    public function confirmReturn(int $bookingId): void
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $rules = [
            'checklistKondisiAkhirOk' => ['accepted'],
            'kondisiKerusakan' => ['required', 'in:baik,rusak'],
            'fotoKondisiAkhir' => ['nullable', 'image', 'max:5120'],
        ];

        if ($this->kondisiKerusakan === 'rusak') {
            $rules['deskripsiKerusakan'] = ['required', 'string', 'min:5', 'max:1000'];
            $rules['estimasiBiayaKerusakan'] = ['required', 'numeric', 'min:0'];
        }

        $this->validate($rules, [
            'checklistKondisiAkhirOk.accepted' => 'Pemeriksaan kelengkapan fisik akhir wajib dicentang.',
            'deskripsiKerusakan.required' => 'Deskripsi kerusakan wajib diisi jika ditemukan kerusakan pada unit.',
            'estimasiBiayaKerusakan.required' => 'Estimasi biaya klaim kerusakan wajib diisi.',
            'fotoKondisiAkhir.image' => 'File foto pengembalian harus berupa gambar.',
        ]);

        $fotoPath = null;
        if ($this->fotoKondisiAkhir) {
            $path = $this->fotoKondisiAkhir->store('checklist/return', 'public');
            if ($path) {
                $fotoPath = Storage::disk('public')->url($path);
            }
        }

        try {
            app(\App\Services\Mitra\OrderChecklistService::class)->confirmReturn($user, $bookingId, [
                'kondisi_fisik_akhir_ok' => $this->checklistKondisiAkhirOk,
                'kondisi_kerusakan' => $this->kondisiKerusakan,
                'deskripsi_kerusakan' => $this->deskripsiKerusakan,
                'estimasi_biaya_kerusakan' => (float) $this->estimasiBiayaKerusakan,
                'foto_kondisi_akhir' => $fotoPath,
            ]);

            unset($this->dashboard, $this->chart);
            $this->fotoKondisiAkhir = null;
            session()->flash('status', 'Tahap 3 Berhasil: Pengembalian unit telah diverifikasi dan peminjaman selesai.');
        } catch (\Throwable $e) {
            $this->addError('checklist_return', $e->getMessage());
        }
    }

    public function statusBadgeClasses(string $status): string
    {
        $normalized = strtolower($status);

        return match (true) {
            str_contains($normalized, 'selesai') || str_contains($normalized, 'completed') || str_contains($normalized, 'lunas') => 'border-[#fff700] bg-[#fff700] text-[#000000] rounded-[4px]',
            str_contains($normalized, 'disputed') || str_contains($normalized, 'sengketa') || str_contains($normalized, 'terlambat') || str_contains($normalized, 'overdue') || str_contains($normalized, 'batal') || str_contains($normalized, 'habis') => 'border-[#ffdad6] bg-[#ffdad6] text-[#ba1a1a] rounded-[4px]',
            str_contains($normalized, 'disewa') || str_contains($normalized, 'aktif') || str_contains($normalized, 'in_use') => 'border-[#dcfce7] bg-[#dcfce7] text-[#16a34a] rounded-[4px]',
            default => 'border-[#d1d5db] bg-[#f3f4f6] text-[#374151] rounded-[4px]',
        };
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

<div class="flex min-h-screen bg-[#f9f9f9] font-['Montserrat',sans-serif]">
    @if ($sidebarOpen)
        <div class="fixed inset-0 z-40 bg-black/40 md:hidden" wire:click="toggleSidebar"></div>
    @endif

    @include('pages.mitra.partials.sidebar')

    <div class="flex min-h-screen min-w-0 flex-1 flex-col">
        @include('pages.mitra.partials.header')

        <main class="w-full flex-1 px-4 py-5 sm:px-5 sm:py-6 md:px-8 md:py-7">
            <div class="mx-auto w-full max-w-[1240px]">
                @if (session()->has('status'))
                    <div class="mb-5 flex items-center justify-between rounded-[4px] border border-[#bbf7d0] bg-[#dcfce7] px-4 py-3 text-sm text-[#15803d] shadow-xs">
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
    @include('pages.mitra.partials.checklist-modal')
</div>
