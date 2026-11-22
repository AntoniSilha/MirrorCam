<?php

use App\Models\DurasiProduk;
use App\Models\GaleriFotoProduk;
use App\Models\KategoriProduk;
use App\Models\Produk;
use App\Models\Tenant;
use App\Modules\TenantManagement\UseCases\GetTenantDashboardMetricsUseCase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Symfony\Component\HttpFoundation\StreamedResponse;

new #[Title('Dashboard Mitra - MirrorCam')] #[Layout('components.layouts.mitra')] class extends Component
{
    use WithFileUploads;

    public string $section = 'ringkasan';

    public bool $sidebarOpen = false;

    public bool $showProductModal = false;

    public bool $autoAcceptBooking = true;

    public bool $maintenanceMode = false;

    public string $nama_produk = '';

    public string $id_kategori = '';

    public string $harga_sewa = '';

    public string $total_stock_unit = '1';

    public string $deskripsi_lengkap = '';

    /** @var array<int, string> */
    public array $color_produk = [];

    /** @var array<int, string> */
    public array $durasi_terpilih = ['1_hari'];

    /** @var array<string, string> */
    public array $harga_durasi = [];

    /** @var array<int, \Livewire\Features\SupportFileUploads\TemporaryUploadedFile> */
    public array $fotos = [];

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
        $this->autoAcceptBooking = (bool) session('mitra.auto_accept_booking', true);
        $this->maintenanceMode = (bool) session('mitra.maintenance_mode', false);
    }

    #[Computed]
    public function dashboard(): array
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        return app(GetTenantDashboardMetricsUseCase::class)->execute($user, $this->productSearch, $this->bookingSearch);
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

    /**
     * @return array<int, string>
     */
    public function colorOptions(): array
    {
        return ['Black', 'Grey', 'Red', 'White', 'Silver'];
    }

    /**
     * @return array<string, string>
     */
    public function durasiOptions(): array
    {
        return [
            '6_jam' => '6 Jam',
            '12_jam' => '12 Jam',
            '1_hari' => '1 Hari',
            '3_hari' => '3 Hari',
            '7_hari' => '7 Hari',
            '15_hari' => '15 Hari',
            '1_bulan' => '1 Bulan',
            '2_bulan' => '2 Bulan',
            '3_bulan' => '3 Bulan',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function emptyHargaDurasi(): array
    {
        return array_fill_keys(array_keys($this->durasiOptions()), '');
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
        $this->total_stock_unit = '1';
        $this->deskripsi_lengkap = '';
        $this->color_produk = [];
        $this->durasi_terpilih = ['1_hari'];
        $this->harga_durasi = $this->emptyHargaDurasi();
        $this->fotos = [];
        $this->showProductModal = true;
    }

    public function removeFoto(int $index): void
    {
        unset($this->fotos[$index]);
        $this->fotos = array_values($this->fotos);
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

        $rules = [
            'nama_produk' => ['required', 'string', 'max:120'],
            'id_kategori' => ['required', 'exists:kategori_produk,id_kategori'],
            'harga_sewa' => ['required', 'numeric', 'min:0'],
            'total_stock_unit' => ['required', 'integer', 'min:0'],
            'deskripsi_lengkap' => ['nullable', 'string', 'max:1000'],
            'color_produk' => ['array'],
            'color_produk.*' => ['string', 'max:30'],
            'durasi_terpilih' => ['required', 'array', 'min:1'],
            'durasi_terpilih.*' => ['string', Rule::in(array_keys($this->durasiOptions()))],
            'harga_durasi' => ['array'],
            'fotos' => ['array', 'max:8'],
            'fotos.*' => ['image', 'max:4096'],
        ];

        foreach ($this->durasi_terpilih as $kodeDurasi) {
            $rules["harga_durasi.{$kodeDurasi}"] = ['required', 'numeric', 'min:0'];
        }

        $validated = $this->validate($rules, [
            'durasi_terpilih.required' => 'Pilih minimal satu durasi sewa.',
            'durasi_terpilih.min' => 'Pilih minimal satu durasi sewa.',
            'harga_durasi.*.required' => 'Harga untuk durasi yang dipilih wajib diisi.',
            'harga_durasi.*.numeric' => 'Harga durasi harus berupa angka.',
            'harga_durasi.*.min' => 'Harga durasi tidak boleh negatif.',
        ]);

        DB::transaction(function () use ($tenant, $validated): void {
            $productData = [
                'id_tenant' => $tenant->id_tenant,
                'id_kategori' => (int) $validated['id_kategori'],
                'nama_produk' => $validated['nama_produk'],
                'color_produk' => empty($validated['color_produk']) ? null : implode(', ', $validated['color_produk']),
                'deskripsi_lengkap' => $validated['deskripsi_lengkap'] ?: '-',
                'harga_sewa' => $validated['harga_sewa'],
                'total_stock_unit' => (int) $validated['total_stock_unit'],
            ];

            if (\Illuminate\Support\Facades\Schema::hasColumn('produks', 'total_stok_unit')) {
                $productData['total_stok_unit'] = (int) $validated['total_stock_unit'];
            }

            $produk = Produk::query()->create($productData);

            foreach ($validated['durasi_terpilih'] as $kodeDurasi) {
                DurasiProduk::query()->create([
                    'id_produk' => $produk->id_produk,
                    'durasi' => $kodeDurasi,
                    'nama_durasi' => $this->durasiOptions()[$kodeDurasi],
                    'harga' => (float) $validated['harga_durasi'][$kodeDurasi],
                ]);
            }

            foreach ($this->fotos as $foto) {
                $path = $foto->store('produk', 'public');

                if (! $path) {
                    continue;
                }

                GaleriFotoProduk::query()->create([
                    'id_produk' => $produk->id_produk,
                    'url_foto_produk' => Storage::disk('public')->url($path),
                ]);
            }
        });

        unset($this->dashboard, $this->chart);

        $this->showProductModal = false;
        $this->resetValidation();
        $this->nama_produk = '';
        $this->id_kategori = '';
        $this->harga_sewa = '';
        $this->total_stock_unit = '1';
        $this->deskripsi_lengkap = '';
        $this->color_produk = [];
        $this->durasi_terpilih = ['1_hari'];
        $this->harga_durasi = [];
        $this->fotos = [];

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
                'catatan' => $this->catatanSerahTerima,
            ]);

            unset($this->dashboard, $this->chart);
            $this->fotoKondisiAwal = null;
            session()->flash('status', 'Tahap 2 Berhasil: Barang telah diserahterimakan & status berubah menjadi Sedang Disewa.');
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
                    <button type="submit" title="" class="rounded-md p-1.5 text-neutral-400 transition hover:bg-neutral-100 hover:text-black">
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

                            <div class="flex flex-col gap-2.5 sm:flex-row sm:items-center">
                                <div class="relative w-full sm:w-64">
                                    <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-neutral-400">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 21l-4.35-4.35M17 10.5a6.5 6.5 0 11-13 0 6.5 6.5 0 0113 0z" />
                                        </svg>
                                    </span>
                                    <input type="search" wire:model.live.debounce.300ms="productSearch" autocomplete="off" placeholder="Cari produk, kategori, status..." class="w-full rounded-md border border-neutral-300 bg-white py-2.5 pl-9 pr-3 text-xs text-black outline-none transition placeholder:text-neutral-400 focus:border-black focus:ring-1 focus:ring-black">
                                </div>

                                <button type="button" wire:click="openProductModal" class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md bg-black px-4 py-2.5 text-xs font-semibold text-white transition hover:bg-neutral-800">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m6-6H6" />
                                    </svg>
                                    <span>Tambah Produk</span>
                                </button>
                            </div>
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
                                            <td class="px-6 py-4">
                                                <div class="flex items-center gap-3">
                                                    <img src="{{ $product['foto'] }}" alt="{{ $product['nama'] }}" loading="lazy" class="h-10 w-10 shrink-0 rounded-md border border-neutral-200 object-cover" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1516035069371-29a1b244cc32?auto=format&fit=crop&w=200&q=80';">
                                                    <span class="font-semibold text-black">{{ $product['nama'] }}</span>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 text-neutral-500">{{ $product['kategori'] }}</td>
                                            <td class="px-6 py-4 font-medium text-black">{{ $this->rupiah($product['harga']) }}</td>
                                            <td class="px-6 py-4 text-neutral-500">{{ $product['stok'] }} unit</td>
                                            <td class="px-6 py-4">
                                                <span class="inline-flex rounded-full border px-2.5 py-1 text-[10px] font-semibold {{ $this->statusBadgeClasses($product['status']) }}">{{ $product['status'] }}</span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="px-6 py-12 text-center text-xs text-neutral-500">
                                                @if (trim($productSearch) !== '')
                                                    Tidak ada produk yang cocok dengan pencarian "{{ $productSearch }}".
                                                @else
                                                    Belum ada produk pada katalog Anda.
                                                @endif
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </section>
                @elseif ($section === 'pesanan')
                    <section class="overflow-hidden rounded-[4px] border border-neutral-200 bg-[#ffffff] shadow-[0_1px_2px_rgba(0,0,0,0.02)] font-['Montserrat',sans-serif]">
                        <div class="flex flex-col gap-4 border-b border-neutral-200 px-5 py-5 sm:flex-row sm:items-center sm:justify-between md:px-6">
                            <div>
                                <h1 class="text-lg font-bold tracking-tight text-[#000000]">Kelola Pesanan Sewa</h1>
                                <p class="mt-1 text-xs text-neutral-500">Daftar pesanan terbaru beserta status penyewaan unit & alur checklist operasional.</p>
                            </div>

                            <div class="relative w-full sm:w-72">
                                <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-neutral-400">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 21l-4.35-4.35M17 10.5a6.5 6.5 0 11-13 0 6.5 6.5 0 0113 0z" />
                                    </svg>
                                </span>
                                <input type="search" wire:model.live.debounce.300ms="bookingSearch" autocomplete="off" placeholder="Cari kode, produk, penyewa, status..." class="w-full rounded-[4px] border border-neutral-300 bg-white py-2.5 pl-9 pr-3 text-xs text-[#000000] outline-none transition placeholder:text-neutral-400 focus:border-black focus:ring-1 focus:ring-black">
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full min-w-[960px] text-left text-xs">
                                <thead class="bg-[#f9f9f9] text-[10px] uppercase tracking-wider text-neutral-500 font-semibold">
                                    <tr>
                                        <th class="px-5 py-3">Kode</th>
                                        <th class="px-5 py-3">Produk</th>
                                        <th class="px-5 py-3">Penyewa</th>
                                        <th class="px-5 py-3">Periode & Tenggat</th>
                                        <th class="px-5 py-3">Status Sewa</th>
                                        <th class="px-5 py-3">Pembayaran</th>
                                        <th class="px-5 py-3">Total</th>
                                        <th class="px-5 py-3 text-right">Alur Operasional</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-neutral-100">
                                    @forelse ($dashboard['bookings'] as $booking)
                                        <tr class="transition hover:bg-[#f9f9f9]">
                                            <td class="px-5 py-4 font-bold text-[#000000]">
                                                <span>{{ $booking['kode'] }}</span>
                                                @if (! empty($booking['is_overdue']))
                                                    <span class="mt-0.5 block text-[10px] font-extrabold text-[#ba1a1a]">TERLAMBAT</span>
                                                @endif
                                            </td>
                                            <td class="px-5 py-4 font-medium text-neutral-800">{{ $booking['produk'] }}</td>
                                            <td class="px-5 py-4 text-neutral-700">
                                                <div class="font-semibold text-black">{{ $booking['penyewa'] }}</div>
                                                <div class="text-[10px] text-neutral-400">NIK: {{ $booking['nik_ktp'] }}</div>
                                            </td>
                                            <td class="px-5 py-4">
                                                <div class="text-neutral-600">{{ $booking['mulai'] }} - {{ $booking['selesai'] }}</div>
                                                @if (! empty($booking['is_in_use']))
                                                    <div class="mt-1 text-[11px] font-semibold text-[#B45309]">
                                                        Tenggat: {{ $booking['return_deadline'] }}
                                                        @if (! empty($booking['is_overdue']))
                                                            <span class="font-bold text-[#ba1a1a]">(Lewat Tenggat)</span>
                                                        @endif
                                                    </div>
                                                @elseif (! empty($booking['actual_returned_at']))
                                                    <div class="mt-1 text-[10px] text-neutral-400">
                                                        Kembali: {{ $booking['actual_returned_at'] }}
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="px-5 py-4">
                                                <span class="inline-flex items-center rounded-[4px] border px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider {{ $this->statusBadgeClasses($booking['status']) }}">
                                                    {{ $booking['status'] }}
                                                </span>
                                            </td>
                                            <td class="px-5 py-4">
                                                @if (! empty($booking['is_payment_verified']))
                                                    <span class="inline-flex items-center rounded-[4px] border border-[#fff700] bg-[#fff700] px-2 py-0.5 text-[10px] font-bold text-[#000000]">
                                                        LUNAS
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center rounded-[4px] border border-[#ffdad6] bg-[#ffdad6] px-2 py-0.5 text-[10px] font-bold text-[#ba1a1a]">
                                                        {{ strtoupper($booking['status_harga']) }} (Sisa: {{ $this->rupiah($booking['sisa_tagihan']) }})
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-5 py-4 font-bold text-[#000000]">{{ $this->rupiah($booking['total']) }}</td>
                                            <td class="px-5 py-4 text-right">
                                                <button type="button" wire:click="openChecklistModal({{ $booking['id'] }})" class="inline-flex items-center gap-1.5 rounded-[4px] bg-[#000000] px-3 py-1.5 text-[11px] font-bold text-[#ffffff] shadow-xs transition hover:bg-neutral-800">
                                                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2M9 14l2 2 4-4" />
                                                    </svg>
                                                    <span>Checklist Alur</span>
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="px-6 py-12 text-center text-xs text-neutral-500">
                                                @if (trim($bookingSearch) !== '')
                                                    Tidak ada pesanan yang cocok dengan pencarian "{{ $bookingSearch }}".
                                                @else
                                                    Belum ada pesanan sewa.
                                                @endif
                                            </td>
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
                            <form action="{{ route('logout') }}" method="POST" class="inline">
    @csrf
    <button type="submit" class="text-xs text-gray-600 hover:text-black font-semibold cursor-pointer">
        Keluar
    </button>
</form>
                        </div>
                    </section>
                @elseif ($section === 'komisi')
                    @php
                        $billing = $dashboard['commission_billing'];
                        $bank = config('mirrorcam.platform_bank');
                    @endphp

                    <section class="space-y-5">
                        <div class="rounded-xl border border-neutral-200 bg-white p-6 shadow-[0_1px_2px_rgba(0,0,0,0.02)] md:p-7">
                            <h1 class="text-[25px] font-bold leading-tight tracking-tight text-black md:text-[28px]">Pembayaran Komisi</h1>
                            <p class="mt-2 text-xs leading-relaxed text-neutral-500 md:text-sm">Kelola tanggungan komisi {{ $tenant['nama_toko'] }} kepada PT. MirrorCam.</p>
                        </div>

                        <div class="rounded-xl border border-neutral-200 bg-white p-6 shadow-[0_1px_2px_rgba(0,0,0,0.02)] md:p-7">
                            <h2 class="text-base font-bold tracking-tight text-black md:text-lg">Tanggungan Komisi ke MirrorCam</h2>

                            <div class="mt-5 flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
                                <div>
                                    <p class="text-[10px] font-semibold uppercase tracking-[0.1em] text-neutral-500">JUMLAH TANGGUNGAN</p>
                                    @if ($billing['has_bill'])
                                        <p class="mt-2 text-[30px] font-bold leading-tight tracking-tight text-black md:text-[36px]">{{ $this->rupiah($billing['amount']) }}</p>
                                    @else
                                        <p class="mt-2 text-[30px] font-bold leading-tight tracking-tight text-neutral-700 md:text-[36px]">Belum tersedia</p>
                                    @endif
                                </div>

                                @if ($billing['has_bill'])
                                    <a href="#qris-pembayaran" class="inline-flex items-center justify-center whitespace-nowrap rounded-md bg-[#e9e9e9] px-5 py-3 text-xs font-semibold text-black transition hover:bg-[#dedede]">
                                        Bayar Sekarang
                                    </a>
                                @else
                                    <button type="button" disabled class="inline-flex cursor-not-allowed items-center justify-center whitespace-nowrap rounded-md bg-[#e9e9e9] px-5 py-3 text-xs font-semibold text-neutral-400">
                                        Bayar Sekarang
                                    </button>
                                @endif
                            </div>

                            <div class="mt-6 grid gap-4 border-t border-neutral-200 pt-5 sm:grid-cols-3">
                                <div>
                                    <p class="text-[10px] font-semibold uppercase tracking-[0.1em] text-neutral-500">PENYEDIA LAYANAN</p>
                                    <p class="mt-1.5 text-sm font-semibold text-black">PT. MirrorCam</p>
                                </div>
                                <div>
                                    <p class="text-[10px] font-semibold uppercase tracking-[0.1em] text-neutral-500">PERIODE TAGIHAN</p>
                                    <p class="mt-1.5 text-sm font-semibold text-black">{{ $billing['periode'] ?? 'Belum tersedia' }}</p>
                                </div>
                                <div>
                                    <p class="text-[10px] font-semibold uppercase tracking-[0.1em] text-neutral-500">JATUH TEMPO</p>
                                    <p class="mt-1.5 text-sm font-semibold text-black">{{ $billing['jatuh_tempo'] ?? 'Belum tersedia' }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="grid gap-5 lg:grid-cols-2">
                            <div id="qris-pembayaran" class="scroll-mt-24 rounded-xl border border-neutral-200 bg-white p-6 shadow-[0_1px_2px_rgba(0,0,0,0.02)] md:p-7">
                                <div class="flex items-start justify-between gap-3">
                                    <h2 class="text-base font-bold tracking-tight text-black md:text-lg">Pembayaran via QRIS</h2>
                                    <span class="text-sm font-bold tracking-wide text-black">QRIS</span>
                                </div>
                                <p class="mt-3 text-xs leading-relaxed text-neutral-500 md:text-sm">Bayar komisi dengan memindai QRIS melalui aplikasi bank atau dompet digital yang mendukung QRIS.</p>

                                <div class="mt-5 rounded-lg border border-neutral-200 bg-[#f4f4f5] px-5 py-6">
                                    @if ($billing['has_bill'] && $billing['qris_data_uri'])
                                        <div class="flex flex-col items-center gap-3">
                                            <img src="{{ $billing['qris_data_uri'] }}" alt="QRIS Pembayaran Komisi" class="h-44 w-44 rounded-md bg-white p-2 shadow-xs">
                                            <p class="text-sm font-bold text-black">QRIS siap dipindai</p>
                                            <p class="text-center text-[11px] leading-relaxed text-neutral-500">
                                                Nominal {{ $this->rupiah($billing['amount']) }} &middot; {{ $billing['periode'] }}<br>
                                                Ref. {{ $billing['reference'] }}
                                            </p>
                                        </div>
                                    @else
                                        <div class="flex flex-col items-center gap-3 py-3 text-center">
                                            <span class="flex h-11 w-11 items-center justify-center rounded-lg bg-[#e9e9e9] text-neutral-500">
                                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M4 4h6v6H4V4zm10 0h6v6h-6V4zM4 14h6v6H4v-6zM14 14h2v2h-2v-2zm4 0h2v2h-2v-2zm-4 4h2v2h-2v-2zm4 0h2v2h-2v-2z" />
                                                </svg>
                                            </span>
                                            <p class="text-sm font-bold text-black">QRIS belum tersedia</p>
                                            <p class="text-[11px] leading-relaxed text-neutral-500">QRIS tersedia setelah tagihan diterbitkan.<br>Belum ada kode pembayaran yang dapat dipindai.</p>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="rounded-xl border border-neutral-200 bg-white p-6 shadow-[0_1px_2px_rgba(0,0,0,0.02)] md:p-7">
                                <div class="flex items-center gap-2.5">
                                    <span class="flex h-6 w-6 items-center justify-center text-black">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M3 21h18M5 21V9l7-5 7 5v12M9 21v-6h6v6M3 9h18" />
                                        </svg>
                                    </span>
                                    <h2 class="text-base font-bold tracking-tight text-black md:text-lg">Informasi Rekening Tujuan</h2>
                                </div>
                                <p class="mt-3 text-xs leading-relaxed text-neutral-500 md:text-sm">Informasi rekening penyedia layanan untuk pembayaran komisi kepada PT. MirrorCam.</p>

                                <dl class="mt-4 divide-y divide-neutral-200">
                                    <div class="flex items-center justify-between gap-4 py-3.5">
                                        <dt class="text-xs text-neutral-500 md:text-sm">Atas nama</dt>
                                        <dd class="text-right text-xs font-semibold text-black md:text-sm">{{ $bank['atas_nama'] }}</dd>
                                    </div>
                                    <div class="flex items-center justify-between gap-4 py-3.5">
                                        <dt class="text-xs text-neutral-500 md:text-sm">Bank</dt>
                                        <dd class="text-right text-xs font-semibold text-black md:text-sm">{{ $bank['nama_bank'] }}</dd>
                                    </div>
                                    <div class="flex items-center justify-between gap-4 py-3.5">
                                        <dt class="text-xs text-neutral-500 md:text-sm">Nomor rekening</dt>
                                        <dd class="text-right text-xs font-semibold text-black md:text-sm">{{ $bank['nomor_rekening'] }}</dd>
                                    </div>
                                </dl>
                            </div>
                        </div>

                        <div class="rounded-xl border border-neutral-200 bg-white p-6 shadow-[0_1px_2px_rgba(0,0,0,0.02)] md:p-7">
                            <h2 class="text-base font-bold tracking-tight text-black md:text-lg">Cara Membayar Komisi</h2>

                            <div class="mt-5 grid gap-5 sm:grid-cols-3">
                                <div class="flex items-start gap-3">
                                    <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-md bg-[#f1f1f1] text-[11px] font-bold text-black">1</span>
                                    <div>
                                        <p class="text-xs font-bold text-black md:text-sm">Periksa tagihan</p>
                                        <p class="mt-1 text-[11px] leading-relaxed text-neutral-500">Setelah diterbitkan, cek jumlah tanggungan, periode, dan jatuh tempo.</p>
                                    </div>
                                </div>
                                <div class="flex items-start gap-3">
                                    <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-md bg-[#f1f1f1] text-[11px] font-bold text-black">2</span>
                                    <div>
                                        <p class="text-xs font-bold text-black md:text-sm">Pilih Bayar Sekarang</p>
                                        <p class="mt-1 text-[11px] leading-relaxed text-neutral-500">QRIS pembayaran akan tersedia setelah tagihan diterbitkan.</p>
                                    </div>
                                </div>
                                <div class="flex items-start gap-3">
                                    <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-md bg-[#f1f1f1] text-[11px] font-bold text-black">3</span>
                                    <div>
                                        <p class="text-xs font-bold text-black md:text-sm">Pindai dan bayar</p>
                                        <p class="mt-1 text-[11px] leading-relaxed text-neutral-500">Periksa penerima serta nominal di aplikasi, lalu simpan bukti pembayaran.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                @endif
            </div>
        </main>
    </div>

    @if ($showProductModal)
        <div class="fixed inset-0 z-[60] flex items-center justify-center bg-black/50 px-4 py-6" wire:click.self="closeProductModal">
            <div class="flex max-h-[92vh] w-full max-w-2xl flex-col overflow-hidden rounded-xl bg-white shadow-xl">
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

                <form wire:submit="saveProduct" class="max-h-[calc(92vh-73px)] space-y-4 overflow-y-auto px-6 py-5">
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

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-[11px] font-semibold text-neutral-700">Total Stok Unit</label>
                            <input type="number" min="0" wire:model="total_stock_unit" class="w-full rounded-md border border-neutral-300 bg-white px-3 py-2.5 text-xs text-black outline-none transition focus:border-black focus:ring-1 focus:ring-black">
                            @error('total_stock_unit') <p class="mt-1 text-[10px] text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-1.5 block text-[11px] font-semibold text-neutral-700">Galeri Foto</label>
                            <input type="file" wire:model="fotos" multiple accept="image/png,image/jpeg,image/webp" class="block w-full cursor-pointer text-[11px] text-neutral-600 file:mr-3 file:cursor-pointer file:rounded-md file:border-0 file:bg-black file:px-3 file:py-2 file:text-[11px] file:font-semibold file:text-white hover:file:bg-neutral-800">
                            <p class="mt-1 text-[10px] text-neutral-400">JPG/PNG/WEBP, maks. 4MB per foto (maks. 8 foto).</p>
                            @error('fotos.*') <p class="mt-1 text-[10px] text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div wire:loading wire:target="fotos" class="text-[10px] font-semibold text-neutral-500">Mengunggah foto...</div>

                    @if (! empty($fotos))
                        <div class="grid grid-cols-3 gap-2 sm:grid-cols-4">
                            @foreach ($fotos as $index => $foto)
                                <div wire:key="foto-{{ $index }}" class="group relative aspect-square overflow-hidden rounded-md border border-neutral-200 bg-neutral-50">
                                    @if ($foto)
                                        <img src="{{ $foto->temporaryUrl() }}" alt="Pratinjau foto {{ $index + 1 }}" class="h-full w-full object-cover">
                                    @endif
                                    <button type="button" wire:click="removeFoto({{ $index }})" title="Hapus foto" class="absolute right-1 top-1 flex h-5 w-5 items-center justify-center rounded-full bg-black/70 text-[10px] font-bold text-white transition hover:bg-black">
                                        &times;
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <div>
                        <label class="mb-1.5 block text-[11px] font-semibold text-neutral-700">Warna Tersedia</label>
                        @php
                            $colorSwatches = [
                                'Black' => '#000000',
                                'Grey' => '#9ca3af',
                                'Red' => '#ef4444',
                                'White' => '#ffffff',
                                'Silver' => '#d1d5db',
                            ];
                        @endphp
                        <div class="flex flex-wrap gap-2">
                            @foreach ($this->colorOptions() as $color)
                                <label class="cursor-pointer">
                                    <input type="checkbox" wire:model="color_produk" value="{{ $color }}" class="peer sr-only">
                                    <span class="inline-flex items-center gap-1.5 rounded-md border border-neutral-300 bg-white px-3 py-1.5 text-[11px] font-semibold text-neutral-700 transition peer-checked:border-black peer-checked:bg-black peer-checked:text-white">
                                        <span class="h-2.5 w-2.5 rounded-full border border-neutral-300" style="background-color: {{ $colorSwatches[$color] ?? '#000000' }}"></span>
                                        {{ $color }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        @error('color_produk.*') <p class="mt-1 text-[10px] text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-1.5 block text-[11px] font-semibold text-neutral-700">Pilihan Durasi & Harga Sewa</label>
                        <p class="mb-2 text-[10px] text-neutral-400">Centang durasi yang tersedia, lalu isi harga sewanya.</p>
                        <div class="space-y-2">
                            @foreach ($this->durasiOptions() as $value => $label)
                                <div class="flex flex-col gap-2 rounded-md border border-neutral-200 px-3 py-2 sm:flex-row sm:items-center sm:justify-between">
                                    <label class="flex cursor-pointer items-center gap-2 text-[11px] font-semibold text-neutral-700">
                                        <input type="checkbox" wire:model="durasi_terpilih" value="{{ $value }}" class="h-4 w-4 rounded border-neutral-300 text-black focus:ring-black">
                                        {{ $label }}
                                    </label>
                                    <div class="relative w-full sm:w-48">
                                        <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-[11px] text-neutral-400">Rp</span>
                                        <input type="number" min="0" wire:model="harga_durasi.{{ $value }}" placeholder="Harga sewa" class="w-full rounded-md border border-neutral-300 bg-white py-2 pl-9 pr-3 text-xs text-black outline-none transition placeholder:text-neutral-400 focus:border-black focus:ring-1 focus:ring-black">
                                    </div>
                                </div>
                                @error("harga_durasi.$value") <p class="text-[10px] text-red-600">{{ $message }}</p> @enderror
                            @endforeach
                        </div>
                        @error('durasi_terpilih') <p class="mt-1 text-[10px] text-red-600">{{ $message }}</p> @enderror
                        @error('durasi_terpilih.*') <p class="mt-1 text-[10px] text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-1.5 block text-[11px] font-semibold text-neutral-700">Deskripsi Lengkap</label>
                        <textarea rows="3" wire:model="deskripsi_lengkap" placeholder="Spesifikasi singkat dan kelengkapan unit" class="w-full resize-none rounded-md border border-neutral-300 bg-white px-3 py-2.5 text-xs text-black outline-none transition focus:border-black focus:ring-1 focus:ring-black"></textarea>
                        @error('deskripsi_lengkap') <p class="mt-1 text-[10px] text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex items-center justify-end gap-3 border-t border-neutral-100 pt-4">
                        <button type="button" wire:click="closeProductModal" class="rounded-md border border-neutral-300 bg-white px-4 py-2.5 text-xs font-semibold text-black transition hover:bg-neutral-50">Batal</button>
                        <button type="submit" wire:loading.attr="disabled" wire:target="saveProduct, fotos" class="rounded-md bg-black px-4 py-2.5 text-xs font-semibold text-white transition hover:bg-neutral-800 disabled:opacity-60">Simpan Produk</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- MODAL CHECKLIST ALUR OPERASIONAL PESANAN (SRS ALIGNED) --}}
    @if ($showChecklistModal && $this->selectedBooking)
        @php
            $active = $this->selectedBooking;
            $stage1Done = ! empty($active['is_payment_verified']);
            $stage2Done = ! empty($active['is_in_use']) || ! empty($active['is_completed']) || ! empty($active['is_disputed']);
            $stage3Done = ! empty($active['is_completed']) || ! empty($active['is_disputed']);
        @endphp
        <div class="fixed inset-0 z-[70] flex items-center justify-center overflow-y-auto bg-black/60 p-4 font-['Montserrat',sans-serif]" wire:click.self="closeChecklistModal">
            <div class="relative w-full max-w-3xl overflow-hidden rounded-[4px] border border-neutral-200 bg-[#ffffff] shadow-2xl">
                <!-- Modal Header -->
                <div class="flex items-start justify-between border-b border-neutral-200 bg-[#ffffff] px-6 py-5">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="rounded-[4px] bg-black px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-white">Checklist Operasional Pesanan</span>
                            <span class="text-xs font-bold text-neutral-500">Kode: {{ $active['kode'] }}</span>
                        </div>
                        <h2 class="mt-1 text-base font-bold text-[#000000]">{{ $active['produk'] }}</h2>
                        <p class="text-xs text-neutral-500">Penyewa: <span class="font-bold text-black">{{ $active['penyewa'] }}</span> (NIK: {{ $active['nik_ktp'] }}) • Periode: {{ $active['mulai'] }} s/d {{ $active['selesai'] }}</p>
                    </div>
                    <button type="button" wire:click="closeChecklistModal" class="rounded-[4px] p-1.5 text-neutral-400 hover:bg-neutral-100 hover:text-black">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="max-h-[75vh] space-y-6 overflow-y-auto p-6">
                    @if (session()->has('status'))
                        <div class="rounded-[4px] border border-[#dcfce7] bg-[#dcfce7] px-4 py-3 text-xs font-bold text-[#16a34a]">
                            {{ session('status') }}
                        </div>
                    @endif

                    @error('checklist_payment')
                        <div class="rounded-[4px] border border-[#ffdad6] bg-[#ffdad6] px-4 py-3 text-xs font-bold text-[#ba1a1a]">
                            {{ $message }}
                        </div>
                    @enderror
                    @error('checklist_handover')
                        <div class="rounded-[4px] border border-[#ffdad6] bg-[#ffdad6] px-4 py-3 text-xs font-bold text-[#ba1a1a]">
                            {{ $message }}
                        </div>
                    @enderror
                    @error('checklist_return')
                        <div class="rounded-[4px] border border-[#ffdad6] bg-[#ffdad6] px-4 py-3 text-xs font-bold text-[#ba1a1a]">
                            {{ $message }}
                        </div>
                    @enderror

                    <!-- Stepper Bar (Urutan Transisi Status Bertahap) -->
                    <div class="grid grid-cols-3 gap-2">
                        <div class="rounded-[4px] p-3 text-center border {{ $stage1Done ? 'bg-[#fff700]/20 border-[#fff700] text-black' : 'bg-[#f9f9f9] border-neutral-200 text-neutral-400' }}">
                            <div class="text-[10px] font-bold uppercase tracking-wider">Tahap 1</div>
                            <div class="text-xs font-bold {{ $stage1Done ? 'text-black' : 'text-neutral-500' }}">Pembayaran</div>
                            <span class="mt-1 inline-block text-[10px] font-semibold {{ $stage1Done ? 'text-black font-bold' : 'text-neutral-400' }}">
                                {{ $stage1Done ? '✓ Lunas' : 'Menunggu Pelunasan' }}
                            </span>
                        </div>
                        <div class="rounded-[4px] p-3 text-center border {{ $stage2Done ? 'bg-[#dcfce7] border-[#bbf7d0] text-[#16a34a]' : ($stage1Done ? 'bg-[#f9f9f9] border-black text-black' : 'bg-[#f9f9f9] border-neutral-200 text-neutral-400') }}">
                            <div class="text-[10px] font-bold uppercase tracking-wider">Tahap 2</div>
                            <div class="text-xs font-bold {{ $stage2Done ? 'text-[#16a34a]' : ($stage1Done ? 'text-black' : 'text-neutral-500') }}">Serah Terima</div>
                            <span class="mt-1 inline-block text-[10px] font-semibold {{ $stage2Done ? 'text-[#16a34a]' : 'text-neutral-400' }}">
                                {{ $stage2Done ? '✓ Sedang Disewa' : ($stage1Done ? 'Siap Diproses' : 'Terkunci') }}
                            </span>
                        </div>
                        <div class="rounded-[4px] p-3 text-center border {{ $stage3Done ? ($active['is_disputed'] ? 'bg-[#ffdad6] border-[#fca5a5] text-[#ba1a1a]' : 'bg-[#fff700] border-[#facc15] text-black') : ($active['is_in_use'] ? 'bg-[#f9f9f9] border-black text-black' : 'bg-[#f9f9f9] border-neutral-200 text-neutral-400') }}">
                            <div class="text-[10px] font-bold uppercase tracking-wider">Tahap 3</div>
                            <div class="text-xs font-bold {{ $stage3Done ? ($active['is_disputed'] ? 'text-[#ba1a1a]' : 'text-black') : 'text-neutral-500' }}">Pengembalian</div>
                            <span class="mt-1 inline-block text-[10px] font-semibold {{ $stage3Done ? ($active['is_disputed'] ? 'text-[#ba1a1a]' : 'text-black') : 'text-neutral-400' }}">
                                {{ $stage3Done ? ($active['is_disputed'] ? '⚠ Sengketa' : '✓ Selesai') : ($active['is_in_use'] ? 'Siap Kembali' : 'Terkunci') }}
                            </span>
                        </div>
                    </div>

                    <!-- TAHAP 1 BOX: CEKLIST PEMBAYARAN SELESAI -->
                    <div class="rounded-[4px] border border-neutral-200 bg-[#f9f9f9] p-5">
                        <div class="flex items-center justify-between border-b border-neutral-200 pb-3">
                            <div class="flex items-center gap-2">
                                <span class="flex h-5 w-5 items-center justify-center rounded-[4px] bg-black text-[10px] font-bold text-white">1</span>
                                <h3 class="text-sm font-bold text-[#000000]">Tahap 1: Ceklist Pembayaran Selesai (SRS UC-08 & FR-PAY-02)</h3>
                            </div>
                            @if ($stage1Done)
                                <span class="rounded-[4px] border border-[#fff700] bg-[#fff700] px-2.5 py-1 text-[10px] font-bold text-[#000000]">
                                    ✓ Lunas & Terverifikasi
                                </span>
                            @else
                                <span class="rounded-[4px] border border-[#ffdad6] bg-[#ffdad6] px-2.5 py-1 text-[10px] font-bold text-[#ba1a1a]">
                                    Belum Lunas
                                </span>
                            @endif
                        </div>

                        <div class="mt-4 grid gap-3 sm:grid-cols-3">
                            <div class="rounded-[4px] border border-neutral-200 bg-[#ffffff] p-3">
                                <span class="text-[10px] font-semibold uppercase text-neutral-400">Total Sewa</span>
                                <div class="mt-1 text-sm font-bold text-black">{{ $this->rupiah($active['total']) }}</div>
                            </div>
                            <div class="rounded-[4px] border border-neutral-200 bg-[#ffffff] p-3">
                                <span class="text-[10px] font-semibold uppercase text-neutral-400">Nominal DP Terbayar</span>
                                <div class="mt-1 text-sm font-bold text-black">{{ $this->rupiah($active['nominal_dp']) }}</div>
                            </div>
                            <div class="rounded-[4px] border border-neutral-200 bg-[#ffffff] p-3">
                                <span class="text-[10px] font-semibold uppercase text-neutral-400">Sisa Tagihan</span>
                                <div class="mt-1 text-sm font-bold {{ $active['sisa_tagihan'] > 0 ? 'text-[#ba1a1a]' : 'text-[#16a34a]' }}">
                                    {{ $this->rupiah($active['sisa_tagihan']) }}
                                </div>
                            </div>
                        </div>

                        @if ($stage1Done)
                            <div class="mt-4 rounded-[4px] bg-white p-3 text-xs text-neutral-600 border border-neutral-200">
                                <span class="font-bold text-black">Status Pembayaran Terverifikasi:</span> Seluruh biaya sewa sebesar {{ $this->rupiah($active['total']) }} telah berstatus lunas. Anda dapat melanjutkan serah terima barang pada Tahap 2.
                            </div>
                        @else
                            <div class="mt-4 space-y-3 rounded-[4px] bg-white p-4 border border-neutral-200">
                                <p class="text-xs text-neutral-600">
                                    Pelanggan menggunakan skema pembayaran DP. Sisa tagihan sebesar <strong class="text-black">{{ $this->rupiah($active['sisa_tagihan']) }}</strong> wajib dilunasi saat pengambilan barang di tenant (SRS UC-08).
                                </p>
                                <div>
                                    <label class="block text-[11px] font-semibold text-neutral-600">Catatan Pelunasan Sisa Tagihan (Opsional)</label>
                                    <input type="text" wire:model="catatanPembayaran" placeholder="Contoh: Diterima tunai / transfer pelunasan saat pengambilan unit" class="mt-1 w-full rounded-[4px] border border-neutral-300 bg-[#ffffff] px-3 py-2 text-xs text-[#000000] outline-none focus:border-black">
                                </div>
                                <button type="button" wire:click="verifyPayment({{ $active['id'] }})" class="rounded-[4px] bg-[#000000] px-4 py-2 text-xs font-bold text-[#ffffff] hover:bg-neutral-800 transition">
                                    Ceklist Pembayaran Selesai (Lunas)
                                </button>
                            </div>
                        @endif
                    </div>

                    <!-- TAHAP 2 BOX: CEKLIST BARANG DIGUNAKAN / SERAH TERIMA -->
                    <div class="rounded-[4px] border border-neutral-200 bg-[#f9f9f9] p-5">
                        <div class="flex items-center justify-between border-b border-neutral-200 pb-3">
                            <div class="flex items-center gap-2">
                                <span class="flex h-5 w-5 items-center justify-center rounded-[4px] bg-black text-[10px] font-bold text-white">2</span>
                                <h3 class="text-sm font-bold text-[#000000]">Tahap 2: Ceklist Barang Digunakan / Serah Terima (SRS FR-TEN-03 & UC-10)</h3>
                            </div>
                            @if ($stage2Done)
                                <span class="rounded-[4px] border border-[#bbf7d0] bg-[#dcfce7] px-2.5 py-1 text-[10px] font-bold text-[#16a34a]">
                                    ✓ Sedang Disewa
                                </span>
                            @elseif (! $stage1Done)
                                <span class="rounded-[4px] border border-[#ffdad6] bg-[#ffdad6] px-2.5 py-1 text-[10px] font-bold text-[#ba1a1a]">
                                    Terkunci
                                </span>
                            @else
                                <span class="rounded-[4px] border border-neutral-300 bg-[#d9d9d9] px-2.5 py-1 text-[10px] font-bold text-[#000000]">
                                    Menunggu Serah Terima
                                </span>
                            @endif
                        </div>

                        @if (! $stage1Done)
                            <div class="mt-4 rounded-[4px] border border-[#ffdad6] bg-[#ffdad6] p-3 text-xs font-semibold text-[#ba1a1a]">
                                🔒 <strong>Guard Aturan:</strong> Tahap 2 tidak dapat diselesaikan apabila status pembayaran belum lunas. Selesaikan Tahap 1 terlebih dahulu.
                            </div>
                        @elseif ($stage2Done)
                            <div class="mt-4 space-y-3 rounded-[4px] bg-[#ffffff] p-4 border border-neutral-200 text-xs">
                                <div class="grid gap-3 sm:grid-cols-2">
                                    <div>
                                        <span class="text-[10px] font-semibold uppercase text-neutral-400">Waktu Serah Terima Aktual</span>
                                        <div class="mt-1 font-semibold text-black">{{ $active['actual_handover_at'] ?? '-' }}</div>
                                    </div>
                                    <div>
                                        <span class="text-[10px] font-semibold uppercase text-neutral-400">Tenggat Waktu Pengembalian</span>
                                        <div class="mt-1 font-bold text-[#B45309]">{{ $active['return_deadline'] ?? '-' }}</div>
                                    </div>
                                </div>
                                @if ($active['foto_kondisi_awal'])
                                    <div>
                                        <span class="text-[10px] font-semibold uppercase text-neutral-400">Dokumentasi Kondisi Awal Unit</span>
                                        <div class="mt-1.5">
                                            <a href="{{ $active['foto_kondisi_awal'] }}" target="_blank" class="inline-block">
                                                <img src="{{ $active['foto_kondisi_awal'] }}" alt="Kondisi Awal" class="h-20 w-28 rounded-[4px] object-cover border border-neutral-300">
                                            </a>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @else
                            <div class="mt-4 space-y-4 rounded-[4px] bg-[#ffffff] p-4 border border-neutral-200 text-xs">
                                <!-- Checkbox 1: Identitas KTP -->
                                <div class="space-y-2 rounded-[4px] bg-[#f9f9f9] p-3 border border-neutral-200">
                                    <label class="flex items-start gap-2.5 cursor-pointer">
                                        <input type="checkbox" wire:model="checklistIdentitasOk" class="mt-0.5 rounded-[4px] border-neutral-300 text-black focus:ring-black">
                                        <span class="font-bold text-black">
                                            1. Identitas asli pelanggan cocok dengan data KTP terverifikasi pada sistem
                                        </span>
                                    </label>
                                    @error('checklistIdentitasOk') <span class="block text-[11px] font-semibold text-[#ba1a1a]">{{ $message }}</span> @enderror
                                    <div class="ml-6 flex items-center gap-4 text-neutral-500">
                                        <span>NIK Terdaftar: <strong class="text-black">{{ $active['nik_ktp'] }}</strong></span>
                                        @if ($active['url_foto_ktp'])
                                            <a href="{{ $active['url_foto_ktp'] }}" target="_blank" class="font-bold text-blue-600 hover:underline">Pratinjau Foto KTP Sistem</a>
                                        @endif
                                    </div>
                                </div>

                                <!-- Checkbox 2: Fisik Awal Lengkap -->
                                <div class="space-y-1 rounded-[4px] bg-[#f9f9f9] p-3 border border-neutral-200">
                                    <label class="flex items-start gap-2.5 cursor-pointer">
                                        <input type="checkbox" wire:model="checklistKondisiAwalOk" class="mt-0.5 rounded-[4px] border-neutral-300 text-black focus:ring-black">
                                        <span class="font-bold text-black">
                                            2. Kelengkapan unit (body kamera, lensa, baterai, charger, tas) lengkap dan berfungsi normal
                                        </span>
                                    </label>
                                    @error('checklistKondisiAwalOk') <span class="block text-[11px] font-semibold text-[#ba1a1a]">{{ $message }}</span> @enderror
                                </div>

                                <!-- Step 3: Upload Foto Awal -->
                                <div class="space-y-2">
                                    <label class="block font-bold text-neutral-700">
                                        3. Unggah Foto Kondisi Awal Unit (Dokumentasi Serah Terima)
                                    </label>
                                    <input type="file" wire:model="fotoKondisiAwal" accept="image/*" class="w-full text-xs text-neutral-500 file:mr-3 file:rounded-[4px] file:border-0 file:bg-[#d9d9d9] file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-black hover:file:bg-neutral-300">
                                    @error('fotoKondisiAwal') <span class="block text-[11px] font-semibold text-[#ba1a1a]">{{ $message }}</span> @enderror

                                    @if ($fotoKondisiAwal)
                                        <div class="mt-2 flex items-center gap-3">
                                            <img src="{{ $fotoKondisiAwal->temporaryUrl() }}" alt="Pratinjau Kondisi Awal" class="h-20 w-28 rounded-[4px] object-cover border border-neutral-300">
                                            <span class="text-[11px] font-medium text-neutral-500">Pratinjau foto dokumentasi serah terima</span>
                                        </div>
                                    @endif
                                </div>

                                <button type="button" wire:click="confirmHandover({{ $active['id'] }})" class="rounded-[4px] bg-[#000000] px-4 py-2.5 text-xs font-bold text-[#ffffff] hover:bg-neutral-800 transition">
                                    Konfirmasi & Tandai Barang Digunakan
                                </button>
                            </div>
                        @endif
                    </div>

                    <!-- TAHAP 3 BOX: CEKLIST PENANDA PEMINJAMAN SELESAI / PENGEMBALIAN -->
                    <div class="rounded-[4px] border border-neutral-200 bg-[#f9f9f9] p-5">
                        <div class="flex items-center justify-between border-b border-neutral-200 pb-3">
                            <div class="flex items-center gap-2">
                                <span class="flex h-5 w-5 items-center justify-center rounded-[4px] bg-black text-[10px] font-bold text-white">3</span>
                                <h3 class="text-sm font-bold text-[#000000]">Tahap 3: Ceklist Penanda Peminjaman Selesai / Pengembalian (SRS FR-TEN-04 & UC-13/14)</h3>
                            </div>
                            @if ($active['is_completed'])
                                <span class="rounded-[4px] border border-[#fff700] bg-[#fff700] px-2.5 py-1 text-[10px] font-bold text-[#000000]">
                                    ✓ Peminjaman Selesai
                                </span>
                            @elseif ($active['is_disputed'])
                                <span class="rounded-[4px] border border-[#ffdad6] bg-[#ffdad6] px-2.5 py-1 text-[10px] font-bold text-[#ba1a1a]">
                                    ⚠ Sengketa Kerusakan
                                </span>
                            @elseif (! $active['is_in_use'])
                                <span class="rounded-[4px] border border-neutral-300 bg-[#d9d9d9] px-2.5 py-1 text-[10px] font-bold text-[#000000]">
                                    Terkunci
                                </span>
                            @else
                                <span class="rounded-[4px] border border-[#bbf7d0] bg-[#dcfce7] px-2.5 py-1 text-[10px] font-bold text-[#16a34a]">
                                    Siap Diproses
                                </span>
                            @endif
                        </div>

                        @if (! $active['is_in_use'] && ! $stage3Done)
                            <div class="mt-4 rounded-[4px] border border-neutral-200 bg-[#ffffff] p-3 text-xs font-medium text-neutral-500">
                                🔒 <strong>Guard Aturan:</strong> Hanya aktif apabila pesanan sudah berstatus <em>sedang disewa</em> (in_use). Selesaikan Tahap 2 terlebih dahulu.
                            </div>
                        @elseif ($stage3Done)
                            <div class="mt-4 space-y-3 rounded-[4px] bg-[#ffffff] p-4 border border-neutral-200 text-xs">
                                <div class="grid gap-3 sm:grid-cols-3">
                                    <div>
                                        <span class="text-[10px] font-semibold uppercase text-neutral-400">Waktu Pengembalian Aktual</span>
                                        <div class="mt-1 font-bold text-black">{{ $active['actual_returned_at'] ?? '-' }}</div>
                                    </div>
                                    <div>
                                        <span class="text-[10px] font-semibold uppercase text-neutral-400">Denda Keterlambatan</span>
                                        <div class="mt-1 font-bold {{ $active['denda_keterlambatan'] > 0 ? 'text-[#ba1a1a]' : 'text-neutral-700' }}">
                                            {{ $this->rupiah($active['denda_keterlambatan']) }}
                                        </div>
                                    </div>
                                    <div>
                                        <span class="text-[10px] font-semibold uppercase text-neutral-400">Kondisi Akhir</span>
                                        <div class="mt-1 font-bold {{ $active['kondisi_barang_kembali'] === 'rusak' ? 'text-[#ba1a1a]' : 'text-[#16a34a]' }}">
                                            {{ strtoupper($active['kondisi_barang_kembali']) }}
                                        </div>
                                    </div>
                                </div>
                                @if ($active['kondisi_barang_kembali'] === 'rusak')
                                    <div class="rounded-[4px] border border-[#ffdad6] bg-[#ffdad6]/40 p-3">
                                        <div class="font-bold text-[#ba1a1a]">Detail Klaim Kerusakan (Diteruskan ke Pusat Aduan SuperAdmin):</div>
                                        <p class="mt-1 text-neutral-700">{{ $active['deskripsi_kerusakan'] ?: '-' }}</p>
                                        <div class="mt-1 font-bold text-[#ba1a1a]">Estimasi Klaim: {{ $this->rupiah($active['estimasi_biaya_kerusakan']) }}</div>
                                    </div>
                                @endif
                                @if ($active['foto_kondisi_akhir'])
                                    <div>
                                        <span class="text-[10px] font-semibold uppercase text-neutral-400">Dokumentasi Kondisi Akhir Unit</span>
                                        <div class="mt-1.5">
                                            <a href="{{ $active['foto_kondisi_akhir'] }}" target="_blank" class="inline-block">
                                                <img src="{{ $active['foto_kondisi_akhir'] }}" alt="Kondisi Akhir" class="h-20 w-28 rounded-[4px] object-cover border border-neutral-300">
                                            </a>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @else
                            <div class="mt-4 space-y-4 rounded-[4px] bg-[#ffffff] p-4 border border-neutral-200 text-xs">
                                <!-- Info Waktu Pengembalian & Denda Otomatis (UC-14) -->
                                <div class="rounded-[4px] border border-neutral-200 bg-[#f9f9f9] p-3 space-y-1">
                                    <div class="flex items-center justify-between">
                                        <span class="font-bold text-neutral-700">Tenggat Waktu Pengembalian:</span>
                                        <span class="font-bold text-[#B45309]">{{ $active['return_deadline'] }}</span>
                                    </div>
                                    @if ($active['is_overdue'])
                                        <div class="text-[#ba1a1a] font-bold">
                                            ⚠ Waktu sewa telah melewati batas tenggat. Sistem menghitung denda keterlambatan otomatis:
                                            <span class="text-sm font-extrabold text-[#ba1a1a]">{{ $this->rupiah($active['denda_otomatis']) }}</span>
                                        </div>
                                    @else
                                        <div class="text-[#16a34a] font-bold">
                                            ✓ Pengembalian unit masih dalam batas waktu sewa (Rp 0 denda).
                                        </div>
                                    @endif
                                </div>

                                <!-- Checkbox Kondisi Fisik Akhir -->
                                <div class="space-y-1 rounded-[4px] bg-[#f9f9f9] p-3 border border-neutral-200">
                                    <label class="flex items-start gap-2.5 cursor-pointer">
                                        <input type="checkbox" wire:model="checklistKondisiAkhirOk" class="mt-0.5 rounded-[4px] border-neutral-300 text-black focus:ring-black">
                                        <span class="font-bold text-black">
                                            Seluruh unit dan aksesori telah dikembalikan lengkap sesuai checklist awal
                                        </span>
                                    </label>
                                    @error('checklistKondisiAkhirOk') <span class="block text-[11px] font-semibold text-[#ba1a1a]">{{ $message }}</span> @enderror
                                </div>

                                <!-- Upload Foto Kondisi Akhir -->
                                <div class="space-y-2">
                                    <label class="block font-bold text-neutral-700">
                                        Unggah Foto Kondisi Akhir Unit (Dokumentasi Pengembalian)
                                    </label>
                                    <input type="file" wire:model="fotoKondisiAkhir" accept="image/*" class="w-full text-xs text-neutral-500 file:mr-3 file:rounded-[4px] file:border-0 file:bg-[#d9d9d9] file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-black hover:file:bg-neutral-300">
                                    @error('fotoKondisiAkhir') <span class="block text-[11px] font-semibold text-[#ba1a1a]">{{ $message }}</span> @enderror

                                    @if ($fotoKondisiAkhir)
                                        <div class="mt-2 flex items-center gap-3">
                                            <img src="{{ $fotoKondisiAkhir->temporaryUrl() }}" alt="Pratinjau Kondisi Akhir" class="h-20 w-28 rounded-[4px] object-cover border border-neutral-300">
                                            <span class="text-[11px] font-medium text-neutral-500">Pratinjau foto dokumentasi pengembalian</span>
                                        </div>
                                    @endif
                                </div>

                                <!-- Opsi Kondisi Kerusakan -->
                                <div class="space-y-3 rounded-[4px] bg-[#f9f9f9] p-3 border border-neutral-200">
                                    <label class="block font-bold text-black">Opsi Kondisi Unit:</label>
                                    <div class="flex items-center gap-6">
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <input type="radio" wire:model.live="kondisiKerusakan" value="baik" class="rounded-[4px] text-black focus:ring-black">
                                            <span class="font-bold text-black">Unit Baik / Tidak Ada Kerusakan</span>
                                        </label>
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <input type="radio" wire:model.live="kondisiKerusakan" value="rusak" class="rounded-[4px] text-[#ba1a1a] focus:ring-[#ba1a1a]">
                                            <span class="font-bold text-[#ba1a1a]">Ditemukan Kerusakan</span>
                                        </label>
                                    </div>

                                    @if ($kondisiKerusakan === 'rusak')
                                        <div class="mt-3 space-y-3 border-t border-neutral-200 pt-3">
                                            <div>
                                                <label class="block text-[11px] font-bold text-neutral-700">Deskripsi Kerusakan Unit</label>
                                                <textarea wire:model="deskripsiKerusakan" rows="2" placeholder="Jelaskan bagian yang rusak, lecet, pecah, atau malfungsi..." class="mt-1 w-full rounded-[4px] border border-neutral-300 bg-white p-2 text-xs text-black outline-none focus:border-black"></textarea>
                                                @error('deskripsiKerusakan') <span class="block text-[11px] font-semibold text-[#ba1a1a]">{{ $message }}</span> @enderror
                                            </div>
                                            <div>
                                                <label class="block text-[11px] font-bold text-neutral-700">Estimasi Biaya Klaim Kerusakan (Rp)</label>
                                                <input type="number" wire:model="estimasiBiayaKerusakan" min="0" placeholder="0" class="mt-1 w-full rounded-[4px] border border-neutral-300 bg-white p-2 text-xs text-black outline-none focus:border-black">
                                                @error('estimasiBiayaKerusakan') <span class="block text-[11px] font-semibold text-[#ba1a1a]">{{ $message }}</span> @enderror
                                            </div>
                                            <p class="text-[11px] font-medium text-[#ba1a1a]">
                                                * Catatan: Pesanan dengan kerusakan akan dialihkan ke status Sengketa (Disputed) untuk diteruskan ke Pusat Aduan SuperAdmin.
                                            </p>
                                        </div>
                                    @endif
                                </div>

                                <button type="button" wire:click="confirmReturn({{ $active['id'] }})" class="rounded-[4px] bg-[#000000] px-4 py-2.5 text-xs font-bold text-[#ffffff] hover:bg-neutral-800 transition">
                                    Tandai Peminjaman Selesai
                                </button>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="flex items-center justify-end border-t border-neutral-200 bg-[#f9f9f9] px-6 py-4">
                    <button type="button" wire:click="closeChecklistModal" class="rounded-[4px] bg-[#d9d9d9] px-4 py-2 text-xs font-bold text-[#000000] hover:bg-neutral-300 transition">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
