@props(['user' => []])

@php
    $userName = $user['nama_lengkap_ktp'] ?? auth()->user()?->nama_lengkap_ktp ?? 'Nama Pengguna';
@endphp

<div class="mb-6">
    <span class="block text-xs font-semibold text-neutral-400 tracking-wider uppercase mb-1">DASHBOARD USER</span>
    <h1 class="text-2xl md:text-3xl font-extrabold text-black tracking-tight">Halo, {{ $userName }}</h1>
    <p class="text-xs md:text-sm text-neutral-500 mt-2 max-w-2xl leading-relaxed">
        Akses cepat inventaris produksi sinematik yang Anda sewa, pantau waktu sisa penyewaan, dan kelola otorisasi akun.
    </p>
</div>
