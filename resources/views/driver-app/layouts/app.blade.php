<!DOCTYPE html>
<html lang="ru" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- Favicons --}}
    <link rel="icon" type="image/png" href="/images/icons/fleet-192.png">

<link rel="shortcut icon" href="/images/icons/fleet-192.png">

<!-- PWA -->
<link rel="manifest" href="/driver/manifest.webmanifest">
<link rel="apple-touch-icon" href="/images/icons/fleet-512.png">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

    {{-- Driver App assets (отдельные от основной админки) --}}
    @vite([
        'resources/driver/css/app.css',
        'resources/driver/js/app.js'
    ])

    @livewireStyles

    <title>{{ $title ?? 'Driver App' }}</title>
</head>

<body class="bg-gray-100 h-full">

    {{-- Основной контент --}}
    <div class="min-h-screen pb-20">
        {{ $slot }}
    </div>

    {{-- Нижнее меню --}}
    @php
        $currentTrip = auth()->user()?->driver?->activeTrip()->first();
    @endphp

    @include('driver-app.components.bottombar', [
        'currentTripId' => $currentTrip?->id
    ])

    @livewireScripts
</body>
</html>
