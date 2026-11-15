@extends('layouts.landing')

@section('title', 'Eksplorasi Tenant — MirrorCam Multi-Tenant Rental')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10">
    <!-- Filter Bar: Header, Search Input, and Rating Pills -->
    @include('customer.tenant.components.filter-bar', [
        'activeRating' => $activeRating,
        'searchKeyword' => $searchKeyword,
    ])

    <!-- Tenant Grid / Empty State -->
    @if ($tenants->isNotEmpty())
        <!-- 3-Column Grid matching UI Mockup -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-8">
            @foreach ($tenants as $tenant)
                @include('customer.tenant.components.card-tenant', ['tenant' => $tenant])
            @endforeach
        </div>

        {{-- Tombol Muat Lebih Banyak Tenan (Pagination / Load More) --}}
        @if ($remainingTenants > 0)
            <div class="mt-10 flex justify-center">
                @php
                    $loadMoreParams = array_filter([
                        'search' => $searchKeyword,
                        'rating' => ($activeRating !== 'all' ? $activeRating : null),
                        'per_page' => $perPage + 9,
                    ]);
                @endphp
                <a
                    href="{{ route('tenant.index', $loadMoreParams) }}"
                    class="inline-flex items-center gap-2 bg-[#ffffff] text-[#000000] border border-gray-300 hover:bg-gray-50 px-6 py-2.5 rounded-[4px] text-xs font-semibold shadow-xs transition"
                >
                    <svg class="w-4 h-4 text-[#000000] animate-spin-hover" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    <span>Muat Lebih Banyak Tenan (Tersedia {{ $remainingTenants }} Lainnya)</span>
                </a>
            </div>
        @endif
    @else
        <!-- Komponen Empty State Terpusat (SRS NFR-USA-04 & UC-02) -->
        <div class="mt-8 bg-[#ffffff] rounded-[4px] border border-[#e5e5e5] p-10 sm:p-16 text-center shadow-xs">
            <div class="w-14 h-14 mx-auto mb-4 bg-[#f9f9f9] border border-gray-200 rounded-[4px] flex items-center justify-center text-gray-400">
                <svg class="w-7 h-7 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </div>

            @if (!empty($searchKeyword) || ($activeRating !== 'all' && !empty($activeRating)))
                <h2 class="text-base sm:text-lg font-bold text-[#000000]">
                    Tidak Ada Mitra Tenant Ditemukan
                </h2>
                <p class="text-xs text-gray-500 mt-2 max-w-md mx-auto leading-relaxed">
                    Tidak ada mitra tenant yang cocok dengan kata kunci <span class="font-semibold text-black">"{{ $searchKeyword ?: 'filter rating ' . $activeRating }}"</span>. Silakan gunakan kata kunci lain atau reset filter.
                </p>
                <div class="mt-6">
                    <a
                        href="{{ route('tenant.index') }}"
                        class="inline-block bg-[#000000] text-[#ffffff] px-5 py-2.5 rounded-[4px] text-xs font-semibold hover:bg-neutral-800 transition shadow-xs"
                    >
                        Reset Filter & Pencarian
                    </a>
                </div>
            @else
                <h2 class="text-base sm:text-lg font-bold text-[#000000]">
                    Belum Ada Mitra Tenant Aktif
                </h2>
                <p class="text-xs text-gray-500 mt-2 max-w-md mx-auto leading-relaxed">
                    Belum ada mitra tenant aktif yang tersedia saat ini. Kartu tenant akan otomatis muncul setelah mitra baru mendaftar dan diverifikasi oleh sistem.
                </p>
            @endif
        </div>
    @endif
</div>
@endsection
