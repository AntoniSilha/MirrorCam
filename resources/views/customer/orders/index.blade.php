@extends('layouts.user-dashboard', ['user' => $user])

@section('title', 'Data Pesanan - MirrorCam')

@section('content')
    <div class="w-full">
        <!-- Welcome Header -->
        <x-orders.welcome-header :user="$user" />

        <!-- Filter Tabs & Sort -->
        <x-orders.filter-tabs :activeFilter="$activeFilter" :counts="$counts" :activeSort="$activeSort" />

        <!-- Orders List -->
        <div class="space-y-4">
            @forelse ($orders as $order)
                <x-orders.order-card :order="$order" />
            @empty
                <div class="bg-white rounded-lg p-12 text-center border border-neutral-200 shadow-2xs">
                    <div class="w-12 h-12 rounded-full bg-neutral-100 text-neutral-400 mx-auto flex items-center justify-center mb-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-bold text-black mb-1">Belum Ada Pesanan</h3>
                    <p class="text-xs text-neutral-500 max-w-sm mx-auto mb-4">
                        Tidak ada riwayat pesanan yang sesuai dengan filter yang Anda pilih saat ini.
                    </p>
                </div>
            @endforelse
        </div>
    </div>
@endsection