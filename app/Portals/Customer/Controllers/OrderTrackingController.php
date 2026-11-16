<?php

namespace App\Portals\Customer\Controllers;

use App\Http\Controllers\Controller;
use App\Models\DetailPemesananTenant;
use App\Models\ReviewUlasanTenant;
use App\Models\VerifikasiKtp;
use App\Services\MidtransService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;

class OrderTrackingController extends Controller
{
    public function show(int $bookingId): View
    {
        $booking = $this->findCustomerBooking($bookingId);

        $status = strtolower((string) $booking->status_sewa);
        $paymentType = strtolower((string) $booking->status_harga);
        $dpPaid = str_contains($paymentType, 'dp') || str_contains($paymentType, 'lunas') || str_contains($status, 'aktif') || str_contains($status, 'sedang') || str_contains($status, 'selesai');
        $fullPayment = str_contains($paymentType, 'lunas') || str_contains($status, 'sedang') || str_contains($status, 'selesai');
        $inUse = str_contains($status, 'sedang') || str_contains($status, 'selesai');
        $returned = str_contains($status, 'selesai');
        $review = ReviewUlasanTenant::query()->where('id_pemesanan', $booking->id_pemesanan)->first();

        return view('customer.pesanan.tracking', compact('booking', 'dpPaid', 'fullPayment', 'inUse', 'returned', 'review'));
    }

    public function receipt(int $bookingId, MidtransService $midtrans): View|RedirectResponse
    {
        $booking = $this->findCustomerBooking($bookingId);
        $orderId = preg_replace('/-\d{2}$/', '', (string) $booking->sub_order_eksternal);
        $transaction = Cache::get('midtrans:status:'.$orderId);

        if ($midtrans->isConfigured()) {
            try {
                $transaction = $midtrans->transactionStatus($orderId);
            } catch (\Throwable $exception) {
                report($exception);
            }
        }

        if (! is_array($transaction)) {
            return redirect()->route('pesanan.status', $bookingId)
                ->with('error', 'Nota belum tersedia karena data transaksi Midtrans belum diterima.');
        }

        return view('customer.pesanan.receipt', compact('booking', 'transaction', 'orderId'));
    }

    public function storeReview(Request $request, int $bookingId): RedirectResponse
    {
        $booking = $this->findCustomerBooking($bookingId);
        $bookingStatus = strtolower((string) $booking->status_sewa);
        abort_if(str_contains($bookingStatus, 'menunggu pembayaran') || str_contains($bookingStatus, 'batal'), 403);

        $validated = $request->validate([
            'rating_nilai' => ['required', 'integer', 'between:1,5'],
            'komentar_text' => ['nullable', 'string', 'max:1000'],
        ]);

        ReviewUlasanTenant::query()->updateOrCreate(
            ['id_pemesanan' => $booking->id_pemesanan],
            [
                'id_akun_penyewa' => Auth::user()->id_akun,
                'id_tenant' => $booking->id_tenant,
                'rating_nilai' => $validated['rating_nilai'],
                'komentar_text' => $validated['komentar_text'] ?? null,
            ]
        );

        return redirect()->route('pesanan.status', $bookingId)->with('success', 'Komentar dan rating berhasil disimpan.');
    }

    private function findCustomerBooking(int $bookingId): DetailPemesananTenant
    {
        $ktpIds = VerifikasiKtp::query()
            ->where('id_akun', Auth::user()->id_akun)
            ->pluck('id_ktp');

        return DetailPemesananTenant::query()
            ->with(['tenant', 'produk.galeri'])
            ->whereIn('id_ktp', $ktpIds)
            ->findOrFail($bookingId);
    }
}
