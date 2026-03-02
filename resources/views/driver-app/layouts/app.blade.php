<!DOCTYPE html>
<html lang="ru" class="h-full">
<head>
    <meta charset="utf-8">

    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">

    <title>{{ $title ?? 'Driver App' }}</title>

    <link rel="icon" href="/images/icons/icon-192.png">
    <link rel="apple-touch-icon" href="/images/icons/icon-512.png">

    <link rel="manifest" href="/driver/manifest.webmanifest">

    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

    <meta name="theme-color" content="#0066ff">

    @vite([
        'resources/driver/css/app.css',
        'resources/driver/js/app.js'
    ])

    @livewireStyles
</head>

<body class="bg-gray-100 min-h-screen">

    <main class="min-h-screen min-h-[100dvh] pb-20 px-3 flex">
        <div class="mx-auto w-full max-w-[520px] flex-1 flex flex-col">
            {{ $slot }}
        </div>
    </main>

    @php
        $currentTrip = auth()->user()?->driver?->activeTrip()->first();
    @endphp

    @include('driver-app.components.bottombar', [
        'currentTripId' => $currentTrip?->id
    ])

    <script src="/vendor/livewire/livewire.js" data-navigate-once></script>

</body>
</html>
