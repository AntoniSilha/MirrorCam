<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-[#f9f9f9]">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'MirrorCam - Dashboard User')</title>

    <!-- Google Font: Montserrat -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400;1,600&display=swap" rel="stylesheet">

    <!-- Styles / Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        *, ::before, ::after {
            font-family: 'Montserrat', ui-sans-serif, system-ui, sans-serif !important;
        }
        body {
            background-color: #f9f9f9;
            color: #000000;
        }
    </style>
</head>
<body class="min-h-screen bg-[#f9f9f9] text-[#000000] antialiased flex flex-col md:flex-row">
    <!-- Sidebar Navigation -->
    <x-navigation.sidebar :user="$user ?? []" />

    <!-- Main Content Area -->
    <div class="flex-1 min-h-screen flex flex-col bg-[#f9f9f9] overflow-x-hidden">
        <!-- Topbar Header -->
        <x-navigation.topbar />

        <!-- Notification Banner -->
        @if (session('status'))
            <div class="px-6 md:px-12 pt-4">
                <div class="bg-[#dcfce7] border border-[#16a34a]/30 text-[#16a34a] px-4 py-3 rounded-sm text-sm flex items-center justify-between shadow-xs">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span class="font-medium">{{ session('status') }}</span>
                    </div>
                    <button type="button" onclick="this.parentElement.remove()" class="text-[#16a34a] hover:opacity-75">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        @endif

        @if (isset($errors) && $errors->any())
            <div class="px-6 md:px-12 pt-4">
                <div class="bg-[#ffdad6] border border-[#ba1a1a]/30 text-[#ba1a1a] px-4 py-3 rounded-sm text-sm flex items-center justify-between shadow-xs">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <span class="font-medium">{{ $errors->first() }}</span>
                    </div>
                    <button type="button" onclick="this.parentElement.remove()" class="text-[#ba1a1a] hover:opacity-75">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        @endif

        <!-- Page Main Content Slot / Yield -->
        <main class="flex-1 px-6 md:px-12 py-6 max-w-7xl w-full">
            @yield('content')
            {{ $slot ?? '' }}
        </main>
    </div>
</body>
</html>
