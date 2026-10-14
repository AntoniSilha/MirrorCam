@extends('layouts.user-dashboard', ['user' => $user])

@section('title', 'Keamanan Akun & Kata Sandi - MirrorCam')

@section('content')
    <div class="w-full">
        <!-- Page Header -->
        <div class="mb-6">
            <h1 class="text-2xl md:text-3xl font-extrabold text-black tracking-tight">Keamanan Akun & Kata Sandi</h1>
            <p class="text-xs md:text-sm text-neutral-500 mt-1.5">Kelola kata sandi akun</p>
        </div>

        <!-- Form Card Container -->
        <div class="bg-white rounded-[8px] p-6 md:p-8 border border-neutral-200/90 shadow-2xs">
            <!-- Google & Security Notice -->
            <x-security.google-notice :isGoogleAccount="$isGoogleAccount" :hasPassword="$hasPassword" />

            <!-- Reset / Set Password Form -->
            <form method="POST" action="{{ route('customer.security.change-password') }}" id="password-form" class="space-y-4">
                @csrf
                @method('PUT')

                <!-- Kata Sandi Saat Ini (Hanya jika akun memiliki kata sandi aktif) -->
                @if ($hasPassword)
                    <x-security.input-password
                        id="current_password"
                        name="current_password"
                        label="Kata Sandi Saat Ini"
                        placeholder="Masukkan kata sandi lama Anda"
                        topLinkText="Lupa kata sandi saat ini?"
                        topLinkUrl="{{ Route::has('password.request') ? route('password.request') : '#' }}"
                        :required="true"
                    />
                @endif

                <!-- Kata Sandi Baru -->
                <x-security.input-password
                    id="new-password"
                    name="password"
                    label="Kata Sandi Baru"
                    placeholder="Buat kombinasi kata sandi baru"
                    :required="true"
                />

                <!-- Indikator Kekuatan Sandi -->
                <x-security.password-meter targetId="new-password" />

                <!-- Konfirmasi Kata Sandi Baru -->
                <x-security.input-password
                    id="password_confirmation"
                    name="password_confirmation"
                    label="Konfirmasi Kata Sandi Baru"
                    placeholder="Ulangi kata sandi baru"
                    :required="true"
                />

                <!-- Form Action Buttons -->
                <div class="flex items-center gap-3 pt-3">
                    <!-- Simpan Kata Sandi Baru -->
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#000000] hover:bg-neutral-800 text-[#ffffff] text-xs font-semibold rounded-[4px] transition shadow-xs cursor-pointer">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                        </svg>
                        <span>Simpan Kata Sandi Baru</span>
                    </button>

                    <!-- Batalkan Button -->
                    <button type="button"
                            onclick="document.getElementById('password-form').reset(); window.location.href='{{ route('customer.orders.index') }}'"
                            class="inline-flex items-center px-5 py-2.5 bg-[#f3f4f6] hover:bg-neutral-200 text-[#000000] text-xs font-semibold rounded-[4px] transition cursor-pointer">
                        Batalkan
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
