@props([
    'title',
    'value',
    'subtext' => null,
    'trend' => null,
    'trendLabel' => null,
    'icon' => null,
])

<div class="bg-[#ffffff] border border-[#e5e5e5] rounded-[4px] p-6 sm:p-7 flex flex-col justify-between shadow-xs transition hover:border-gray-300">
    <div class="flex items-start justify-between gap-3">
        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider block">
            {{ $title }}
        </span>
        @if ($icon)
            <div class="w-8 h-8 rounded-[4px] bg-[#f9f9f9] border border-gray-200 flex items-center justify-center text-gray-500 shrink-0">
                {!! $icon !!}
            </div>
        @endif
    </div>

    <div class="mt-4 flex items-baseline gap-3 flex-wrap">
        <span class="text-2xl sm:text-3xl font-extrabold text-[#000000] tracking-tight">
            {{ $value }}
        </span>

        @if ($trend)
            <div class="inline-flex items-center gap-1 text-[11px] font-bold text-[#16a34a]">
                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                </svg>
                <span>{{ $trend }}</span>
                @if ($trendLabel)
                    <span class="text-gray-500 font-medium ml-0.5">{{ $trendLabel }}</span>
                @endif
            </div>
        @elseif ($subtext)
            <span class="text-xs text-gray-500 font-medium">
                {{ $subtext }}
            </span>
        @endif
    </div>
</div>
