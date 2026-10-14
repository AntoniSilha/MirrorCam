@props(['order' => []])

@php
    $phone = $order['tenant_phone'] ?? '6281234567890';
    $productName = $order['product_name'] ?? 'Sony FX3 Cinema Line';
    $bookingCode = $order['booking_code'] ?? 'MC-001';
    $waUrl = "https://wa.me/{$phone}?text=" . urlencode("Halo, saya ingin menanyakan status pesanan {$bookingCode} ({$productName}) di MirrorCam.");
@endphp

<div class="flex items-center gap-2 mt-4 sm:mt-0">
    <!-- Hubungi Tenant Button -->
    <a href="{{ $waUrl }}"
       target="_blank"
       rel="noopener noreferrer"
       class="inline-flex items-center gap-2 px-3.5 py-2 bg-[#d9d9d9] hover:bg-neutral-300 text-[#000000] text-xs font-semibold rounded-[4px] transition shadow-2xs">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
        </svg>
        <span>Hubungi Tenant</span>
    </a>

    <!-- Check Status Button -->
    <button type="button"
            onclick="alert('Status Pesanan {{ $bookingCode }}: {{ $order['status_label'] ?? 'Booking Aktif' }}')"
            class="inline-flex items-center gap-2 px-4 py-2 bg-[#000000] hover:bg-neutral-800 text-[#ffffff] text-xs font-semibold rounded-[4px] transition shadow-xs cursor-pointer">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
        </svg>
        <span>Check Status</span>
    </button>
</div>
