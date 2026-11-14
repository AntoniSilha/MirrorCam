<?php

namespace App\Portals\Customer\Controllers;

use App\Http\Controllers\Controller;
use App\Models\KategoriProduk;
use App\Models\Produk;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductCatalogController extends Controller
{
    public function index(Request $request)
    {
        // Daftar wilayah hub utama
        $wilayahList = [
            'Jakarta Selatan' => 'Jakarta Selatan & Sekitarnya',
            'Jakarta Barat'   => 'Jakarta Barat & Pusat',
            'Bandung'         => 'Bandung & Sekitarnya',
            'Surabaya'        => 'Surabaya & Malang',
            'Bali'            => 'Bali (Denpasar, Canggu, Ubud)',
            'Yogyakarta'      => 'D.I. Yogyakarta',
        ];

        // Hitung jumlah produk riil pada masing-masing wilayah hub
        $wilayahCounts = [];
        foreach ($wilayahList as $keyword => $label) {
            $wilayahCounts[$keyword] = Produk::whereHas('tenant', function ($q) use ($keyword) {
                $q->where('alamat_toko', 'like', "%{$keyword}%");
            })->count();
        }

        $totalSemuaWilayah = Produk::count();
        $totalMitraAktif = Tenant::where('status_mitra', 'terverifikasi')->count() ?: Tenant::count();

        // Daftar kategori beserta jumlah produk aktif
        $kategoris = KategoriProduk::withCount('produks')->get();

        // Query produk lintas tenant
        $query = Produk::with(['tenant', 'galeri', 'kategori']);

        // 1. Pencarian nama produk atau nama tenant
        if ($request->filled('q')) {
            $search = trim($request->q);
            $query->where(function ($q) use ($search) {
                $q->where('nama_produk', 'like', "%{$search}%")
                  ->orWhere('deskripsi_lengkap', 'like', "%{$search}%")
                  ->orWhereHas('tenant', fn($t) => $t->where('nama_toko', 'like', "%{$search}%"));
            });
        }

        // 2. Filter Lokasi & Hub Tenant
        if ($request->filled('lokasi') && is_array($request->lokasi)) {
            $lokasis = array_filter($request->lokasi);
            if (!empty($lokasis)) {
                $query->whereHas('tenant', function ($q) use ($lokasis) {
                    $q->where(function ($sub) use ($lokasis) {
                        foreach ($lokasis as $wil) {
                            $sub->orWhere('alamat_toko', 'like', "%{$wil}%");
                        }
                    });
                });
            }
        }

        // 3. Filter Rentang Harga Sewa (Preset Radio atau Input Min/Max)
        if ($request->filled('harga_preset')) {
            match ($request->harga_preset) {
                'under_300k' => $query->where('harga_sewa', '<', 300000),
                '300k_1m'    => $query->whereBetween('harga_sewa', [300000, 1000000]),
                '1m_3m'      => $query->whereBetween('harga_sewa', [1000000, 3000000]),
                'above_3m'   => $query->where('harga_sewa', '>', 3000000),
                default      => null,
            };
        } else {
            if ($request->filled('min_harga')) {
                $minVal = (int) preg_replace('/\D/', '', $request->min_harga);
                if ($minVal > 0) {
                    $query->where('harga_sewa', '>=', $minVal);
                }
            }
            if ($request->filled('max_harga')) {
                $maxVal = (int) preg_replace('/\D/', '', $request->max_harga);
                if ($maxVal > 0) {
                    $query->where('harga_sewa', '<=', $maxVal);
                }
            }
        }

        // 4. Filter Kategori Peralatan
        if ($request->filled('kategori') && is_array($request->kategori)) {
            $kats = array_filter($request->kategori);
            if (!empty($kats)) {
                $query->whereIn('id_kategori', $kats);
            }
        }

        // 5. Urutan Tampilan
        match ($request->get('sort', 'populer')) {
            'termurah' => $query->orderBy('harga_sewa', 'asc'),
            'termahal' => $query->orderBy('harga_sewa', 'desc'),
            'terbaru'  => $query->orderBy('created_at', 'desc'),
            default    => $query->orderBy('id_produk', 'asc'),
        };

        $produks = $query->paginate(12)->withQueryString();
        $user = Auth::user();

        return view('customer.katalog.index', compact(
            'produks',
            'wilayahList',
            'wilayahCounts',
            'totalSemuaWilayah',
            'totalMitraAktif',
            'kategoris',
            'user'
        ));
    }
}
