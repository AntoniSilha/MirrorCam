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

    <x-navigation.topbar />

    <div class="min-h-screen flex flex-col items-center pt-10 sm:pt-16 pb-20">
        {{ $slot }}
    </div>

    @livewireScripts
</body>

</html>
