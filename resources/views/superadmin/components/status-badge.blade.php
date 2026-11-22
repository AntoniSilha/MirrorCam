@props(['status'])

@php
    $cleanStatus = strtolower(trim((string) $status));
    $label = ucfirst($cleanStatus);

    if (str_contains($cleanStatus, 'aktif') || $cleanStatus === 'verified') {
        $classes = 'bg-[#dcfce7] text-[#16a34a]';
        $dotClass = 'bg-[#16a34a]';
        $label = 'AKTIF';
    } elseif (str_contains($cleanStatus, 'selesai')) {
        $classes = 'bg-[#fff700] text-[#000000]';
        $dotClass = 'bg-[#000000]';
        $label = 'Selesai';
    } elseif (str_contains($cleanStatus, 'terlambat') || str_contains($cleanStatus, 'suspend') || str_contains($cleanStatus, 'tangguh') || str_contains($cleanStatus, 'tolak')) {
        $classes = 'bg-[#ffdad6] text-[#ba1a1a]';
        $dotClass = 'bg-[#ba1a1a]';
        $label = str_contains($cleanStatus, 'suspend') || str_contains($cleanStatus, 'tangguh') ? 'Ditangguhkan' : 'Terlambat';
    } elseif (str_contains($cleanStatus, 'verifikasi') || str_contains($cleanStatus, 'validasi') || str_contains($cleanStatus, 'pending') || $cleanStatus === 'noverified') {
        $classes = 'bg-[#fef3c7] text-[#b45309]';
        $dotClass = 'bg-[#b45309]';
        $label = 'Menunggu Validasi';
    } else {
        $classes = 'bg-[#f3f4f6] text-[#374151]';
        $dotClass = 'bg-[#6b7280]';
        $label = ucfirst($cleanStatus);
    }
@endphp

<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-[4px] text-[10px] font-bold tracking-wider uppercase {{ $classes }}">
    <span class="w-1.5 h-1.5 rounded-full {{ $dotClass }}"></span>
    <span>{{ $label }}</span>
</span>
