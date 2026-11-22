@extends('layouts.landing')

@section('title', 'Katalog ' . ($tenant->nama_tenant ?? 'Indonesia Gallery') . ' — MirrorCam')

@section('content')
<div class="bg-[#f9f9f9] text-[#000000] font-['Montserrat'] py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        @include('customer.tenant.components.tenant-profile', ['tenant' => $tenant])

        @if ($tenant->mode_libur)
            <div class="mb-5 rounded-[4px] border border-amber-300 bg-amber-50 px-4 py-3 text-xs font-semibold text-amber-800">
                Toko ini sedang libur sementara dan belum menerima pesanan baru. Silakan kembali lagi nanti.
            </div>
        @endif

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

@endsection
