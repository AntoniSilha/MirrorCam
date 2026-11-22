<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-[#f9f9f9] scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'MirrorCam — Sewa Kamera, Lensa & Studio Lintas Tenant')</title>
    <meta name="description" content="Platform sewa kamera, lensa cinema, lighting, audio & studio lintas tenant terpercaya di Indonesia dengan verifikasi keamanan terpadu.">

    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">

    <x-fonts.montserrat />

    <!-- Styles / Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Alpine.js for lightweight client interactivity (tabs, accordion, modal) -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>

    <style>
        body {
            background-color: #f9f9f9;
            color: #000000;
        }
        /* Strict MirrorCam border radius 4px */
        .rounded-mc,
        .rounded-[4px] {
            border-radius: 4px !important;
        }
        input, select, textarea, button {
            border-radius: 4px;
        }
    </style>
</head>
<body class="min-h-screen bg-[#f9f9f9] text-[#000000] antialiased flex flex-col font-sans selection:bg-[#000000] selection:text-[#ffffff]">
    <!-- Top Header / Navbar -->
    <x-public.navbar :user="$user ?? null" />

    <!-- Main Content Area -->
    <main class="flex-1 w-full">
        @yield('content')
        {{ $slot ?? '' }}
    </main>

    <!-- Footer Component -->
    <x-public.footer />
</body>
</html>
