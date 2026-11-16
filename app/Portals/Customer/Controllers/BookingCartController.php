<?php

namespace App\Portals\Customer\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class BookingCartController extends Controller
{
    public function showProduct($id)
    {
        $produk = Produk::with(['tenant', 'galeri', 'kategori'])->findOrFail($id);$user = Auth::user();

        return view('customer.produk.show', compact('produk', 'user'));
    }

    public function addToCart(Request $request)
    {
        $request->validate([
            'id_produk'   => 'required|exists:produks,id_produk',
            'durasi_kode' => 'required|string',
            'durasi_label'=> 'required|string',
            'warna'       => 'nullable|string',
            'total_harga' => 'required|numeric|min:0',
        ]);

        $produk = Produk::with(['tenant', 'galeri'])->findOrFail($request->id_produk);$cart = session()->get('mirrorcam_cart', []);

        $itemKey =$produk->id_produk . '_' . $request->durasi_kode . '_' . ($request->warna ?? 'default');

        $cart[$itemKey] = [
            'item_key'     => $itemKey,
            'id_produk'    => $produk->id_produk,
            'id_tenant'    => $produk->id_tenant,
            'nama_toko'    => $produk->tenant->nama_toko ?? 'MirrorCam Tenant',
            'nama_produk'  => $produk->nama_produk,
            'foto'         => $produk->primary_image,
            'warna'        => $request->warna ?? ($produk->color_produk ?? 'Black'),
            'durasi_label' => $request->durasi_label,
            'harga'        => (int) $request->total_harga,
        ];

        session()->put('mirrorcam_cart', $cart);

        return redirect()->route('keranjang.index')->with('success', 'Peralatan berhasil ditambahkan ke keranjang sewa!');
    }

    public function cartIndex()
    {
        $cartItems = collect(session()->get('mirrorcam_cart', []));
        $groupedByTenant =$cartItems->groupBy('id_tenant');

        $kodeTransaksiInduk = session()->get('mirrorcam_trx_code');
        if (!$kodeTransaksiInduk) {$kodeTransaksiInduk = 'MC-' . date('Ymd') . '-' . rand(1000, 9999);
            session()->put('mirrorcam_trx_code', $kodeTransaksiInduk);
        }

        $totalBooking = $cartItems->sum('harga');$user = Auth::user();

        return view('customer.keranjang.index', compact(
            'cartItems',
            'groupedByTenant',
            'kodeTransaksiInduk',
            'totalBooking',
            'user'
        ));
    }

    public function removeFromCart(Request $request)
    {
        $cart = session()->get('mirrorcam_cart', []);
        unset($cart[$request->item_key]);
        session()->put('mirrorcam_cart', $cart);

        return back()->with('success', 'Item dihapus dari keranjang.');
    }

    public function checkout(Request $request)
    {
        $request->validate([
            'ktp_file'   => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:4096',
            'tipe_bayar' => 'required|in:dp_50,lunas',
        ]);

        if ($request->hasFile('ktp_file')) {
            $path =$request->file('ktp_file')->store('kyc_documents', 'public');
            session()->put('mirrorcam_kyc_filename', $request->file('ktp_file')->getClientOriginalName());
            session()->put('mirrorcam_kyc_path', $path);
        }

        // Simulasi checkout berhasil & reset keranjang
        $kodeTrx = session()->get('mirrorcam_trx_code');
        $nominal =$request->tipe_bayar === 'dp_50'
            ? (int) ($request->total_booking * 0.5) : (int)$request->total_booking;

        session()->forget(['mirrorcam_cart', 'mirrorcam_trx_code']);

        return redirect()->route('katalog.index')->with(
            'success',
            "Booking berhasil diproses! Kode Transaksi: {$kodeTrx} (Nominal dibayar: Rp " . number_format($nominal, 0, ',', '.') . ")"
        );
    }
}
