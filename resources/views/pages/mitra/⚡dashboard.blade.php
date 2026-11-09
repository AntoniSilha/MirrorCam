<?php

use App\Models\KategoriProduk;
use App\Models\Produk;
use App\Models\Tenant;
use App\Modules\TenantManagement\UseCases\GetTenantDashboardMetricsUseCase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Symfony\Component\HttpFoundation\StreamedResponse;

new #[Title('Dashboard Mitra - MirrorCam')] #[Layout('components.layouts.mitra')] class extends Component {
    public string $section = 'ringkasan';

    public bool $sidebarOpen = false;

    public bool $showProductModal = false;

    public bool $autoAcceptBooking = true;

    public bool $maintenanceMode = false;

    public string $nama_produk = '';

    public string $id_kategori = '';

    public string $harga_sewa = '';

    public string $total_stok_unit = '1';

    public string $deskripsi_lengkap = '';

    public function mount(): void
    {
        $this->autoAcceptBooking = (bool) session('mitra.auto_accept_booking', true);
        $this->maintenanceMode = (bool) session('mitra.maintenance_mode', false);
    }

    #[Computed]
    public function dashboard(): array
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        return app(GetTenantDashboardMetricsUseCase::class)->execute($user);
    }

    #[Computed]
    public function chart(): array
    {
        $labels = $this->dashboard['chart']['labels'] ?: ['', ''];
        $values = array_map('floatval', $this->dashboard['chart']['values'] ?: [0, 0]);

        if (count($values) < 2) {
            $values = [0, 0];
            $labels = ['', ''];
        }

        $maxValue = max($values);
        $axisStep = 10_000_000;
        $axisMax = max($axisStep, (int) (ceil($maxValue / $axisStep) * $axisStep));

        $width = 640.0;
        $height = 250.0;
        $left = 56.0;
        $right = 24.0;
        $top = 16.0;
        $bottom = 30.0;
        $plotWidth = $width - $left - $right;
        $plotHeight = $height - $top - $bottom;
        $count = count($values);

        $points = [];

        foreach ($values as $index => $value) {
            $x = $left + ($index / ($count - 1)) * $plotWidth;
            $y = $top + (1 - min(1, max(0, $value / $axisMax))) * $plotHeight;

            $points[] = [
                'x' => round($x, 2),
                'y' => round($y, 2),
                'label' => $labels[$index] ?? '',
                'value' => $value,
            ];
        }

        $linePath = $this->smoothPath($points);
        $lastPoint = $points[$count - 1];
        $baseline = $height - $bottom;
        $areaPath = $linePath.' L '.$lastPoint['x'].','.$baseline.' L '.$points[0]['x'].','.$baseline.' Z';

        $grid = [];

        for ($index = 0; $index <= 3; $index++) {
            $fraction = $index / 3;
            $grid[] = [
                'y' => round($top + $fraction * $plotHeight, 2),
                'label' => $this->compactRupiah($axisMax * (1 - $fraction)),
            ];
        }

        $tooltipText = $this->rupiah($lastPoint['value']);
        $tooltipWidth = max(72, strlen($tooltipText) * 6.1 + 16);

        return [
            'line_path' => $linePath,
            'area_path' => $areaPath,
            'points' => $points,
            'grid' => $grid,
            'last' => $lastPoint,
            'plot' => [
                'left' => $left,
                'right' => $width - $right,
                'top' => $top,
                'bottom' => $baseline,
            ],
            'tooltip' => [
                'x' => max($left, min($width - $tooltipWidth - 4, $lastPoint['x'] - $tooltipWidth - 8)),
                'y' => max(2, $lastPoint['y'] - 30),
                'w' => $tooltipWidth,
                'label' => $tooltipText,
            ],
        ];
    }

    #[Computed]
    public function categories()
    {
        return KategoriProduk::query()->orderBy('nama_kategori')->get();
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

    public function toggleAutoAccept(): void
    {
        $this->autoAcceptBooking = ! $this->autoAcceptBooking;
    }

    public function toggleMaintenance(): void
    {
        $this->maintenanceMode = ! $this->maintenanceMode;
    }

    public function savePreferences(): void
    {
        session()->put('mitra.auto_accept_booking', $this->autoAcceptBooking);
        session()->put('mitra.maintenance_mode', $this->maintenanceMode);
        session()->flash('status', 'Preferensi toko berhasil disimpan.');
    }

    public function openProductModal(): void
    {
        $this->resetValidation();
        $this->nama_produk = '';
        $this->id_kategori = '';
        $this->harga_sewa = '';
        $this->total_stok_unit = '1';
        $this->deskripsi_lengkap = '';
        $this->showProductModal = true;
    }

    public function closeProductModal(): void
    {
        $this->showProductModal = false;
        $this->resetValidation();
    }

    public function saveProduct(): void
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $tenant = Tenant::query()->where('id_akun_pemilik', $user->id_akun)->first();

        if (! $tenant) {
            $this->addError('nama_produk', 'Lengkapi profil tenant terlebih dahulu sebelum menambahkan produk.');

            return;
        }

        $validated = $this->validate([
            'nama_produk' => ['required', 'string', 'max:120'],
            'id_kategori' => ['required', 'exists:kategori_produk,id_kategori'],
            'harga_sewa' => ['required', 'numeric', 'min:0'],
            'total_stok_unit' => ['required', 'integer', 'min:0'],
            'deskripsi_lengkap' => ['nullable', 'string', 'max:1000'],
        ]);

        Produk::query()->create([
            'id_tenant' => $tenant->id_tenant,
            'id_kategori' => (int) $validated['id_kategori'],
            'nama_produk' => $validated['nama_produk'],
            'deskripsi_lengkap' => $validated['deskripsi_lengkap'] ?: '-',
            'harga_sewa' => $validated['harga_sewa'],
            'total_stok_unit' => (int) $validated['total_stok_unit'],
        ]);

        unset($this->dashboard, $this->chart);

        $this->showProductModal = false;
        $this->resetValidation();
        $this->nama_produk = '';
        $this->id_kategori = '';
        $this->harga_sewa = '';
        $this->total_stok_unit = '1';
        $this->deskripsi_lengkap = '';

        session()->flash('status', 'Produk baru berhasil ditambahkan.');
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

    public function rupiah(float|int|string|null $value): string
    {
        return 'Rp '.number_format((float) $value, 0, ',', '.');
    }

    public function compactRupiah(float|int|string|null $value): string
    {
        $amount = (float) $value;

        if ($amount >= 1_000_000_000) {
            return 'Rp '.rtrim(rtrim(number_format($amount / 1_000_000_000, 1, ',', '.'), '0'), ',').' M';
        }

        if ($amount >= 1_000_000) {
            return 'Rp '.rtrim(rtrim(number_format($amount / 1_000_000, 1, ',', '.'), '0'), ',').' Jt';
        }

        if ($amount >= 1_000) {
            return 'Rp '.number_format($amount / 1_000, 0, ',', '.').' Rb';
        }

        return 'Rp '.number_format($amount, 0, ',', '.');
    }

    public function statusBadgeClasses(string $status): string
    {
        $normalized = strtolower($status);

        return match (true) {
            str_contains($normalized, 'selesai') => 'border-[#fde68a] bg-[#fef3c7] text-[#a16207]',
            str_contains($normalized, 'batal') => 'border-[#fecaca] bg-[#fee2e2] text-[#b91c1c]',
            str_contains($normalized, 'disewa'), str_contains($normalized, 'aktif') => 'border-[#bbf7d0] bg-[#dcfce7] text-[#15803d]',
            str_contains($normalized, 'habis') => 'border-[#fecaca] bg-[#fee2e2] text-[#b91c1c]',
            default => 'border-[#d1d5db] bg-[#f3f4f6] text-[#374151]',
        };
    }

    protected function smoothPath(array $points): string
    {
        $count = count($points);

        if ($count === 0) {
            return '';
        }

        $path = 'M '.$points[0]['x'].','.$points[0]['y'];

        for ($index = 0; $index < $count - 1; $index++) {
            $previous = $points[max(0, $index - 1)];
            $current = $points[$index];
            $next = $points[$index + 1];
            $after = $points[min($count - 1, $index + 2)];

            $controlOneX = $current['x'] + ($next['x'] - $previous['x']) / 6;
            $controlOneY = $current['y'] + ($next['y'] - $previous['y']) / 6;
            $controlTwoX = $next['x'] - ($after['x'] - $current['x']) / 6;
            $controlTwoY = $next['y'] - ($after['y'] - $current['y']) / 6;

            $path .= ' C '.round($controlOneX, 2).','.round($controlOneY, 2)
                .' '.round($controlTwoX, 2).','.round($controlTwoY, 2)
                .' '.$next['x'].','.$next['y'];
        }

        return $path;
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

    <aside class="fixed inset-y-0 left-0 z-50 flex h-screen w-[280px] shrink-0 transform flex-col justify-between border-r border-neutral-200 bg-white px-6 py-7 transition-transform duration-200 md:sticky md:top-0 md:z-30 md:translate-x-0 lg:w-[320px] lg:px-8 {{ $sidebarOpen ? 'translate-x-0' : '-translate-x-full' }}">
        <div>
            <div class="mb-10 flex items-center justify-between">
                <button type="button" wire:click="showSection('ringkasan')" class="flex items-center gap-3 text-left">
                    <span class="flex h-9 w-9 items-center justify-center rounded-sm bg-black text-white shadow-xs">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                            <circle cx="12" cy="13" r="3" stroke-width="2" />
                        </svg>
                    </span>
                    <span>
                        <span class="block text-sm font-bold leading-none tracking-tight text-black">MIRRORCAM</span>
                        <span class="mt-1 block text-[8px] font-semibold uppercase leading-none tracking-[0.18em] text-neutral-400">MULTI-TENANT RENTAL</span>
                    </span>
                </button>

                <button type="button" wire:click="toggleSidebar" class="rounded-md p-1 text-neutral-500 hover:bg-neutral-100 md:hidden">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="mb-3">
                <span class="text-[11px] font-semibold uppercase tracking-[0.12em] text-neutral-400">NAVIGASI UTAMA</span>
            </div>

            <nav class="space-y-2.5">
                <button type="button" wire:click="showSection('ringkasan')" class="flex w-full items-center gap-3 rounded-lg border px-4 py-3 text-left text-[13px] font-semibold transition {{ $section === 'ringkasan' ? 'border-black bg-black text-white shadow-xs' : 'border-neutral-800 bg-white text-black hover:bg-neutral-50' }}">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 4h6v6H4V4zm10 0h6v6h-6V4zM4 14h6v6H4v-6zm10 0h6v6h-6v-6z" />
                    </svg>
                    <span>Ringkasan &amp; Dashboard</span>
                </button>

                <button type="button" wire:click="showSection('produk')" class="flex w-full items-center gap-3 rounded-lg border px-4 py-3 text-left text-[13px] font-semibold transition {{ $section === 'produk' ? 'border-black bg-black text-white shadow-xs' : 'border-neutral-800 bg-white text-black hover:bg-neutral-50' }}">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                        <circle cx="12" cy="13" r="3" stroke-width="1.8" />
                    </svg>
                    <span>Kelola Produk</span>
                </button>

                <button type="button" wire:click="showSection('pesanan')" class="flex w-full items-center gap-3 rounded-lg border px-4 py-3 text-left text-[13px] font-semibold transition {{ $section === 'pesanan' ? 'border-black bg-black text-white shadow-xs' : 'border-neutral-800 bg-white text-black hover:bg-neutral-50' }}">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2M9 12h6M9 16h6" />
                    </svg>
                    <span>Kelola Pesanan Sewa</span>
                </button>

                <button type="button" wire:click="showSection('profil')" class="flex w-full items-center gap-3 rounded-lg border px-4 py-3 text-left text-[13px] font-semibold transition {{ $section === 'profil' ? 'border-black bg-black text-white shadow-xs' : 'border-neutral-800 bg-white text-black hover:bg-neutral-50' }}">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 10l1.5-5.5A2 2 0 016.43 3h11.14a2 2 0 011.93 1.5L21 10M3 10v9a2 2 0 002 2h14a2 2 0 002-2v-9M3 10h18M8 14h.01M12 14h.01M16 14h.01" />
                    </svg>
                    <span>Pengaturan Profile</span>
                </button>

                <button type="button" wire:click="showSection('keamanan')" class="flex w-full items-center gap-3 rounded-lg border px-4 py-3 text-left text-[13px] font-semibold transition {{ $section === 'keamanan' ? 'border-black bg-black text-white shadow-xs' : 'border-neutral-800 bg-white text-black hover:bg-neutral-50' }}">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 3l7 3v6c0 4.2-2.9 7.4-7 9-4.1-1.6-7-4.8-7-9V6l7-3z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9.5 12.5l1.8 1.8 3.4-4" />
                    </svg>
                    <span>Keamanan &amp; Password</span>
                </button>

                <button type="button" wire:click="showSection('komisi')" class="flex w-full items-center gap-3 rounded-lg border px-4 py-3 text-left text-[13px] font-semibold transition {{ $section === 'komisi' ? 'border-black bg-black text-white shadow-xs' : 'border-neutral-800 bg-white text-black hover:bg-neutral-50' }}">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <rect x="3" y="6" width="18" height="13" rx="2" stroke-width="1.8" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 10h18M16 14.5h2" />
                    </svg>
                    <span>Pembayaran Komisi</span>
                </button>
            </nav>
        </div>

        <div class="mt-8 border-t border-neutral-100 pt-4">
            <div class="flex items-center justify-between rounded-xl border border-neutral-200 bg-white p-3 shadow-[0_1px_2px_rgba(0,0,0,0.04)]">
                <div class="flex min-w-0 items-center gap-3">
                    @if ($tenant['logo'])
                        <img src="{{ $tenant['logo'] }}" alt="{{ $tenant['nama_toko'] }}" class="h-10 w-10 shrink-0 rounded-full border border-neutral-200 object-cover">
                    @else
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#d9d9d9] text-xs font-bold text-black">
                            {{ $tenant['inisial'] }}
                        </div>
                    @endif

                    <div class="min-w-0">
                        <div class="truncate text-xs font-bold leading-tight text-black">{{ $tenant['nama_toko'] }}</div>
                        <div class="mt-1 text-[10px] font-medium leading-tight text-neutral-400">Admin</div>
                    </div>
                </div>

                <form method="POST" action="{{ route('logout') }}" class="shrink-0">
                    @csrf
                    <button type="submit" title="Keluar" class="rounded-md p-1.5 text-neutral-400 transition hover:bg-neutral-100 hover:text-black">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <div class="flex min-h-screen min-w-0 flex-1 flex-col">
        <header class="sticky top-0 z-30 flex h-16 items-center justify-between border-b border-neutral-200 bg-[#f9f9f9]/95 px-5 backdrop-blur md:justify-end md:px-8">
            <button type="button" wire:click="toggleSidebar" class="rounded-md p-1.5 text-neutral-600 hover:bg-neutral-200 md:hidden">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>

            <a href="{{ route('home') }}" class="flex items-center gap-2 text-xs font-semibold text-neutral-700 transition hover:text-black md:text-sm">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 21h18M5 21V7l7-4 7 4v14M9 9h.01M15 9h.01M9 13h.01M15 13h.01M9 17h.01M15 17h.01" />
                </svg>
                <span>Kembali ke Beranda</span>
            </a>
        </header>

        <main class="w-full flex-1 px-5 py-6 md:px-8 md:py-7">
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
                    <section class="rounded-xl border border-neutral-200 bg-white p-6 shadow-[0_1px_2px_rgba(0,0,0,0.02)] md:p-7">
                        <div class="flex flex-col justify-between gap-5 lg:flex-row lg:items-center">
                            <div>
                                <h1 class="text-[25px] font-bold leading-tight tracking-tight text-black md:text-[28px]">{{ $hasTenant ? 'Halo, Pemilik '.$tenant['nama_toko'] : 'Halo, Pemilik Toko' }}</h1>
                                <p class="mt-3 max-w-2xl text-xs leading-relaxed text-neutral-500 md:text-sm">
                                    @if ($hasTenant)
                                        Ringkasan operasional harian, katalog inventaris gear, serta pemantauan komisi platform Anda di {{ $tenant['alamat'] }}.
                                    @else
                                        Lengkapi profil tenant Anda untuk mulai mengelola katalog gear dan memantau pendapatan.
                                    @endif
                                </p>
                            </div>

                            <div class="flex flex-col gap-3 sm:flex-row">
                                <button type="button" wire:click="exportCsv" wire:loading.attr="disabled" class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md bg-[#e9e9e9] px-5 py-3 text-xs font-semibold text-black transition hover:bg-[#dedede] disabled:opacity-60">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 3v12m0 0l-4-4m4 4l4-4M5 21h14" />
                                    </svg>
                                    <span>Unduh Rekap Bulanan</span>
                                </button>

                                <button type="button" wire:click="openProductModal" class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md bg-black px-5 py-3 text-xs font-semibold text-white transition hover:bg-neutral-800">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m6-6H6" />
                                    </svg>
                                    <span>Tambah Produk</span>
                                </button>
                            </div>
                        </div>
                    </section>

                    <section class="mt-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                        <div class="rounded-lg border border-neutral-200 bg-white p-5 shadow-[0_1px_2px_rgba(0,0,0,0.02)] md:p-6">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.1em] text-neutral-500">TOTAL PRODUK</p>
                            <div class="mt-6 flex items-baseline gap-2">
                                <span class="text-[42px] font-normal leading-none tracking-tight text-black">{{ number_format($stats['total_produk'], 0, ',', '.') }}</span>
                                <span class="text-sm text-neutral-500">Unit</span>
                            </div>
                        </div>

                        <div class="rounded-lg border border-neutral-200 bg-white p-5 shadow-[0_1px_2px_rgba(0,0,0,0.02)] md:p-6">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.1em] text-neutral-500">PENYEWAAN BERJALAN</p>
                            <div class="mt-6 flex items-baseline gap-2">
                                <span class="text-[42px] font-normal leading-none tracking-tight text-black">{{ str_pad((string) $stats['penyewaan_berjalan'], 2, '0', STR_PAD_LEFT) }}</span>
                                <span class="text-sm text-neutral-500">Booking</span>
                            </div>
                        </div>

                        <div class="rounded-lg border border-neutral-200 bg-white p-5 shadow-[0_1px_2px_rgba(0,0,0,0.02)] md:p-6">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.1em] text-neutral-500">PENDAPATAN TENANT</p>
                            <div class="mt-6 flex flex-wrap items-baseline gap-x-3 gap-y-1">
                                <span class="text-[29px] font-normal leading-none tracking-tight text-black md:text-[32px]">{{ $this->rupiah($stats['pendapatan_tenant']) }}</span>
                                <span class="text-xs font-medium {{ $growth >= 0 ? 'text-[#16a34a]' : 'text-[#dc2626]' }}">{{ $growthLabel }}</span>
                            </div>
                        </div>
                    </section>

                    <section class="mt-5 overflow-hidden rounded-xl border border-neutral-200 bg-white shadow-[0_1px_2px_rgba(0,0,0,0.02)]">
                        <div class="flex flex-col gap-4 border-b border-neutral-200 px-5 py-5 sm:flex-row sm:items-center sm:justify-between md:px-6">
                            <div class="flex items-center gap-3">
                                <svg class="h-5 w-5 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 20V10m5 10V4m5 16v-7m5 7V8" />
                                </svg>
                                <h2 class="text-base font-bold tracking-tight text-black md:text-lg">Tren &amp; Analisis Pendapatan Sewa</h2>
                            </div>

                            <button type="button" wire:click="exportCsv" wire:loading.attr="disabled" class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md bg-[#e9e9e9] px-4 py-2.5 text-[11px] font-semibold text-black transition hover:bg-[#dedede] disabled:opacity-60">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 3v12m0 0l-4-4m4 4l4-4M5 21h14" />
                                </svg>
                                <span>Unduh Laporan (CSV/PDF)</span>
                            </button>
                        </div>

                        <div class="p-5 md:p-6">
                            <div class="grid gap-4 md:grid-cols-3">
                                <div class="rounded-md bg-[#f1f1f1] px-5 py-4">
                                    <p class="text-[10px] font-semibold uppercase tracking-[0.08em] text-neutral-500">PENDAPATAN TENANT</p>
                                    <p class="mt-4 text-xl font-bold tracking-tight text-black">{{ $this->rupiah($stats['pendapatan_tenant']) }}</p>
                                </div>

                                <div class="rounded-md bg-[#f1f1f1] px-5 py-4">
                                    <p class="text-[10px] font-semibold uppercase tracking-[0.08em] text-neutral-500">PENDAPATAN RATA-RATA HARIAN</p>
                                    <p class="mt-4 text-xl font-bold tracking-tight text-black">{{ $this->rupiah($stats['pendapatan_harian_rata_rata']) }}</p>
                                </div>

                                <div class="rounded-md bg-[#f1f1f1] px-5 py-4">
                                    <p class="text-[10px] font-semibold uppercase tracking-[0.08em] text-neutral-500">TOTAL UNIT TERSEWA</p>
                                    <p class="mt-4 text-xl font-bold tracking-tight text-black">{{ number_format($stats['total_unit_tersewa'], 0, ',', '.') }} Unit</p>
                                </div>
                            </div>

                            <div class="mt-6 grid gap-5 xl:grid-cols-[minmax(0,1fr)_320px]">
                                <div class="min-w-0">
                                    @if ($chartHasData)
                                    <svg viewBox="0 0 640 250" class="h-auto w-full" role="img" aria-label="Grafik tren pendapatan sewa">
                                        <defs>
                                            <linearGradient id="revenueAreaGradient" x1="0" y1="0" x2="0" y2="1">
                                                <stop offset="0%" stop-color="#bfbfbf" stop-opacity="0.72" />
                                                <stop offset="100%" stop-color="#f3f3f3" stop-opacity="0.12" />
                                            </linearGradient>
                                        </defs>

                                        @foreach ($chart['grid'] as $line)
                                            <line x1="{{ $chart['plot']['left'] }}" y1="{{ $line['y'] }}" x2="{{ $chart['plot']['right'] }}" y2="{{ $line['y'] }}" stroke="#dedede" stroke-width="1" stroke-dasharray="2 5" />
                                            <text x="{{ $chart['plot']['left'] - 8 }}" y="{{ $line['y'] + 3 }}" text-anchor="end" font-size="9" fill="#9ca3af">{{ $line['label'] }}</text>
                                        @endforeach

                                        <path d="{{ $chart['area_path'] }}" fill="url(#revenueAreaGradient)" />
                                        <path d="{{ $chart['line_path'] }}" fill="none" stroke="#111111" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />

                                        @foreach ($chart['points'] as $point)
                                            <text x="{{ $point['x'] }}" y="{{ $chart['plot']['bottom'] + 19 }}" text-anchor="middle" font-size="9" fill="#9ca3af">{{ $point['label'] }}</text>
                                        @endforeach

                                        <circle cx="{{ $chart['last']['x'] }}" cy="{{ $chart['last']['y'] }}" r="3.2" fill="#111111" />

                                        <g>
                                            <rect x="{{ $chart['tooltip']['x'] }}" y="{{ $chart['tooltip']['y'] }}" width="{{ $chart['tooltip']['w'] }}" height="20" rx="3" fill="#111111" />
                                            <text x="{{ $chart['tooltip']['x'] + ($chart['tooltip']['w'] / 2) }}" y="{{ $chart['tooltip']['y'] + 13.5 }}" text-anchor="middle" font-size="9" font-weight="600" fill="#ffffff">{{ $chart['tooltip']['label'] }}</text>
                                        </g>
                                    </svg>
                                    @else
                                    <div class="flex h-[250px] w-full items-center justify-center rounded-md border border-dashed border-neutral-200 bg-[#fafafa]">
                                        <div class="text-center">
                                            <p class="text-xs font-semibold text-neutral-500">Belum ada data pendapatan</p>
                                            <p class="mt-1 text-[10px] text-neutral-400">Data akan muncul setelah ada transaksi penyewaan.</p>
                                        </div>
                                    </div>
                                    @endif
                                </div>

                                <aside class="rounded-lg border border-neutral-200 bg-white p-4">
                                    <div class="flex items-start gap-2.5">
                                        <span class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-md bg-[#f1f1f1] text-black">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 21V10l8-6 8 6v11M9 21v-6h6v6M3 10l1-4h16l1 4" />
                                            </svg>
                                        </span>
                                        <div>
                                            <h3 class="text-xs font-bold leading-snug text-black">Pengaturan Cepat Ketersediaan Toko</h3>
                                            <p class="mt-1 text-[10px] leading-relaxed text-neutral-500">Sinkronisasi status pemesanan instan pada sistem</p>
                                        </div>
                                    </div>

                                    <div class="mt-4 space-y-2.5">
                                        <div class="flex items-center justify-between gap-3 rounded-md bg-[#f1f1f1] px-3 py-2.5">
                                            <div class="min-w-0">
                                                <p class="text-[11px] font-bold text-black">Auto-Accept Booking Standby</p>
                                                <p class="mt-0.5 text-[9px] leading-relaxed text-neutral-500">Otomatis setujui sewa jika unit berstatus ready</p>
                                            </div>
                                            <button type="button" wire:click="toggleAutoAccept" class="relative inline-flex h-5 w-9 shrink-0 items-center rounded-full transition {{ $autoAcceptBooking ? 'bg-black' : 'bg-neutral-300' }}">
                                                <span class="inline-block h-4 w-4 rounded-full bg-white shadow-xs transition {{ $autoAcceptBooking ? 'translate-x-[18px]' : 'translate-x-0.5' }}"></span>
                                            </button>
                                        </div>

                                        <div class="flex items-center justify-between gap-3 rounded-md bg-[#f1f1f1] px-3 py-2.5">
                                            <div class="min-w-0">
                                                <p class="text-[11px] font-bold text-black">Mode Libur / Maintenance Toko</p>
                                                <p class="mt-0.5 text-[9px] leading-relaxed text-neutral-500">Tutup toko sementara</p>
                                            </div>
                                            <button type="button" wire:click="toggleMaintenance" class="relative inline-flex h-5 w-9 shrink-0 items-center rounded-full transition {{ $maintenanceMode ? 'bg-black' : 'bg-neutral-300' }}">
                                                <span class="inline-block h-4 w-4 rounded-full bg-white shadow-xs transition {{ $maintenanceMode ? 'translate-x-[18px]' : 'translate-x-0.5' }}"></span>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="mt-4 flex items-center justify-between gap-3">
                                        <span class="flex items-center gap-1.5 text-[9px] font-medium text-black">
                                            <span class="h-1.5 w-1.5 rounded-full bg-black"></span>
                                            Sinkronisasi &middot; Real-time
                                        </span>
                                        <button type="button" wire:click="savePreferences" class="rounded-md bg-[#e9e9e9] px-3 py-2 text-[10px] font-semibold text-black transition hover:bg-[#dedede]">Simpan Preferensi</button>
                                    </div>
                                </aside>
                            </div>
                        </div>
                    </section>
                @elseif ($section === 'produk')
                    <section class="overflow-hidden rounded-xl border border-neutral-200 bg-white shadow-[0_1px_2px_rgba(0,0,0,0.02)]">
                        <div class="flex flex-col gap-4 border-b border-neutral-200 px-5 py-5 sm:flex-row sm:items-center sm:justify-between md:px-6">
                            <div>
                                <h1 class="text-lg font-bold tracking-tight text-black">Kelola Produk</h1>
                                <p class="mt-1 text-xs text-neutral-500">Pantau katalog inventaris gear dan ketersediaan unit Anda.</p>
                            </div>
                            <button type="button" wire:click="openProductModal" class="inline-flex items-center justify-center gap-2 rounded-md bg-black px-4 py-2.5 text-xs font-semibold text-white transition hover:bg-neutral-800">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m6-6H6" />
                                </svg>
                                <span>Tambah Produk</span>
                            </button>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full min-w-[680px] text-left text-xs">
                                <thead class="bg-neutral-50 text-[10px] uppercase tracking-wider text-neutral-500">
                                    <tr>
                                        <th class="px-6 py-3 font-semibold">Produk</th>
                                        <th class="px-6 py-3 font-semibold">Kategori</th>
                                        <th class="px-6 py-3 font-semibold">Harga Sewa</th>
                                        <th class="px-6 py-3 font-semibold">Stok</th>
                                        <th class="px-6 py-3 font-semibold">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-neutral-100">
                                    @forelse ($dashboard['products'] as $product)
                                        <tr class="transition hover:bg-neutral-50">
                                            <td class="px-6 py-4 font-semibold text-black">{{ $product['nama'] }}</td>
                                            <td class="px-6 py-4 text-neutral-500">{{ $product['kategori'] }}</td>
                                            <td class="px-6 py-4 font-medium text-black">{{ $this->rupiah($product['harga']) }}</td>
                                            <td class="px-6 py-4 text-neutral-500">{{ $product['stok'] }} unit</td>
                                            <td class="px-6 py-4">
                                                <span class="inline-flex rounded-full border px-2.5 py-1 text-[10px] font-semibold {{ $this->statusBadgeClasses($product['status']) }}">{{ $product['status'] }}</span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="px-6 py-12 text-center text-xs text-neutral-500">Belum ada produk pada katalog Anda.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </section>
                @elseif ($section === 'pesanan')
                    <section class="overflow-hidden rounded-xl border border-neutral-200 bg-white shadow-[0_1px_2px_rgba(0,0,0,0.02)]">
                        <div class="border-b border-neutral-200 px-5 py-5 md:px-6">
                            <h1 class="text-lg font-bold tracking-tight text-black">Kelola Pesanan Sewa</h1>
                            <p class="mt-1 text-xs text-neutral-500">Daftar pesanan terbaru beserta status penyewaan unit.</p>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full min-w-[860px] text-left text-xs">
                                <thead class="bg-neutral-50 text-[10px] uppercase tracking-wider text-neutral-500">
                                    <tr>
                                        <th class="px-6 py-3 font-semibold">Kode</th>
                                        <th class="px-6 py-3 font-semibold">Produk</th>
                                        <th class="px-6 py-3 font-semibold">Penyewa</th>
                                        <th class="px-6 py-3 font-semibold">Periode</th>
                                        <th class="px-6 py-3 font-semibold">Status</th>
                                        <th class="px-6 py-3 font-semibold">Total</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-neutral-100">
                                    @forelse ($dashboard['bookings'] as $booking)
                                        <tr class="transition hover:bg-neutral-50">
                                            <td class="px-6 py-4 font-semibold text-black">{{ $booking['kode'] }}</td>
                                            <td class="px-6 py-4 text-neutral-600">{{ $booking['produk'] }}</td>
                                            <td class="px-6 py-4 text-neutral-600">{{ $booking['penyewa'] }}</td>
                                            <td class="px-6 py-4 text-neutral-500">{{ $booking['mulai'] }} - {{ $booking['selesai'] }}</td>
                                            <td class="px-6 py-4">
                                                <span class="inline-flex rounded-full border px-2.5 py-1 text-[10px] font-semibold {{ $this->statusBadgeClasses($booking['status']) }}">{{ $booking['status'] }}</span>
                                            </td>
                                            <td class="px-6 py-4 font-medium text-black">{{ $this->rupiah($booking['total']) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-6 py-12 text-center text-xs text-neutral-500">Belum ada pesanan sewa.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </section>
                @elseif ($section === 'profil')
                    <section class="grid gap-5 lg:grid-cols-[1.25fr_0.75fr]">
                        <div class="rounded-xl border border-neutral-200 bg-white p-6 shadow-[0_1px_2px_rgba(0,0,0,0.02)]">
                            <h1 class="text-lg font-bold tracking-tight text-black">Pengaturan Profile</h1>
                            <p class="mt-1 text-xs text-neutral-500">Informasi resmi tenant yang tampil pada halaman publik MirrorCam.</p>

                            <dl class="mt-6 grid gap-4 sm:grid-cols-2">
                                <div>
                                    <dt class="text-[10px] font-semibold uppercase tracking-wider text-neutral-400">Nama Toko</dt>
                                    <dd class="mt-1.5 text-sm font-semibold text-black">{{ $tenant['nama_toko'] }}</dd>
                                </div>
                                <div>
                                    <dt class="text-[10px] font-semibold uppercase tracking-wider text-neutral-400">Penanggung Jawab</dt>
                                    <dd class="mt-1.5 text-sm font-semibold text-black">{{ $tenant['pemilik'] }}</dd>
                                </div>
                                <div>
                                    <dt class="text-[10px] font-semibold uppercase tracking-wider text-neutral-400">Email Resmi</dt>
                                    <dd class="mt-1.5 text-sm text-neutral-700">{{ $tenant['email'] }}</dd>
                                </div>
                                <div>
                                    <dt class="text-[10px] font-semibold uppercase tracking-wider text-neutral-400">WhatsApp</dt>
                                    <dd class="mt-1.5 text-sm text-neutral-700">{{ $tenant['whatsapp'] }}</dd>
                                </div>
                                <div class="sm:col-span-2">
                                    <dt class="text-[10px] font-semibold uppercase tracking-wider text-neutral-400">Alamat Toko</dt>
                                    <dd class="mt-1.5 text-sm text-neutral-700">{{ $tenant['alamat'] }}</dd>
                                </div>
                            </dl>
                        </div>

                        <div class="rounded-xl border border-neutral-200 bg-white p-6 shadow-[0_1px_2px_rgba(0,0,0,0.02)]">
                            <h2 class="text-sm font-bold text-black">Status Kemitraan</h2>
                            <div class="mt-4 flex items-center gap-3 rounded-lg bg-[#f1f1f1] p-4">
                                <span class="flex h-10 w-10 items-center justify-center rounded-full bg-black text-white">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 13l4 4L19 7" />
                                    </svg>
                                </span>
                                <div>
                                    <p class="text-xs font-bold text-black">{{ $tenant['status'] }}</p>
                                    <p class="mt-0.5 text-[10px] text-neutral-500">Tenant aktif di platform MirrorCam</p>
                                </div>
                            </div>
                            <p class="mt-4 text-[11px] leading-relaxed text-neutral-500">Hubungi admin MirrorCam jika ingin memperbarui data legalitas atau rekening settlement tenant.</p>
                        </div>
                    </section>
                @elseif ($section === 'keamanan')
                    <section class="grid gap-5 lg:grid-cols-[1.25fr_0.75fr]">
                        <div class="rounded-xl border border-neutral-200 bg-white p-6 shadow-[0_1px_2px_rgba(0,0,0,0.02)]">
                            <h1 class="text-lg font-bold tracking-tight text-black">Keamanan &amp; Password</h1>
                            <p class="mt-1 text-xs text-neutral-500">Akun tenant Anda terhubung melalui autentikasi Google.</p>

                            <div class="mt-6 flex items-start gap-4 rounded-lg border border-neutral-200 p-4">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#f1f1f1]">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 3l7 3v6c0 4.2-2.9 7.4-7 9-4.1-1.6-7-4.8-7-9V6l7-3z" />
                                    </svg>
                                </span>
                                <div>
                                    <p class="text-sm font-bold text-black">Login dengan Google</p>
                                    <p class="mt-1 text-xs leading-relaxed text-neutral-500">Password dan verifikasi dua langkah dikelola langsung oleh akun Google {{ $tenant['email'] }}.</p>
                                </div>
                            </div>

                            <a href="https://myaccount.google.com/security" target="_blank" rel="noopener" class="mt-5 inline-flex items-center gap-2 rounded-md bg-black px-4 py-2.5 text-xs font-semibold text-white transition hover:bg-neutral-800">
                                Kelola Keamanan Google
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M14 5h5v5M19 5l-8 8M19 14v5H5V5h5" />
                                </svg>
                            </a>
                        </div>

                        <div class="rounded-xl border border-neutral-200 bg-white p-6 shadow-[0_1px_2px_rgba(0,0,0,0.02)]">
                            <h2 class="text-sm font-bold text-black">Sesi Aktif</h2>
                            <div class="mt-4 rounded-lg bg-[#f1f1f1] p-4">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-semibold text-black">Perangkat ini</span>
                                    <span class="rounded-full bg-[#dcfce7] px-2 py-0.5 text-[9px] font-bold text-[#15803d]">Aktif</span>
                                </div>
                                <p class="mt-2 text-[10px] text-neutral-500">Sesi login saat ini tersinkronisasi secara real-time.</p>
                            </div>
                            <form method="POST" action="{{ route('logout') }}" class="mt-4">
                                @csrf
                                <button type="submit" class="w-full rounded-md border border-neutral-300 bg-white px-4 py-2.5 text-xs font-semibold text-black transition hover:bg-neutral-50">Keluar dari Perangkat Ini</button>
                            </form>
                        </div>
                    </section>
                @elseif ($section === 'komisi')
                    <section class="space-y-5">
                        <div class="grid gap-4 md:grid-cols-3">
                            <div class="rounded-lg border border-neutral-200 bg-white p-5 shadow-[0_1px_2px_rgba(0,0,0,0.02)]">
                                <p class="text-[10px] font-semibold uppercase tracking-[0.1em] text-neutral-500">PENDAPATAN KOTOR</p>
                                <p class="mt-4 text-xl font-bold tracking-tight text-black">{{ $this->rupiah($stats['pendapatan_kotor']) }}</p>
                            </div>
                            <div class="rounded-lg border border-neutral-200 bg-white p-5 shadow-[0_1px_2px_rgba(0,0,0,0.02)]">
                                <p class="text-[10px] font-semibold uppercase tracking-[0.1em] text-neutral-500">KOMISI PLATFORM</p>
                                <p class="mt-4 text-xl font-bold tracking-tight text-black">{{ $this->rupiah($stats['komisi_platform']) }}</p>
                            </div>
                            <div class="rounded-lg border border-neutral-200 bg-white p-5 shadow-[0_1px_2px_rgba(0,0,0,0.02)]">
                                <p class="text-[10px] font-semibold uppercase tracking-[0.1em] text-neutral-500">PENDAPATAN BERSIH</p>
                                <p class="mt-4 text-xl font-bold tracking-tight text-black">{{ $this->rupiah($stats['pendapatan_tenant']) }}</p>
                            </div>
                        </div>

                        <div class="overflow-hidden rounded-xl border border-neutral-200 bg-white shadow-[0_1px_2px_rgba(0,0,0,0.02)]">
                            <div class="border-b border-neutral-200 px-5 py-5 md:px-6">
                                <h1 class="text-lg font-bold tracking-tight text-black">Pembayaran Komisi</h1>
                                <p class="mt-1 text-xs text-neutral-500">Rincian komisi platform dari transaksi penyewaan tenant.</p>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="w-full min-w-[680px] text-left text-xs">
                                    <thead class="bg-neutral-50 text-[10px] uppercase tracking-wider text-neutral-500">
                                        <tr>
                                            <th class="px-6 py-3 font-semibold">Kode</th>
                                            <th class="px-6 py-3 font-semibold">Tanggal</th>
                                            <th class="px-6 py-3 font-semibold">Nilai Transaksi</th>
                                            <th class="px-6 py-3 font-semibold">Persentase</th>
                                            <th class="px-6 py-3 font-semibold">Komisi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-neutral-100">
                                        @forelse ($dashboard['commissions'] as $commission)
                                            <tr class="transition hover:bg-neutral-50">
                                                <td class="px-6 py-4 font-semibold text-black">{{ $commission['kode'] }}</td>
                                                <td class="px-6 py-4 text-neutral-500">{{ $commission['tanggal'] }}</td>
                                                <td class="px-6 py-4 text-neutral-600">{{ $this->rupiah($commission['pendapatan']) }}</td>
                                                <td class="px-6 py-4 text-neutral-500">{{ rtrim(rtrim(number_format($commission['persen'], 2, ',', '.'), '0'), ',') }}%</td>
                                                <td class="px-6 py-4 font-medium text-black">{{ $this->rupiah($commission['komisi']) }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="px-6 py-12 text-center text-xs text-neutral-500">Belum ada data komisi platform.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </section>
                @endif
            </div>
        </main>
    </div>

    @if ($showProductModal)
        <div class="fixed inset-0 z-[60] flex items-center justify-center bg-black/50 px-4 py-6" wire:click.self="closeProductModal">
            <div class="w-full max-w-lg overflow-hidden rounded-xl bg-white shadow-xl">
                <div class="flex items-start justify-between border-b border-neutral-200 px-6 py-4">
                    <div>
                        <h3 class="text-base font-bold text-black">Tambah Produk Baru</h3>
                        <p class="mt-1 text-[11px] text-neutral-500">Lengkapi data inventaris gear yang akan disewakan.</p>
                    </div>
                    <button type="button" wire:click="closeProductModal" class="rounded-md p-1 text-neutral-500 transition hover:bg-neutral-100 hover:text-black">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form wire:submit="saveProduct" class="space-y-4 px-6 py-5">
                    <div>
                        <label class="mb-1.5 block text-[11px] font-semibold text-neutral-700">Nama Produk</label>
                        <input type="text" wire:model="nama_produk" placeholder="Contoh: Sony FX3 Cinema Line" class="w-full rounded-md border border-neutral-300 bg-white px-3 py-2.5 text-xs text-black outline-none transition focus:border-black focus:ring-1 focus:ring-black">
                        @error('nama_produk') <p class="mt-1 text-[10px] text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-[11px] font-semibold text-neutral-700">Kategori</label>
                            <select wire:model="id_kategori" class="w-full rounded-md border border-neutral-300 bg-white px-3 py-2.5 text-xs text-black outline-none transition focus:border-black focus:ring-1 focus:ring-black">
                                <option value="">Pilih kategori</option>
                                @foreach ($this->categories as $category)
                                    <option value="{{ $category->id_kategori }}">{{ $category->nama_kategori }}</option>
                                @endforeach
                            </select>
                            @error('id_kategori') <p class="mt-1 text-[10px] text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-1.5 block text-[11px] font-semibold text-neutral-700">Harga Sewa / Hari</label>
                            <input type="number" min="0" wire:model="harga_sewa" placeholder="850000" class="w-full rounded-md border border-neutral-300 bg-white px-3 py-2.5 text-xs text-black outline-none transition focus:border-black focus:ring-1 focus:ring-black">
                            @error('harga_sewa') <p class="mt-1 text-[10px] text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-[11px] font-semibold text-neutral-700">Total Stok Unit</label>
                        <input type="number" min="0" wire:model="total_stok_unit" class="w-full rounded-md border border-neutral-300 bg-white px-3 py-2.5 text-xs text-black outline-none transition focus:border-black focus:ring-1 focus:ring-black">
                        @error('total_stok_unit') <p class="mt-1 text-[10px] text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-1.5 block text-[11px] font-semibold text-neutral-700">Deskripsi Lengkap</label>
                        <textarea rows="3" wire:model="deskripsi_lengkap" placeholder="Spesifikasi singkat dan kelengkapan unit" class="w-full resize-none rounded-md border border-neutral-300 bg-white px-3 py-2.5 text-xs text-black outline-none transition focus:border-black focus:ring-1 focus:ring-black"></textarea>
                        @error('deskripsi_lengkap') <p class="mt-1 text-[10px] text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex items-center justify-end gap-3 border-t border-neutral-100 pt-4">
                        <button type="button" wire:click="closeProductModal" class="rounded-md border border-neutral-300 bg-white px-4 py-2.5 text-xs font-semibold text-black transition hover:bg-neutral-50">Batal</button>
                        <button type="submit" wire:loading.attr="disabled" class="rounded-md bg-black px-4 py-2.5 text-xs font-semibold text-white transition hover:bg-neutral-800 disabled:opacity-60">Simpan Produk</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
