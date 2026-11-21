@props(['user' => []])

@php
    $userName = $user['nama_lengkap_ktp'] ?? auth()->user()?->nama_lengkap_ktp ?? 'Nama Pengguna';
@endphp

<div class="mb-5 sm:mb-6">
    <span class="mb-1 block text-[10px] font-semibold uppercase tracking-wider text-neutral-400 sm:text-xs">DASHBOARD USER</span>
    <h1 class="text-xl font-extrabold tracking-tight text-black sm:text-2xl md:text-3xl">Halo, {{ $userName }}</h1>
    <p class="mt-2 hidden max-w-2xl text-xs leading-relaxed text-neutral-500 sm:block md:text-sm">
        Akses cepat inventaris produksi sinematik yang Anda sewa, pantau waktu sisa penyewaan, dan kelola otorisasi akun.
    </p>
</div>
