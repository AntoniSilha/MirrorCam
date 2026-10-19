<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'MirrorCam') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body class="font-sans text-gray-900 antialiased bg-gray-50 overflow-hidden">
    <header class="flex justify-between items-center p-5 bg-white border-b">
        <div class="logo flex items-center gap-2">
            <img src="{{ asset('images/logo.png') }}" alt="MirrorCam" class="h-8">
        </div>
        <a href="/" class="text-sm font-semibold text-gray-600 hover:text-black flex items-center gap-1">
            Kembali ke Beranda &rarr;
        </a>
    </header>

    <div class="min-h-screen flex flex-col items-center pt-10 sm:pt-16 pb-20">
        {{ $slot }}
    </div>

    @livewireScripts
</body>

</html>
