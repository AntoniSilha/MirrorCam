<?php

use App\Models\DetailPemesananTenant;
use App\Models\GaleriFotoProduk;
use App\Models\Produk;
use App\Models\Tenant;
use App\Models\VerifikasiKtp;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

new #[Title('Data Pesanan - MirrorCam')] #[Layout('components.layouts.user-dashboard')] class extends Component {
    #[Url(as: 'filter')]
    public string $activeFilter = 'semua';

    #[Url(as: 'sort')]
    public string $activeSort = 'terbaru';

    #[Computed]
    public function profile(): array
    {
        $user = Auth::user();

        if (! $user) {
            return [
                'nama_lengkap_ktp' => 'Nama Pengguna',
                'role' => 'User',
                'avatar' => null,
                'initials' => 'NP',
            ];
        }

        return [
            'nama_lengkap_ktp' => $user->nama_lengkap_ktp ?: ($user->name ?? 'Nama Pengguna'),
            'role' => $user->role ?: 'User',
            'avatar' => $user->avatar ?? null,
            'initials' => $user->initials,
        ];
    }

    #[Computed]
    public function counts(): array
    {
        $orders = DetailPemesananTenant::query()->whereIn('id_ktp', $this->customerKtpIds());

        return [
            'semua' => (clone $orders)->count(),
            'sedang_disewa' => (clone $orders)->where('status_sewa', 'Sedang Disewa')->count(),
            'selesai' => (clone $orders)->where('status_sewa', 'Selesai')->count(),
        ];
    }

    #[Computed]
    public function orders(): array
    {
        $query = DetailPemesananTenant::query()->whereIn('id_ktp', $this->customerKtpIds());

        if ($this->activeFilter === 'sedang_disewa') {
            $query->where('status_sewa', 'Sedang Disewa');
        } elseif ($this->activeFilter === 'selesai') {
            $query->where('status_sewa', 'Selesai');
        }

        $query->orderBy('waktu_ambil_booking', $this->activeSort === 'terlama' ? 'asc' : 'desc');

        $orders = $query->get();

        $products = Produk::query()
            ->whereIn('id_produk', $orders->pluck('id_produk')->unique()->values())
            ->get()
            ->keyBy('id_produk');

        $tenants = Tenant::query()
            ->whereIn('id_tenant', $orders->pluck('id_tenant')->unique()->values())
            ->get()
            ->keyBy('id_tenant');

        $photos = GaleriFotoProduk::query()
            ->whereIn('id_produk', $orders->pluck('id_produk')->unique()->values())
            ->get()
            ->groupBy('id_produk');

        return $orders->map(function (DetailPemesananTenant $order) use ($products, $tenants, $photos): array {
            $product = $products->get($order->id_produk);
            $tenant = $tenants->get($order->id_tenant);
            $photo = $photos->get($order->id_produk)?->first();

            $startDate = $this->formatDate($order->waktu_ambil_booking);
            $endDate = $this->formatDate($order->waktu_kembali_booking);
            $status = (string) $order->status_sewa;
            $statusKey = $this->statusKey($status);

            return [
                'id' => $order->id_pemesanan,
                'booking_code' => $order->sub_order_eksternal ?: 'MC-'.str_pad((string) $order->id_pemesanan, 4, '0', STR_PAD_LEFT),
                'product_name' => $product?->nama_produk ?: 'Produk',
                'gear_image' => $photo?->url_foto_produk,
                'tenant_name' => $tenant?->nama_toko ?: 'Tenant',
                'tenant_location' => $tenant?->alamat_toko ?: '-',
                'tenant_display' => ($tenant?->nama_toko ?: 'Tenant').', '.($tenant?->alamat_toko ?: '-'),
                'tenant_phone' => $tenant?->whatsapp_resmi ?: '6281234567890',
                'status' => $statusKey,
                'status_label' => $status ?: 'Booking Aktif',
                'start_date' => $startDate,
                'end_date' => $endDate,
                'return_deadline' => $endDate.' (Maks. 18:00)',
                'total_tagihan' => (float) $order->total_harga,
                'formatted_total' => 'Rp '.number_format((float) $order->total_harga, 0, ',', '.'),
            ];
        })->values()->all();
    }

    public function setFilter(string $filter): void
    {
        if (in_array($filter, ['semua', 'sedang_disewa', 'selesai'], true)) {
            $this->activeFilter = $filter;
        }
    }

    /**
     * @return Collection<int, int>
     */
    protected function customerKtpIds(): Collection
    {
        $user = Auth::user();

        if (! $user) {
            return collect();
        }

        return VerifikasiKtp::query()
            ->where('id_akun', $user->id_akun)
            ->pluck('id_ktp');
    }

    protected function statusKey(string $status): string
    {
        $normalized = strtolower($status);

        return match (true) {
            str_contains($normalized, 'selesai') => 'selesai',
            str_contains($normalized, 'batal') => 'dibatalkan',
            str_contains($normalized, 'terlambat') => 'terlambat',
            default => 'booking_aktif',
        };
    }

    protected function formatDate(mixed $value): string
    {
        if (! $value) {
            return '-';
        }

        return Carbon::parse($value)->locale('id')->translatedFormat('d M Y');
    }
}; ?>

<div class="w-full">
    <x-orders.welcome-header :user="$this->profile" />

    <x-orders.filter-tabs :activeFilter="$activeFilter" :counts="$this->counts" :activeSort="$activeSort" />

    <div class="space-y-4">
        @forelse ($this->orders as $order)
            <div wire:key="order-{{ $order['id'] }}">
                <x-orders.order-card :order="$order" />
            </div>
        @empty
            <div class="bg-white rounded-lg p-12 text-center border border-neutral-200 shadow-2xs">
                <div class="w-12 h-12 rounded-full bg-neutral-100 text-neutral-400 mx-auto flex items-center justify-center mb-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
                <h3 class="text-sm font-bold text-black mb-1">Belum Ada Pesanan</h3>
                <p class="text-xs text-neutral-500 max-w-sm mx-auto mb-4">
                    Tidak ada riwayat pesanan yang sesuai dengan filter yang Anda pilih saat ini.
                </p>
            </div>
        @endforelse
    </div>
</div>
