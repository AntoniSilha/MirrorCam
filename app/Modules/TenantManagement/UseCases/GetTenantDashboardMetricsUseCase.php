<?php

namespace App\Modules\TenantManagement\UseCases;

use App\Models\DetailPemesananTenant;
use App\Models\KategoriProduk;
use App\Models\KomisiPlatform;
use App\Models\Produk;
use App\Models\Tenant;
use App\Models\User;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class GetTenantDashboardMetricsUseCase
{
    /**
     * @return array<string, mixed>
     */
    public function execute(User $user, string $productSearch = '', string $bookingSearch = ''): array
    {
        $tenant = Tenant::query()
            ->where('id_akun_pemilik', $user->id_akun)
            ->first();

        if (! $tenant) {
            return $this->emptyData($user);
        }

        return $this->databaseData($tenant, $productSearch, $bookingSearch);
    }

    /**
     * @return array<string, mixed>
     */
    protected function databaseData(Tenant $tenant, string $productSearch = '', string $bookingSearch = ''): array
    {
        $products = Produk::query()
            ->where('id_tenant', $tenant->id_tenant)
            ->orderBy('nama_produk')
            ->get();

        $bookings = DetailPemesananTenant::query()
            ->where('id_tenant', $tenant->id_tenant)
            ->orderByDesc('waktu_ambil_booking')
            ->get();

        $categories = KategoriProduk::query()->pluck('nama_kategori', 'id_kategori');
        $productsById = $products->keyBy('id_produk');

        $commissions = KomisiPlatform::query()
            ->whereIn('id_pemesanan', $bookings->pluck('id_pemesanan'))
            ->orderByDesc('created_at')
            ->get();

        $commissionsByBooking = $commissions->keyBy('id_pemesanan');

        $netByBooking = $bookings->mapWithKeys(function (DetailPemesananTenant $booking) use ($commissionsByBooking) {
            $commissionModel = $commissionsByBooking->get($booking->id_pemesanan);
            $commission = $commissionModel ? (float) $commissionModel->total_komisi : 0.0;

            return [$booking->id_pemesanan => max(0, (float) $booking->total_harga - $commission)];
        })->all();

        $grossRevenue = (float) $bookings->sum('total_harga');
        $platformCommission = (float) $commissions->sum('total_komisi');
        $netRevenue = (float) array_sum($netByBooking);

        $activeBookings = $bookings->filter(
            fn (DetailPemesananTenant $booking) => ! in_array(
                Str::lower((string) $booking->status_sewa),
                ['selesai', 'completed', 'dibatalkan', 'cancelled'],
                true
            )
        );

        $activeProductIds = $activeBookings->pluck('id_produk')->unique();
        $rentedUnits = (int) $products->whereIn('id_produk', $activeProductIds)->sum('total_stock_unit');

        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();
        $previousStart = $startOfMonth->copy()->subMonth()->startOfMonth();
        $previousEnd = $startOfMonth->copy()->subMonth()->endOfMonth();

        $currentMonthRevenue = $this->revenueBetween($bookings, $netByBooking, $startOfMonth, $endOfMonth);
        $previousMonthRevenue = $this->revenueBetween($bookings, $netByBooking, $previousStart, $previousEnd);

        $growth = $previousMonthRevenue > 0
            ? (($currentMonthRevenue - $previousMonthRevenue) / $previousMonthRevenue) * 100
            : 0.0;

        $daysElapsed = max(1, Carbon::now()->day);
        $averageDailyRevenue = $netRevenue / $daysElapsed;

        [$chartLabels, $chartValues] = $this->chartData($bookings, $netByBooking);

        $productRows = $products->map(fn (Produk $product) => [
            'nama' => $product->nama_produk,
            'kategori' => $categories->get($product->id_kategori) ?? 'Umum',
            'harga' => (float) $product->harga_sewa,
            'stok' => (int) $product->total_stock_unit,
            'status' => $product->total_stock_unit > 0 ? 'Tersedia' : 'Stok Habis',
        ])->values();

        $bookingRows = $bookings->map(fn (DetailPemesananTenant $booking) => [
            'kode' => $booking->sub_order_eksternal ?: 'MC-'.str_pad((string) $booking->id_pemesanan, 4, '0', STR_PAD_LEFT),
            'produk' => $productsById->get($booking->id_produk) ? $productsById->get($booking->id_produk)->nama_produk : 'Produk',
            'penyewa' => '-',
            'mulai' => $this->formatDate($booking->waktu_ambil_booking),
            'selesai' => $this->formatDate($booking->waktu_kembali_booking),
            'status' => $booking->status_sewa ?: 'Booking Aktif',
            'total' => (float) $booking->total_harga,
        ])->values();

        $commissionRows = $commissions->map(function (KomisiPlatform $commission) use ($bookings) {
            $booking = $bookings->firstWhere('id_pemesanan', $commission->id_pemesanan);

            return [
                'kode' => 'KOM-'.str_pad((string) $commission->id_komisi, 4, '0', STR_PAD_LEFT),
                'tanggal' => $this->formatDate($commission->created_at),
                'persen' => (float) $commission->persentase_komisi_platform,
                'komisi' => (float) $commission->total_komisi,
                'pendapatan' => $booking ? (float) $booking->total_harga : 0.0,
            ];
        })->values();

        $productTerm = Str::lower(trim($productSearch));

        if ($productTerm !== '') {
            $productRows = $productRows->filter(fn (array $row) => Str::contains(
                Str::lower($row['nama'].' '.$row['kategori'].' '.$row['status']),
                $productTerm
            ))->values();
        }

        $bookingTerm = Str::lower(trim($bookingSearch));

        if ($bookingTerm !== '') {
            $bookingRows = $bookingRows->filter(fn (array $row) => Str::contains(
                Str::lower(implode(' ', [
                    $row['kode'],
                    $row['produk'],
                    $row['penyewa'],
                    $row['mulai'],
                    $row['selesai'],
                    $row['status'],
                    (string) $row['total'],
                ])),
                $bookingTerm
            ))->values();
        }

        return [
            'has_tenant' => true,
            'tenant' => $this->tenantPayload($tenant),
            'stats' => [
                'total_produk' => $products->count(),
                'total_stok' => (int) $products->sum('total_stock_unit'),
                'penyewaan_berjalan' => $activeBookings->count(),
                'pendapatan_tenant' => $netRevenue,
                'pendapatan_kotor' => $grossRevenue,
                'komisi_platform' => $platformCommission,
                'tingkat_pertumbuhan' => round($growth, 1),
                'pendapatan_harian_rata_rata' => $averageDailyRevenue,
                'total_unit_tersewa' => $rentedUnits > 0 ? $rentedUnits : $activeBookings->count(),
            ],
            'chart' => [
                'labels' => $chartLabels,
                'values' => $chartValues,
            ],
            'products' => $productRows->all(),
            'bookings' => $bookingRows->take(10)->all(),
            'commissions' => $commissionRows->all(),
            'commission_billing' => $this->commissionBilling($tenant, $commissions),
        ];
    }

    /**
     * @param  Collection<int, KomisiPlatform>  $commissions
     * @return array<string, mixed>
     */
    protected function commissionBilling(Tenant $tenant, Collection $commissions): array
    {
        $amount = (float) $commissions->sum('total_komisi');

        if ($commissions->isEmpty() || $amount <= 0.0) {
            return [
                'has_bill' => false,
                'amount' => 0.0,
                'periode' => null,
                'jatuh_tempo' => null,
                'reference' => null,
                'qris_data_uri' => null,
            ];
        }

        $firstDate = Carbon::instance(new \DateTimeImmutable((string) $commissions->min('created_at')));
        $lastDate = Carbon::instance(new \DateTimeImmutable((string) $commissions->max('created_at')));
        $firstDate->locale('id');
        $lastDate->locale('id');

        $periode = $firstDate->isSameMonth($lastDate)
            ? $firstDate->translatedFormat('F Y')
            : $firstDate->translatedFormat('F Y').' - '.$lastDate->translatedFormat('F Y');

        $dueDate = $lastDate->copy()->addMonthNoOverflow()->day(10);
        $dueDate->locale('id');
        $reference = 'KOM-'.$tenant->id_tenant.'-'.$lastDate->format('Ym');

        $payload = implode('|', [
            'MIRRORCAM-COMMISSION',
            'TENANT:'.$tenant->nama_toko,
            'AMOUNT:'.number_format($amount, 0, '', ''),
            'PERIOD:'.$periode,
            'REF:'.$reference,
        ]);

        return [
            'has_bill' => true,
            'amount' => $amount,
            'periode' => $periode,
            'jatuh_tempo' => $dueDate->translatedFormat('d F Y'),
            'reference' => $reference,
            'qris_data_uri' => $this->qrisDataUri($payload),
        ];
    }

    protected function qrisDataUri(string $payload): ?string
    {
        try {
            $renderer = new ImageRenderer(new RendererStyle(260, 2), new SvgImageBackEnd);
            $svg = (new Writer($renderer))->writeString($payload);

            return 'data:image/svg+xml;base64,'.base64_encode($svg);
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function emptyData(User $user): array
    {
        [$chartLabels, $chartValues] = $this->chartData(collect(), []);
        $name = $user->nama_lengkap_ktp ?: 'Toko Mitra';

        return [
            'has_tenant' => false,
            'tenant' => [
                'id' => 0,
                'nama_toko' => $name,
                'pemilik' => $name,
                'email' => '-',
                'whatsapp' => '-',
                'alamat' => '-',
                'status' => 'Belum Terdaftar',
                'logo' => null,
                'inisial' => $this->initials($name),
            ],
            'stats' => [
                'total_produk' => 0,
                'total_stok' => 0,
                'penyewaan_berjalan' => 0,
                'pendapatan_tenant' => 0,
                'pendapatan_kotor' => 0,
                'komisi_platform' => 0,
                'tingkat_pertumbuhan' => 0,
                'pendapatan_harian_rata_rata' => 0,
                'total_unit_tersewa' => 0,
            ],
            'chart' => [
                'labels' => $chartLabels,
                'values' => $chartValues,
            ],
            'products' => [],
            'bookings' => [],
            'commissions' => [],
            'commission_billing' => [
                'has_bill' => false,
                'amount' => 0.0,
                'periode' => null,
                'jatuh_tempo' => null,
                'reference' => null,
                'qris_data_uri' => null,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function tenantPayload(Tenant $tenant): array
    {
        $name = $tenant->nama_toko ?: 'Toko Mitra';

        return [
            'id' => $tenant->id_tenant,
            'nama_toko' => $name,
            'pemilik' => $tenant->nama_penanggungjawab ?: 'Pemilik Toko',
            'email' => $tenant->email_resmi ?: '-',
            'whatsapp' => $tenant->whatsapp_resmi ?: '-',
            'alamat' => $tenant->alamat_toko ?: '-',
            'status' => $tenant->status_mitra ?: 'Menunggu Verifikasi',
            'logo' => $tenant->url_logo,
            'inisial' => $this->initials($name),
        ];
    }

    /**
     * @param  Collection<int, DetailPemesananTenant>  $bookings
     * @param  array<int, float>  $netByBooking
     * @return array{0: array<int, string>, 1: array<int, float>}
     */
    protected function chartData(Collection $bookings, array $netByBooking): array
    {
        $labels = [];
        $values = [];
        $lastBucketStart = Carbon::now()->startOfMonth()->subMonth();

        for ($index = 4; $index >= 0; $index--) {
            $start = $lastBucketStart->copy()->subMonths($index * 2);
            $end = $start->copy()->addMonth()->endOfMonth();

            $labels[] = $this->monthLabel($start).'-'.$this->monthLabel($end);
            $values[] = $this->revenueBetween($bookings, $netByBooking, $start, $end);
        }

        return [$labels, $values];
    }

    /**
     * @param  Collection<int, DetailPemesananTenant>  $bookings
     * @param  array<int, float>  $netByBooking
     */
    protected function revenueBetween(Collection $bookings, array $netByBooking, Carbon $start, Carbon $end): float
    {
        return (float) $bookings->filter(function (DetailPemesananTenant $booking) use ($start, $end) {
            $date = $booking->waktu_ambil_booking ?? $booking->created_at;

            return $date !== null && $date->betweenIncluded($start, $end);
        })->sum(fn (DetailPemesananTenant $booking) => (float) ($netByBooking[$booking->id_pemesanan] ?? 0));
    }

    protected function monthLabel(Carbon $date): string
    {
        return [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun',
            7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
        ][$date->month] ?? $date->format('M');
    }

    protected function formatDate(mixed $date): string
    {
        if (! $date) {
            return '-';
        }

        $parsed = date_create((string) $date);

        if (! $parsed) {
            return '-';
        }

        $carbon = Carbon::instance($parsed);

        return $carbon->format('d').' '.$this->monthLabel($carbon).' '.$carbon->format('Y');
    }

    protected function initials(string $name): string
    {
        $words = preg_split('/\s+/', trim($name)) ?: [];

        if (count($words) >= 2) {
            return Str::upper(Str::substr($words[0], 0, 1).Str::substr($words[1], 0, 1));
        }

        return Str::upper(Str::substr($name, 0, 2));
    }
}
