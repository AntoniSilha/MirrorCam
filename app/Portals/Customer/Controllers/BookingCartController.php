<?php

namespace App\Portals\Customer\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Produk;
use App\Models\Tenant;
use App\Models\VerifikasiKtp;
use App\Services\PaymentOrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookingCartController extends Controller
{
    public function __construct(
        private readonly PaymentOrderService $paymentOrders
    ) {}

    public function showProduct($id)
    {
        $produk = Produk::with(['tenant', 'galeri', 'kategori', 'durasi'])
            ->withAvg('reviews as avg_rating', 'rating_nilai')
            ->withCount(['reviews', 'pemesanans'])
            ->findOrFail($id);

        $user = Auth::user();

        return view('customer.produk.show', compact('produk', 'user'));
    }

    public function addToCart(Request $request)
    {
        $validated = $request->validate([
            'id_produk' => 'required|exists:produks,id_produk',
            'durasi_kode' => 'required|string',
            'durasi_label' => 'required|string|max:255',
            'warna' => 'nullable|string|max:50',
        ]);

        $produk = Produk::with(['tenant', 'galeri', 'durasi'])->findOrFail($validated['id_produk']);

        if ($produk->tenant?->mode_libur) {
            return back()->withErrors([
                'id_produk' => 'Toko '.$produk->tenant->nama_toko.' sedang libur dan belum menerima pesanan baru.',
            ])->withInput();
        }

        $durasi = $produk->durasi->firstWhere('durasi', $validated['durasi_kode']);

        if (! $durasi) {
            return back()->withErrors([
                'durasi_kode' => 'Durasi sewa yang dipilih tidak tersedia untuk produk ini.',
            ])->withInput();
        }

        $cart = session()->get('mirrorcam_cart', []);

        $itemKey = $produk->id_produk.'_'.$durasi->durasi.'_'.($validated['warna'] ?? 'default');

        $cart[$itemKey] = [
            'item_key' => $itemKey,
            'id_produk' => $produk->id_produk,
            'id_tenant' => $produk->id_tenant,
            'nama_toko' => $produk->tenant->nama_toko ?? 'MirrorCam Tenant',
            'nama_produk' => $produk->nama_produk,
            'foto' => $produk->primary_image,
            'warna' => $validated['warna'] ?? ($produk->color_produk ?? 'Black'),
            'durasi_kode' => $durasi->durasi,
            'durasi_label' => $validated['durasi_label'],
            'harga' => (int) $durasi->harga,
        ];

        session()->put('mirrorcam_cart', $cart);

        return redirect()->route('keranjang.index')->with('success', 'Peralatan berhasil ditambahkan ke keranjang sewa!');
    }

    public function cartIndex()
    {
        $cartItems = collect(session()->get('mirrorcam_cart', []));

        $paidItemKeys = collect(session()->get('mirrorcam_payments', []))
            ->filter(fn ($order) => is_array($order) && ($order['status'] ?? null) === 'paid')
            ->flatMap(fn (array $order) => collect($order['items'] ?? [])->pluck('item_key'))
            ->filter()
            ->values()
            ->all();

        if ($paidItemKeys !== []) {
            $cartItems = $cartItems->reject(
                fn ($item, $key) => in_array($item['item_key'] ?? $key, $paidItemKeys, true)
            );

            if ($cartItems->isEmpty()) {
                session()->forget(['mirrorcam_cart', 'mirrorcam_trx_code']);
            } else {
                session()->put('mirrorcam_cart', $cartItems->all());
            }
        }

        $groupedByTenant = $cartItems->groupBy('id_tenant');

        $kodeTransaksiInduk = $cartItems->isEmpty()
            ? null
            : session()->get('mirrorcam_trx_code');

        if ($cartItems->isNotEmpty() && ! $kodeTransaksiInduk) {
            $kodeTransaksiInduk = 'MC-'.date('Ymd').'-'.rand(1000, 9999);
            session()->put('mirrorcam_trx_code', $kodeTransaksiInduk);
        }

        $totalBooking = $cartItems->sum('harga');
        $user = Auth::user();
        $hasKtp = VerifikasiKtp::query()->where('id_akun', $user->id_akun)->exists()
            || filled($user->foto_ktp);

        return view('customer.keranjang.index', compact(
            'cartItems',
            'groupedByTenant',
            'kodeTransaksiInduk',
            'totalBooking',
            'user',
            'hasKtp'
        ));
    }

    public function removeFromCart(Request $request)
    {
        $cart = session()->get('mirrorcam_cart', []);
        unset($cart[$request->item_key]);

        if (empty($cart)) {
            session()->forget(['mirrorcam_cart', 'mirrorcam_trx_code']);
        } else {
            session()->put('mirrorcam_cart', $cart);
        }

        return back()->with('success', 'Item dihapus dari keranjang.');
    }

    public function checkout(Request $request)
    {
        $validated = $request->validate([
            'ktp_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:4096',
            'tipe_bayar' => 'required|in:dp_50,lunas',
        ]);

        $cartItems = collect(session()->get('mirrorcam_cart', []))
            ->filter(fn ($item) => is_array($item) && ! empty($item['id_produk']));

        if ($cartItems->isEmpty()) {
            return redirect()->route('keranjang.index')
                ->withErrors(['cart' => 'Keranjang sewa masih kosong.']);
        }

        $closedTenants = Tenant::query()
            ->whereIn('id_tenant', $cartItems->pluck('id_tenant')->filter()->all())
            ->where('mode_libur', true)
            ->pluck('nama_toko');

        if ($closedTenants->isNotEmpty()) {
            return redirect()->route('keranjang.index')->withErrors([
                'cart' => 'Toko berikut sedang libur sehingga pesanan tidak dapat diproses: '.$closedTenants->implode(', ').'.',
            ]);
        }

        $user = Auth::user();
        $verification = VerifikasiKtp::query()->where('id_akun', $user->id_akun)->latest('id_ktp')->first();

        if (! $verification && filled($user->foto_ktp)) {
            $verification = VerifikasiKtp::query()->create([
                'id_akun' => $user->id_akun,
                'nik_ktp' => $user->nik_ktp ?: 'Belum diisi',
                'url_foto_ktp' => $user->foto_ktp,
                'status_verifikasi' => 'Terverifikasi',
            ]);
        }

        $kycPath = $verification?->url_foto_ktp ?: $user->foto_ktp;
        $kycFilename = $kycPath ? basename($kycPath) : null;

        if ($request->hasFile('ktp_file')) {
            $kycPath = $request->file('ktp_file')->store('kyc_documents', 'public');
            $kycFilename = $request->file('ktp_file')->getClientOriginalName();
            $verification = VerifikasiKtp::query()->create([
                'id_akun' => $user->id_akun,
                'nik_ktp' => $user->nik_ktp ?: 'Belum diisi',
                'url_foto_ktp' => $kycPath,
                'status_verifikasi' => 'Menunggu Verifikasi',
            ]);
            session()->put('mirrorcam_kyc_filename', $kycFilename);
            session()->put('mirrorcam_kyc_path', $kycPath);
        }

        if (! $verification) {
            return redirect()->route('keranjang.index')->withErrors([
                'ktp_file' => 'Unggah KTP agar pesanan dapat dikaitkan dengan akun Anda.',
            ]);
        }

        $order = $this->paymentOrders->create(
            $user,
            $validated['tipe_bayar'],
            $cartItems,
            $kycPath,
            $kycFilename,
            (int) $verification->id_ktp
        );

        session()->forget(['mirrorcam_cart', 'mirrorcam_trx_code']);

        return redirect()
            ->route('pembayaran.show', ['token' => $order['token']])
            ->with('success', 'Pesanan dibuat. Silakan selesaikan pembayaran QRIS.');
    }
}
