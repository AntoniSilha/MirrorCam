<?php

namespace App\Http\Controllers;

use App\Http\Requests\PengaduanPelanggan\StorePengaduanPelangganRequest;
use App\Models\DetailPemesananTenant;
use App\Models\PengaduanPelanggan;
use App\Models\VerifikasiKtp;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PengaduanPelangganController extends Controller
{
    /**
     * Display customer resolution hub / complaints list.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        // 1. Filter status (semua, aktif, selesai, terlambat)
        $activeTab = $request->query('status', 'semua');
        $kategori = $request->query('kategori');
        $keyword = trim((string) $request->query('keyword', ''));
        $sort = $request->query('sort', 'terbaru');

        // 2. Base isolated query for current customer
        $baseQuery = PengaduanPelanggan::query()
            ->with(['tenant', 'pemesanan.produk.galeri', 'user'])
            ->where('id_akun', $user->id_akun);

        // 3. Dynamic Summary Statistics for this user
        $totalCount = (clone $baseQuery)->count();
        $aktifCount = (clone $baseQuery)->whereIn('status_kasus', ['Aktif', 'Diproses', 'Menunggu Tanggapan', 'Mediasi'])->count();
        $selesaiCount = (clone $baseQuery)->whereIn('status_kasus', ['Selesai', 'Ditutup', 'Disetujui', 'Refund Selesai'])->count();
        $terlambatCount = (clone $baseQuery)->whereIn('status_kasus', ['Terlambat', 'Eskalasi', 'Perlu Tindakan Segera'])->count();

        $counts = [
            'semua' => $totalCount,
            'aktif' => $aktifCount,
            'selesai' => $selesaiCount,
            'terlambat' => $terlambatCount,
        ];

        // 4. Apply status tab filter
        $query = clone $baseQuery;
        if ($activeTab === 'aktif') {
            $query->whereIn('status_kasus', ['Aktif', 'Diproses', 'Menunggu Tanggapan', 'Mediasi']);
        } elseif ($activeTab === 'selesai') {
            $query->whereIn('status_kasus', ['Selesai', 'Ditutup', 'Disetujui', 'Refund Selesai']);
        } elseif (in_array($activeTab, ['terlambat', 'eskalasi'])) {
            $query->whereIn('status_kasus', ['Terlambat', 'Eskalasi', 'Perlu Tindakan Segera']);
        }

        // 5. Apply category filter
        if (! empty($kategori) && $kategori !== 'all') {
            $query->where('kategori_pelanggaran', 'like', "%{$kategori}%");
        }

        // 6. Apply keyword search
        if (! empty($keyword)) {
            $query->where(function ($q) use ($keyword) {
                $q->where('nomor_tiket', 'like', "%{$keyword}%")
                  ->orWhere('kronologi_kejadian', 'like', "%{$keyword}%")
                  ->orWhere('kategori_pelanggaran', 'like', "%{$keyword}%")
                  ->orWhereHas('pemesanan.produk', function ($pq) use ($keyword) {
                      $pq->where('nama_produk', 'like', "%{$keyword}%");
                  })
                  ->orWhereHas('tenant', function ($tq) use ($keyword) {
                      $tq->where('nama_toko', 'like', "%{$keyword}%");
                  });
            });
        }

        // 7. Apply sorting
        if ($sort === 'terlama') {
            $query->orderBy('created_at', 'asc');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $pengaduans = $query->paginate(5)->withQueryString();

        // 8. Retrieve customer bookings for the complaint submission dropdown
        $ktpIds = VerifikasiKtp::where('id_akun', $user->id_akun)->pluck('id_ktp');
        $userBookings = DetailPemesananTenant::with(['tenant', 'produk.galeri'])
            ->whereIn('id_ktp', $ktpIds)
            ->orderBy('created_at', 'desc')
            ->get();

        // 9. Standard resolution hub categories
        $kategoriOptions = [
            'Sensor Kotor & Malfungsi Optik',
            'Kerusakan Fisik Gear & Aksesoris',
            'Keterlambatan Pengembalian & Perhitungan Denda',
            'Ketidaksesuaian Spesifikasi / Paket Tidak Lengkap',
            'Fasilitas Studio Tidak Berfungsi Optimal',
            'Deposit Tertahan / Sengketa Pembayaran Escrow',
            'Lainnya',
        ];

        $viewName = view()->exists('customer.pengaduan.index') ? 'customer.pengaduan.index' : 'pages.customer.pengaduan.index';

        return view($viewName, [
            'pengaduans' => $pengaduans,
            'counts' => $counts,
            'totalLaporan' => $totalCount,
            'kasusAktif' => $aktifCount,
            'kasusSelesai' => $selesaiCount,
            'perluTindakan' => $terlambatCount,
            'userBookings' => $userBookings,
            'kategoriOptions' => $kategoriOptions,
            'activeTab' => $activeTab,
            'currentKategori' => $kategori,
            'keyword' => $keyword,
            'currentSort' => $sort,
            'user' => $user,
        ]);
    }

    /**
     * Show form to create new complaint.
     */
    public function create(): RedirectResponse
    {
        return redirect()->route('pengaduan.index', ['#form-pengaduan']);
    }

    /**
     * Store newly created customer complaint.
     */
    public function store(StorePengaduanPelangganRequest $request): RedirectResponse
    {
        $user = $request->user();

        // Ensure booking exists
        $booking = DetailPemesananTenant::with(['tenant', 'produk'])->findOrFail($request->id_pemesanan);

        // Security check: ensure user owns this booking (via KTP verification) if KTP records exist
        $userKtpIds = VerifikasiKtp::where('id_akun', $user->id_akun)->pluck('id_ktp');
        if ($userKtpIds->isNotEmpty() && ! $userKtpIds->contains($booking->id_ktp)) {
            abort(403, 'Anda tidak berwenang mengajukan sengketa untuk pesanan ini.');
        }

        // Handle uploaded proof files (photos / videos)
        $storedFiles = [];
        if ($request->hasFile('bukti_dokumen')) {
            $files = is_array($request->file('bukti_dokumen'))
                ? $request->file('bukti_dokumen')
                : [$request->file('bukti_dokumen')];

            foreach ($files as $file) {
                if ($file && $file->isValid()) {
                    $path = $file->store('pengaduan', 'public');
                    $storedFiles[] = $path;
                }
            }
        }

        $urlBukti = ! empty($storedFiles) ? json_encode($storedFiles) : null;

        // Generate standard Ticket Number: AMC-YYYY-XXXX
        $year = date('Y');
        $countThisYear = PengaduanPelanggan::whereYear('created_at', $year)->count() + 1;
        $nomorTiket = 'AMC-' . $year . '-' . str_pad((string) $countThisYear, 4, '0', STR_PAD_LEFT);

        // Create complaint record
        $pengaduan = PengaduanPelanggan::create([
            'nomor_tiket' => $nomorTiket,
            'id_akun' => $user->id_akun,
            'id_tenant' => $booking->id_tenant,
            'id_pemesanan' => $booking->id_pemesanan,
            'kategori_pelanggaran' => $request->kategori_pelanggaran,
            'waktu_kejadian' => $request->waktu_kejadian,
            'sla_tenggat' => Carbon::now()->addHours(24),
            'estimasi_klaim' => $request->estimasi_klaim ?: ($booking->total_harga ?: 500000),
            'kronologi_kejadian' => $request->kronologi_kejadian,
            'url_bukti_dokumen' => $urlBukti,
            'status_kasus' => 'Aktif',
            'status_klaim' => 'Escrow Ditahan',
            'pesan_peringatan' => 'Tenggat Waktu Tanggapan Tenant: 24 Jam (Sesuai SLA QC Digital)',
        ]);

        return redirect()->route('pengaduan.index')
            ->with('status', "Tiket {$pengaduan->nomor_tiket} berhasil didaftarkan. Tim mediasi MirrorCam akan segera memproses laporan Anda.");
    }

    /**
     * Display specific complaint details (supports JSON for modal & View).
     */
    public function show(Request $request, int|string $id): View|JsonResponse
    {
        $user = $request->user();

        $pengaduan = PengaduanPelanggan::with(['tenant', 'pemesanan.produk.galeri', 'user'])
            ->where('id_akun', $user->id_akun)
            ->where(function ($q) use ($id) {
                $q->where('id_pengaduan', $id)
                  ->orWhere('nomor_tiket', $id);
            })
            ->firstOrFail();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $pengaduan->id_pengaduan,
                    'nomor_tiket' => $pengaduan->nomor_tiket,
                    'kategori' => $pengaduan->kategori_pelanggaran,
                    'status_kasus' => $pengaduan->status_kasus,
                    'status_badge' => $pengaduan->status_badge,
                    'status_klaim' => $pengaduan->status_klaim,
                    'kronologi' => $pengaduan->kronologi_kejadian,
                    'pesan_peringatan' => $pengaduan->pesan_peringatan,
                    'catatan_resolusi' => $pengaduan->catatan_resolusi,
                    'evaluasi_kepuasan' => $pengaduan->evaluasi_kepuasan,
                    'estimasi_klaim' => $pengaduan->formatted_estimasi_klaim,
                    'sla_remaining' => $pengaduan->sla_remaining,
                    'tanggal_lapor' => $pengaduan->created_at->translatedFormat('d M Y (H:i T)'),
                    'tenant' => [
                        'nama' => $pengaduan->tenant?->nama_toko ?? 'Mitra MirrorCam',
                        'lokasi' => $pengaduan->tenant?->alamat_toko ?? 'Indonesia',
                        'whatsapp' => $pengaduan->tenant?->whatsapp_resmi ?? '-',
                    ],
                    'pesanan' => [
                        'sub_order' => $pengaduan->pemesanan?->sub_order_eksternal ?? 'INV-ORDER',
                        'produk' => $pengaduan->pemesanan?->produk?->nama_produk ?? 'Peralatan Multimedia',
                        'foto' => $pengaduan->pemesanan?->produk?->primary_image ?? '/images/gear/placeholder.jpg',
                        'harga_sewa' => $pengaduan->pemesanan?->harga_sewa_booking ?? 0,
                    ],
                    'bukti' => $pengaduan->bukti_files,
                ],
            ]);
        }

        $showView = view()->exists('customer.pengaduan.show') ? 'customer.pengaduan.show' : 'pages.customer.pengaduan.show';

        return view($showView, [
            'pengaduan' => $pengaduan,
            'user' => $user,
        ]);
    }
}
