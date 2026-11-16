@extends('layouts.landing')

@section('title', 'Katalog ' . ($tenant->nama_tenant ?? 'Indonesia Gallery') . ' — MirrorCam')

@section('content')
<div class="bg-[#f9f9f9] text-[#000000] font-['Montserrat'] py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        @include('customer.tenant.components.tenant-profile', ['tenant' => $tenant])

        @include('customer.tenant.components.filter-bar', [
            'tenant' => $tenant,
            'kategoriTabs' => $kategoriTabs,
            'totalSemuaAlat' => $totalSemuaAlat
        ])

        <div class="flex items-center justify-between mb-4">
            <h2 class="text-base font-bold text-[#000000]">Katalog {{ $tenant->nama_tenant ?? 'Indonesia Gallery' }}</h2>
            <p class="text-xs text-black/50 font-medium">
                Menampilkan {{ $produks->count() }} dari {{ $produks->total() }} gear
            </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
            @forelse($produks as $produk)
                @include('customer.tenant.components.product-card', [
                    'produk' => $produk,
                    'tenant' => $tenant
                ])
            @empty
                <div class="col-span-full bg-[#ffffff] border border-black/10 rounded-[4px] p-12 text-center">
                    <p class="text-sm text-black/60">Tidak ada peralatan yang sesuai dengan filter pencarian.</p>
                </div>
            @endforelse
        </div>

        <div class="bg-[#ffffff] border border-black/10 rounded-[4px] px-5 py-3.5 flex flex-col sm:flex-row items-center justify-between gap-4">
            <p class="text-xs text-black/60 font-medium">
                Menampilkan {{ $produks->firstItem() ?? 0 }} - {{ $produks->lastItem() ?? 0 }} dari {{ $produks->total() }} peralatan di {{ $tenant->nama_tenant ?? 'Indonesia Gallery' }}
            </p>

            <div class="flex items-center gap-1.5">
                @if ($produks->onFirstPage())
                    <span class="w-8 h-8 rounded-[4px] border border-black/15 flex items-center justify-center text-black/30">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>                    </span>
                @else
                    <a href="{{ $produks->previousPageUrl() }}" class="w-8 h-8 rounded-[4px] border border-black/15 flex items-center justify-center text-[#000000] hover:bg-[#f9f9f9]">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                   </a>
                @endif

                @foreach ($produks->getUrlRange(1, $produks->lastPage()) as $page => $url)
                    <a href="{{ $url }}"
                       class="w-8 h-8 rounded-[4px] text-xs font-semibold flex items-center justify-center transition {{ $page == $produks->currentPage() ? 'bg-[#000000] text-[#ffffff]' : 'border border-black/15 text-[#000000] hover:bg-[#f9f9f9]' }}">
                        {{ $page }}
                    </a>
                @endforeach

                @if ($produks->hasMorePages())
                    <a href="{{ $produks->nextPageUrl() }}" class="w-8 h-8 rounded-[4px] border border-black/15 flex items-center justify-center text-[#000000] hover:bg-[#f9f9f9]">
<svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>                    </a>
                @else
                    <span class="w-8 h-8 rounded-[4px] border border-black/15 flex items-center justify-center text-black/30">
<svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>                    </span>
                @endif
            </div>
        </div>
    </div>
</div>

@include('customer.tenant.components.detail-modal')

<script>
    function openDetailModal(produk) {
        const fotoUtama = (produk.galeri && produk.galeri.length > 0)
            ? produk.galeri[0].url_foto_produk
            : 'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?auto=format&fit=crop&w=600&q=80';

        document.getElementById('detailImg').src = fotoUtama;
        document.getElementById('detailKategori').textContent = produk.kategori ? produk.kategori.nama_kategori : 'Gear';
        document.getElementById('detailNama').textContent = produk.nama_produk;
        document.getElementById('detailDeskripsi').textContent = produk.deskripsi_lengkap || 'Spesifikasi standar industri.';
        document.getElementById('detailWarna').textContent = produk.color_produk || 'Black';
        document.getElementById('detailStok').textContent = produk.total_stok_unit + ' Unit';
        document.getElementById('detailHarga').textContent = 'Rp ' + Number(produk.harga_sewa).toLocaleString('id-ID') + ' / hari';

        const modal = document.getElementById('detailModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeDetailModal() {
        const modal = document.getElementById('detailModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
</script>
@endsection
