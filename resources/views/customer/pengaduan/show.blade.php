@extends('layouts.landing')

@section('title', 'Detail Kasus #' . $pengaduan->nomor_tiket . ' — MirrorCam')

@section('content')
<div class="min-h-screen bg-[#f9f9f9] text-[#000000] py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <!-- Breadcrumb -->
        <nav class="flex items-center gap-2 text-xs text-gray-500 font-medium">
            <a href="{{ route('home') }}" class="hover:text-black transition">Beranda</a>
            <span>/</span>
            <a href="{{ route('pengaduan.index') }}" class="hover:text-black transition">Pusat Pengaduan</a>
            <span>/</span>
            <span class="text-[#000000] font-bold">Detail Kasus #{{ $pengaduan->nomor_tiket }}</span>
        </nav>

        <!-- Main Card -->
        <div class="bg-[#ffffff] border border-[#e5e5e5] rounded-[4px] p-6 sm:p-8 shadow-xs space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-[#e5e5e5]">
                <div>
                    <span class="text-[10px] font-extrabold uppercase tracking-widest text-gray-500 block mb-1">
                        BERKAS SENGKETA RESMI
                    </span>
                    <h1 class="text-xl sm:text-2xl font-extrabold text-[#000000]">
                        Tiket #{{ $pengaduan->nomor_tiket }}
                    </h1>
                    <p class="text-xs text-gray-500 mt-1">
                        Dilaporkan pada {{ $pengaduan->created_at->translatedFormat('d F Y, H:i T') }} • Tenant Rekanan: <span class="font-bold text-black">{{ $pengaduan->tenant?->nama_toko ?? 'Mitra MirrorCam' }}</span>
                    </p>
                </div>

                @php $badge = $pengaduan->status_badge; @endphp
                <span
                    class="inline-flex items-center px-3 py-1 text-xs font-extrabold uppercase rounded-[4px] border self-start sm:self-auto"
                    style="background-color: {{ $badge['bg'] }}; color: {{ $badge['text'] }}; border-color: {{ $badge['border'] }};"
                >
                    {{ $badge['label'] }}
                </span>
            </div>

            <!-- Product Box -->
            <div class="bg-[#f9f9f9] border border-[#e5e5e5] rounded-[4px] p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3.5">
                    <div class="w-16 h-16 bg-white border border-[#e5e5e5] rounded-[4px] overflow-hidden shrink-0 flex items-center justify-center p-1">
                        @if ($pengaduan->pemesanan?->produk?->primary_image)
                            <img src="{{ $pengaduan->pemesanan->produk->primary_image }}" alt="" class="w-full h-full object-cover rounded-[2px]" />
                        @else
                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                            </svg>
                        @endif
                    </div>
                    <div>
                        <span class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block">
                            NOMOR PESANAN: {{ $pengaduan->pemesanan?->sub_order_eksternal ?? ('INV-MC' . $pengaduan->id_pemesanan) }}
                        </span>
                        <h2 class="text-sm sm:text-base font-extrabold text-[#000000]">
                            {{ $pengaduan->pemesanan?->produk?->nama_produk ?? 'Peralatan Multimedia' }}
                        </h2>
                        <p class="text-xs text-gray-500 mt-0.5">
                            Harga Sewa: Rp {{ number_format((float) ($pengaduan->pemesanan?->harga_sewa_booking ?? 0), 0, ',', '.') }} / hari
                        </p>
                    </div>
                </div>

                <div class="text-left sm:text-right">
                    <span class="text-[10px] font-bold text-gray-500 uppercase block">Estimasi Nilai Klaim / Escrow:</span>
                    <span class="text-sm font-extrabold text-black">
                        {{ $pengaduan->formatted_estimasi_klaim ?: 'Rp 1.500.000' }}
                    </span>
                </div>
            </div>

            <!-- Details Section -->
            <div class="space-y-4 text-xs">
                <div>
                    <span class="text-[11px] font-bold text-gray-500 uppercase block mb-1">Kategori Pelanggaran:</span>
                    <span class="font-extrabold text-sm text-[#000000]">{{ $pengaduan->kategori_pelanggaran }}</span>
                </div>

                <div>
                    <span class="text-[11px] font-bold text-gray-500 uppercase block mb-1">Kronologi Kejadian Lengkap:</span>
                    <div class="p-4 bg-[#f9f9f9] border border-[#e5e5e5] rounded-[4px] text-gray-800 leading-relaxed font-medium">
                        {{ $pengaduan->kronologi_kejadian }}
                    </div>
                </div>

                @if ($pengaduan->pesan_peringatan)
                    <div class="p-3 bg-[#fffbeb] border border-[#fde68a] rounded-[4px] text-[#B45309] font-medium flex items-center gap-2">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <span>{{ $pengaduan->pesan_peringatan }}</span>
                    </div>
                @endif

                @if ($pengaduan->catatan_resolusi)
                    <div class="p-3 bg-[#f0fdf4] border border-[#bbf7d0] rounded-[4px] text-[#16a34a] font-medium flex items-center gap-2">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>{{ $pengaduan->catatan_resolusi }}</span>
                    </div>
                @endif

                @if (!empty($pengaduan->bukti_files))
                    <div class="pt-3 border-t border-[#e5e5e5] space-y-2">
                        <span class="text-[11px] font-bold text-gray-500 uppercase block">Berkas Bukti Terlampir:</span>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($pengaduan->bukti_files as $file)
                                <a
                                    href="{{ $file['url'] }}"
                                    target="_blank"
                                    class="inline-flex items-center gap-2 px-3 py-1.5 bg-[#ffffff] hover:bg-gray-100 border border-gray-300 rounded-[4px] text-xs font-semibold text-black shadow-2xs transition"
                                >
                                    <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                    </svg>
                                    <span>{{ $file['name'] }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <!-- Footer Action -->
            <div class="pt-4 border-t border-[#e5e5e5] flex justify-between items-center">
                <a
                    href="{{ route('pengaduan.index') }}"
                    class="px-4 py-2 bg-[#d9d9d9] hover:bg-[#c9c9c9] text-black text-xs font-bold rounded-[4px] border border-gray-300 transition"
                >
                    ← Kembali ke Pusat Resolusi
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
