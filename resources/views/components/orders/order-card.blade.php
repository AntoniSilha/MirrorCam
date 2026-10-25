@props(['order' => []])

@php
    $statusKey = $order['status'] ?? 'booking_aktif';
    $statusLabel = $order['status_label'] ?? 'Booking Aktif';
    $statusStyle = match ($statusKey) {
        'booking_aktif', 'active' => [
            'bg' => '#dcfce7',
            'text' => '#16a34a',
        ],
        'selesai', 'completed' => [
            'bg' => '#fff700',
            'text' => '#000000',
        ],
        'terlambat', 'overdue' => [
            'bg' => '#ffdad6',
            'text' => '#ba1a1a',
        ],
        'selesaikan_pembayaran', 'pending_payment' => [
            'bg' => '#ffedd5',
            'text' => '#c2410c',
        ],
        default => [
            'bg' => '#dcfce7',
            'text' => '#16a34a',
        ],
    };

    $tenantDisplay = $order['tenant_display'] ?? ($order['tenant_name'] ?? 'Indonesia Galery') . ', ' . ($order['tenant_location'] ?? 'Kebon Jeruk');
    $productName = $order['product_name'] ?? 'Sony FX3 Cinema Line + FE 24–70mm GM II';
    $startDate = $order['start_date'] ?? '24 Okt 2025';
    $returnDeadline = $order['return_deadline'] ?? '27 Okt 2025 (Maks. 18:00)';
    $formattedTotal = $order['formatted_total'] ?? 'Rp 2.400.000';
    $gearImage = $order['gear_image'] ?? null;
@endphp

<div class="bg-white rounded-[8px] p-5 md:p-6 border border-neutral-200/90 shadow-2xs mb-4 flex flex-col lg:flex-row lg:items-center justify-between gap-6 hover:border-neutral-300 transition duration-150">
    <!-- Left + Center Content -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-5 flex-1 min-w-0">
        <!-- Gear Photo / Cinema Rig Thumbnail -->
        <div class="w-full sm:w-44 md:w-48 h-32 rounded-[6px] overflow-hidden bg-neutral-900 shrink-0 flex items-center justify-center relative border border-neutral-800">
            @if ($gearImage)
                <img src="{{ $gearImage }}"
                     alt="{{ $productName }}"
                     class="w-full h-full object-cover"
                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';" />
            @endif
            <div class="{{ $gearImage ? 'hidden' : 'flex' }} w-full h-full flex-col items-center justify-center p-3 bg-neutral-900 text-neutral-400">
                <svg class="w-12 h-12 text-neutral-500 mb-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                </svg>
                <span class="text-[10px] text-neutral-400 font-medium">Cinema Rig FX3</span>
            </div>
        </div>

        <!-- Order Information -->
        <div class="flex-1 min-w-0">
            <!-- Tenant Location -->
            <div class="flex items-center gap-1.5 text-xs text-neutral-500 font-medium mb-1">
                <svg class="w-3.5 h-3.5 text-neutral-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                <span class="truncate">{{ $tenantDisplay }}</span>
            </div>

            <!-- Product Title -->
            <h2 class="text-base md:text-lg font-bold text-black leading-snug truncate">
                {{ $productName }}
            </h2>

            <!-- Status Indicator -->
            <div class="flex items-center gap-1.5 text-xs text-neutral-600 mt-1.5">
                <span>Status :</span>
                <span class="px-2 py-0.5 rounded-[4px] text-xs font-semibold"
                      style="background-color: {{ $statusStyle['bg'] }}; color: {{ $statusStyle['text'] }};">
                    {{ $statusLabel }}
                </span>
            </div>

            <!-- Rental Dates -->
            <div class="flex flex-wrap items-center gap-2 text-xs text-neutral-600 mt-2">
                <!-- Start Date -->
                <div class="flex items-center gap-1">
                    <svg class="w-3.5 h-3.5 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2" stroke-width="2"></rect>
                        <line x1="16" y1="2" x2="16" y2="6" stroke-width="2"></line>
                        <line x1="8" y1="2" x2="8" y2="6" stroke-width="2"></line>
                        <line x1="3" y1="10" x2="21" y2="10" stroke-width="2"></line>
                    </svg>
                    <span>Ambil: <strong class="font-semibold text-black">{{ $startDate }}</strong></span>
                </div>

                <span class="text-neutral-400">→</span>

                <!-- Return Date with #B45309 Warning Color -->
                <div class="flex items-center gap-1">
                    <svg class="w-3.5 h-3.5 text-[#B45309]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2" stroke-width="2"></rect>
                        <line x1="16" y1="2" x2="16" y2="6" stroke-width="2"></line>
                        <line x1="8" y1="2" x2="8" y2="6" stroke-width="2"></line>
                        <line x1="3" y1="10" x2="21" y2="10" stroke-width="2"></line>
                    </svg>
                    <span>Kembali:</span>
                    <span style="color: #B45309;" class="font-bold">
                        {{ $returnDeadline }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Side: Total Bill & Actions -->
    <div class="flex flex-col lg:items-end justify-between self-stretch lg:self-center pt-3 lg:pt-0 border-t lg:border-t-0 border-neutral-100 flex-shrink-0">
        <div class="lg:text-right">
            <span class="block text-[10px] font-semibold tracking-wider text-neutral-400 uppercase">TOTAL TAGIHAN</span>
            <div class="text-xl font-extrabold text-black mt-0.5">{{ $formattedTotal }}</div>
        </div>

        <div class="mt-3">
            <x-orders.order-actions :order="$order" />
        </div>
    </div>
</div>
