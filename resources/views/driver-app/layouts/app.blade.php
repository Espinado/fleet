<!DOCTYPE html>
<html lang="{{ app()->getLocale() ?? 'lv' }}" class="h-full">
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

    {{-- Global driver toast: "Выполнено" / "Ошибка! Свяжитесь с администратором" (locale from SetDriverLocale) --}}
    <div id="driver-toast-root" class="fixed bottom-24 left-4 right-4 z-50 pointer-events-none flex justify-center" aria-live="polite"></div>

    <script>
        window.driverToastMessages = {
            success: @json(__('app.driver.toast.success')),
            error: @json(__('app.driver.toast.error')),
        };
        function showDriverToast(isError) {
            const root = document.getElementById('driver-toast-root');
            if (!root) return;
            const msg = isError ? window.driverToastMessages.error : window.driverToastMessages.success;
            const el = document.createElement('div');
            el.setAttribute('role', 'alert');
            el.className = (isError ? 'bg-red-600' : 'bg-green-600') + ' text-white text-sm font-medium px-4 py-3 rounded-lg shadow-lg';
            el.textContent = msg;
            root.innerHTML = '';
            root.appendChild(el);
            const duration = isError ? 3500 : 2500;
            setTimeout(() => { el.style.opacity = '0'; el.style.transition = 'opacity 0.2s'; setTimeout(() => el.remove(), 200); }, duration);
        }
        document.addEventListener('livewire:init', () => {
            Livewire.on('driver-toast-success', () => showDriverToast(false));
            Livewire.on('driver-toast-error', () => showDriverToast(true));
        });
    </script>

    <script src="/vendor/livewire/livewire.js" data-navigate-once></script>

</body>
</html>
