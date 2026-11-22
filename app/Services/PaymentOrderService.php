<?php

namespace App\Services;

use App\Models\DetailPemesananTenant;
use App\Models\Produk;
use App\Models\User;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PaymentOrderService
{
    private const SESSION_KEY = 'mirrorcam_payments';

    /**
     * Create a pending payment order from the current cart.
     *
     * @param  Collection<int, array<string, mixed>>  $cartItems
     * @return array<string, mixed>
     */
    public function create(
        User $user,
        string $tipeBayar,
        Collection $cartItems,
        ?string $kycPath = null,
        ?string $kycFilename = null,
        ?int $ktpId = null
    ): array {
        $totalBooking = (int) $cartItems->sum('harga');
        $token = Str::random(48);

        $order = [
            'token' => $token,
            'order_id' => $this->newOrderId(),
            'user_id' => (int) $user->id_akun,
            'tipe_bayar' => $tipeBayar,
            'total_booking' => $totalBooking,
            'gross_amount' => $this->amountFor($tipeBayar, $totalBooking),
            'items' => $cartItems->values()->all(),
            'customer' => [
                'first_name' => $user->nama_lengkap_ktp ?: 'Penyewa MirrorCam',
                'email' => $user->email,
                'phone' => $user->no_whatsapp,
            ],
            'kyc_path' => $kycPath,
            'kyc_filename' => $kycFilename,
            'status' => 'pending',
            'mode' => null,
            'qr_string' => null,
            'qr_url' => null,
            'qr_data_uri' => null,
            'error' => null,
            'midtrans' => null,
            'created_at' => now()->toIso8601String(),
            'expires_at' => now()->addMinutes((int) config('midtrans.expiry_minutes', 15))->toIso8601String(),
            'paid_at' => null,
            'booking_ids' => [],
        ];

        $cartHasValidProducts = $cartItems->every(fn (array $item) => Produk::query()
            ->where('id_produk', $item['id_produk'] ?? 0)
            ->where('id_tenant', $item['id_tenant'] ?? 0)
            ->exists());

        if ($ktpId && $cartHasValidProducts) {
            $bookingIds = DB::transaction(function () use ($cartItems, $order, $ktpId): array {
                $ids = [];
                foreach ($cartItems->values() as $index => $item) {
                    $start = now();
                    $duration = (string) ($item['durasi_kode'] ?? '1_hari');
                    [$amount, $unit] = match ($duration) {
                        '6_jam' => [6, 'hours'], '12_jam' => [12, 'hours'],
                        '3_hari' => [3, 'days'], '7_hari' => [7, 'days'], '15_hari' => [15, 'days'],
                        '1_bulan' => [30, 'days'], '2_bulan' => [60, 'days'], '3_bulan' => [90, 'days'],
                        default => [1, 'days'],
                    };

                    $booking = DetailPemesananTenant::query()->create([
                        'sub_order_eksternal' => $order['order_id'].'-'.str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT),
                        'id_tenant' => $item['id_tenant'],
                        'id_produk' => $item['id_produk'],
                        'id_ktp' => $ktpId,
                        'url_foto_ktp' => $order['kyc_path'] ?? '',
                        'waktu_ambil_booking' => $start,
                        'waktu_kembali_booking' => $start->copy()->add($unit, $amount),
                        'durasi_sewa_hari' => $unit === 'hours' ? 1 : $amount,
                        'harga_sewa_booking' => $item['harga'],
                        'total_harga' => $item['harga'],
                        'status_harga' => $order['tipe_bayar'] === 'dp_50' ? 'DP 50%' : 'Lunas',
                        'status_sewa' => 'Menunggu Pembayaran',
                    ]);
                    $ids[] = $booking->id_pemesanan;
                }

                return $ids;
            });
            $order['booking_ids'] = $bookingIds;
        }

        $payments = $this->prune(session()->get(self::SESSION_KEY, []));
        $payments[$token] = $order;
        session()->put(self::SESSION_KEY, $payments);

        return $order;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function find(string $token): ?array
    {
        $payments = session()->get(self::SESSION_KEY, []);

        return $payments[$token] ?? null;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findForUser(string $token, User $user): ?array
    {
        $order = $this->find($token);

        if (! $order || (int) ($order['user_id'] ?? 0) !== (int) $user->id_akun) {
            return null;
        }

        return $order;
    }

    /**
     * Find an existing, still-payable order for the same cart items and
     * payment type so checkout does not create duplicate bookings.
     *
     * @param  Collection<int, array<string, mixed>>  $cartItems
     * @return array<string, mixed>|null
     */
    public function findReusablePendingOrder(Collection $cartItems, string $tipeBayar): ?array
    {
        $itemKeys = $cartItems->pluck('item_key')->filter()->sort()->values()->all();

        if ($itemKeys === []) {
            return null;
        }

        $payments = $this->prune(session()->get(self::SESSION_KEY, []));

        foreach (array_reverse($payments, true) as $order) {
            if (($order['status'] ?? null) !== 'pending' || ($order['tipe_bayar'] ?? null) !== $tipeBayar) {
                continue;
            }

            $orderKeys = collect($order['items'] ?? [])->pluck('item_key')->filter()->sort()->values()->all();

            if ($orderKeys !== $itemKeys) {
                continue;
            }

            try {
                if (Carbon::parse((string) ($order['expires_at'] ?? ''))->isPast()) {
                    continue;
                }
            } catch (\Throwable) {
                continue;
            }

            return $order;
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>|null
     */
    public function update(string $token, array $attributes): ?array
    {
        $order = $this->find($token);

        if (! $order) {
            return null;
        }

        $order = array_merge($order, $attributes);
        $this->put($order);

        return $order;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function markPaid(string $token): ?array
    {
        $order = $this->update($token, [
            'status' => 'paid',
            'paid_at' => now()->toIso8601String(),
        ]);

        if ($order && ! empty($order['booking_ids'])) {
            DetailPemesananTenant::query()->whereIn('id_pemesanan', $order['booking_ids'])
                ->where('status_sewa', 'Menunggu Pembayaran')
                ->update(['status_sewa' => 'Booking Aktif']);
        }

        return $order;
    }

    public function forget(string $token): void
    {
        $payments = session()->get(self::SESSION_KEY, []);
        unset($payments[$token]);
        session()->put(self::SESSION_KEY, $payments);
    }

    public function amountFor(string $tipeBayar, int $totalBooking): int
    {
        return $tipeBayar === 'dp_50'
            ? (int) round($totalBooking * 0.5)
            : $totalBooking;
    }

    public function newOrderId(): string
    {
        return 'MC-'.now()->format('YmdHis').'-'.Str::upper(Str::random(5));
    }

    public function qrDataUri(string $payload): ?string
    {
        try {
            $renderer = new ImageRenderer(new RendererStyle(280, 2), new SvgImageBackEnd);
            $svg = (new Writer($renderer))->writeString($payload);

            return 'data:image/svg+xml;base64,'.base64_encode($svg);
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * @param  array<string, array<string, mixed>>  $payments
     * @return array<string, array<string, mixed>>
     */
    private function prune(array $payments): array
    {
        return collect($payments)
            ->filter(function (array $order) {
                try {
                    return Carbon::parse((string) ($order['created_at'] ?? 'now'))->greaterThan(now()->subDay());
                } catch (\Throwable) {
                    return false;
                }
            })
            ->all();
    }

    private function put(array $order): void
    {
        $payments = session()->get(self::SESSION_KEY, []);
        $payments[$order['token']] = $order;
        session()->put(self::SESSION_KEY, $payments);
    }
}
