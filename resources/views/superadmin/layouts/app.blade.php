<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-[#f9f9f9]">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'SuperAdmin — MirrorCam Multi-Tenant Rental')</title>

    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">

    <x-fonts.montserrat />

    <!-- Styles / Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>

    <style>
        body {
            background-color: #f9f9f9;
            color: #000000;
        }
        .rounded-[4px], input, select, textarea, button, table {
            border-radius: 4px !important;
        }
    </style>
</head>
<body class="min-h-screen bg-[#f9f9f9] text-[#000000] antialiased flex flex-row font-sans selection:bg-[#000000] selection:text-[#ffffff]">

    <!-- Sidebar Kiri Tetap (w-64, background #ffffff) -->
    @include('superadmin.components.sidebar')

    <!-- Area Konten Utama Kanan (flex-1, background #f9f9f9) -->
    <div class="flex-1 min-h-screen flex flex-col min-w-0">
        <!-- Top Bar Kanan Atas: Tautan Kembali ke Beranda -->
        <header class="w-full bg-[#ffffff] border-b border-[#e5e5e5] px-6 sm:px-10 py-3.5 flex items-center justify-end">
            <a
                href="{{ route('home') }}"
                class="inline-flex items-center gap-2 text-xs font-semibold text-gray-700 hover:text-black transition"
            >
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                <span>Kembali ke Beranda</span>
            </a>
        </header>

        <!-- Main Body -->
        <main class="flex-1 p-6 sm:p-10 max-w-7xl w-full">
            <!-- Flash Notification Status -->
            @if (session('status'))
                <div class="mb-6 bg-[#dcfce7] border border-[#bbf7d0] text-[#16a34a] px-4 py-3 rounded-[4px] text-xs font-semibold flex items-center justify-between shadow-xs">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span>{{ session('status') }}</span>
                    </div>
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-6 bg-[#ffdad6] border border-[#fecaca] text-[#ba1a1a] px-4 py-3 rounded-[4px] text-xs font-semibold shadow-xs">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

</body>
</html>
