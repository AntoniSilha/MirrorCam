<?php

namespace App\Modules\BookingAndOrdering\UseCases;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class GetCustomerOrdersUseCase
{
    /**
     * Get customer orders with counts and filtering.
     *
     * @param  string  $filter  'all' | 'sedang-disewa' | 'selesai'
     * @param  string  $sort  'terbaru' | 'terlama'
     * @return array{orders: Collection<int, mixed>, counts: array{all: int, renting: int, completed: int}}
     */
    public function execute(User $user, string $filter = 'semua', string $sort = 'terbaru'): array
    {
        $allOrders = $this->retrieveOrders($user);

        // Calculate counts
        $counts = [
            'all' => $allOrders->count(),
            'semua' => $allOrders->count(),
            'renting' => $allOrders->filter(function ($order) {
                $status = is_array($order) ? ($order['status'] ?? '') : ($order->status_sewa ?? $order->status ?? '');
                $norm = strtolower(str_replace(' ', '_', (string) $status));

                return in_array($norm, ['sedang_disewa', 'booking_aktif', 'active']);
            })->count(),
            'sedang_disewa' => $allOrders->filter(function ($order) {
                $status = is_array($order) ? ($order['status'] ?? '') : ($order->status_sewa ?? $order->status ?? '');
                $norm = strtolower(str_replace(' ', '_', (string) $status));

                return in_array($norm, ['sedang_disewa', 'booking_aktif', 'active']);
            })->count(),
            'completed' => $allOrders->filter(function ($order) {
                $status = is_array($order) ? ($order['status'] ?? '') : ($order->status_sewa ?? $order->status ?? '');
                $norm = strtolower(str_replace(' ', '_', (string) $status));

                return in_array($norm, ['selesai', 'completed']);
            })->count(),
            'selesai' => $allOrders->filter(function ($order) {
                $status = is_array($order) ? ($order['status'] ?? '') : ($order->status_sewa ?? $order->status ?? '');
                $norm = strtolower(str_replace(' ', '_', (string) $status));

                return in_array($norm, ['selesai', 'completed']);
            })->count(),
        ];

        // Apply tab filter
        $filtered = match ($filter) {
            'sedang_disewa', 'sedang-disewa', 'renting' => $allOrders->filter(function ($order) {
                $status = is_array($order) ? ($order['status'] ?? '') : ($order->status_sewa ?? $order->status ?? '');
                $norm = strtolower(str_replace(' ', '_', (string) $status));

                return in_array($norm, ['sedang_disewa', 'booking_aktif', 'active']);
            }),
            'selesai', 'completed' => $allOrders->filter(function ($order) {
                $status = is_array($order) ? ($order['status'] ?? '') : ($order->status_sewa ?? $order->status ?? '');
                $norm = strtolower(str_replace(' ', '_', (string) $status));

                return in_array($norm, ['selesai', 'completed']);
            }),
            default => $allOrders,
        };

        // Apply sorting
        $sorted = $sort === 'terlama'
            ? $filtered->sortBy(function ($item) {
                if (is_array($item)) return $item['id'] ?? 0;
                return $item->waktu_ambil_booking ? strtotime((string) $item->waktu_ambil_booking) : ($item->id_pemesanan ?? $item->id ?? 0);
            })->values()
            : $filtered->sortByDesc(function ($item) {
                if (is_array($item)) return $item['id'] ?? 0;
                return $item->waktu_ambil_booking ? strtotime((string) $item->waktu_ambil_booking) : ($item->id_pemesanan ?? $item->id ?? 0);
            })->values();

        return [
            'orders' => $sorted,
            'counts' => $counts,
        ];
    }

    /**
     * Retrieve orders from database or mockup fallback
     *
     * @return Collection<int, mixed>
     */
    protected function retrieveOrders(User $user): Collection
    {
        if (Schema::hasTable('detail_pemesanan_tenants')) {
            $ktpIds = \App\Models\VerifikasiKtp::where('id_akun', $user->id_akun)->pluck('id_ktp');

            $dbOrders = \App\Models\DetailPemesananTenant::with(['tenant', 'produk.galeri'])
                ->whereIn('id_ktp', $ktpIds)
                ->get();

            if ($dbOrders->isNotEmpty()) {
                return $dbOrders;
            }

            // Fallback to all orders in database if this user doesn't have an order yet
            $allDbOrders = \App\Models\DetailPemesananTenant::with(['tenant', 'produk.galeri'])->get();
            if ($allDbOrders->isNotEmpty()) {
                return $allDbOrders;
            }
        }

        if (Schema::hasTable('bookings')) {
            $dbOrders = Booking::with('tenant')
                ->where('user_id', $user->getKey())
                ->get();

            if ($dbOrders->isNotEmpty()) {
                return $dbOrders;
            }
        }

        return $this->getMockOrders();
    }

    /**
     * Fallback mock orders directly matching the UI mockup
     *
     * @return Collection<int, mixed>
     */
    public function getMockOrders(): Collection
    {
        return collect([
            [
                'id' => 1,
                'booking_code' => 'MC-20251024-001',
                'tenant_name' => 'Indonesia Galery',
                'tenant_location' => 'Kebon Jeruk',
                'product_name' => 'Sony FX3 Cinema Line + FE 24–70mm GM II',
                'gear_image' => '/images/gear/sony-fx3.jpg',
                'status' => 'selesaikan_pembayaran',
                'status_label' => 'Selesaikan Pembayaran',
                'status_style' => [
                    'bg' => '#ffedd5',
                    'text' => '#c2410c',
                    'border' => '#fed7aa',
                ],
                'start_date' => '24 Okt 2025',
                'end_date' => '27 Okt 2025',
                'return_deadline' => '27 Okt 2025 (Maks. 18:00)',
                'total_tagihan' => 2400000,
                'formatted_total' => 'Rp 2.400.000',
                'tenant_phone' => '6281234567890',
            ],
            [
                'id' => 2,
                'booking_code' => 'MC-20251024-002',
                'tenant_name' => 'Indonesia Galery',
                'tenant_location' => 'Kebon Jeruk',
                'product_name' => 'Sony FX3 Cinema Line + FE 24–70mm GM II',
                'gear_image' => '/images/gear/sony-fx3.jpg',
                'status' => 'booking_aktif',
                'status_label' => 'Booking Aktif',
                'status_style' => [
                    'bg' => '#dcfce7',
                    'text' => '#16a34a',
                    'border' => '#bbf7d0',
                ],
                'start_date' => '24 Okt 2025',
                'end_date' => '27 Okt 2025',
                'return_deadline' => '27 Okt 2025 (Maks. 18:00)',
                'total_tagihan' => 2400000,
                'formatted_total' => 'Rp 2.400.000',
                'tenant_phone' => '6281234567890',
            ],
            [
                'id' => 3,
                'booking_code' => 'MC-20251024-003',
                'tenant_name' => 'Indonesia Galery',
                'tenant_location' => 'Kebon Jeruk',
                'product_name' => 'Sony FX3 Cinema Line + FE 24–70mm GM II',
                'gear_image' => '/images/gear/sony-fx3.jpg',
                'status' => 'selesai',
                'status_label' => 'Selesai',
                'status_style' => [
                    'bg' => '#fff700',
                    'text' => '#000000',
                    'border' => '#facc15',
                ],
                'start_date' => '24 Okt 2025',
                'end_date' => '27 Okt 2025',
                'return_deadline' => '27 Okt 2025 (Maks. 18:00)',
                'total_tagihan' => 2400000,
                'formatted_total' => 'Rp 2.400.000',
                'tenant_phone' => '6281234567890',
            ],
        ]);
    }
}
