<?php

namespace App\Portals\Customer\Controllers;

use App\Http\Controllers\Controller;
use App\Models\KategoriProduk;
use App\Models\Produk;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TenantCatalogController extends Controller
{
    public function show(Request $request, $id = 1)
    {
        $tenant = Tenant::with('detailOperasional')
            ->withAvg('reviews as avg_rating', 'rating_nilai')
            ->withCount(['reviews', 'pemesanans'])
            ->find($id) ?? Tenant::first();

        if (! $tenant) {
            abort(404, 'Data tenant di tabel tenants masih kosong.');
        }

        $id = $tenant->id_tenant;

        // Hitung jumlah alat per kategori khusus milik tenant ini
        $kategoriTabs = KategoriProduk::withCount([
            'produks as total' => function ($query) use ($id) {
                $query->where('id_tenant', $id);
            },
        ])->get();

        $totalSemuaAlat = Produk::where('id_tenant', $id)->count();

        // Ambil daftar produk milik tenant + relasi foto galeri & kategori
        $query = Produk::with(['galeri', 'kategori', 'durasi'])
            ->withMin('durasi as lowest_duration_price', 'harga')
            ->withAvg('reviews as avg_rating', 'rating_nilai')
            ->withCount(['reviews', 'pemesanans'])
            ->where('id_tenant', $id);

        if ($request->filled('kategori')) {
            $query->where('id_kategori', $request->kategori);
        }

        if ($request->filled('search')) {
            $query->where('nama_produk', 'like', '%'.$request->search.'%');
        }

        if ($request->boolean('siap_pakai')) {
            $query->where('total_stock_unit', '>', 0);
        }

        match ($request->get('sort', 'terpopuler')) {
            'termurah' => $query->orderBy('lowest_duration_price', 'asc'),
            'termahal' => $query->orderBy('lowest_duration_price', 'desc'),
            default => $query->orderBy('id_produk', 'asc'),
        };

        $produks = $query->paginate(8)->withQueryString();
        $user = Auth::user();

        return view('customer.tenant.katalog', compact(
            'tenant',
            'kategoriTabs',
            'totalSemuaAlat',
            'produks',
            'user'
        ));
    }
}
