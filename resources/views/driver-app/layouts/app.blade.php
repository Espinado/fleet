<!DOCTYPE html>
<html lang="{{ app()->getLocale() ?? 'lv' }}" class="driver-app-root">
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

<body class="driver-app bg-gray-100 flex flex-col min-w-0 w-full max-w-full">

    {{-- Глобальный спиннер при переходах и любых загрузках --}}
    <div id="global-navigate-overlay" class="fixed inset-0 z-[250] flex items-center justify-center bg-white/90 backdrop-blur-sm hidden" aria-live="polite" aria-label="{{ __('app.please_wait') }}">
        <div class="flex flex-col items-center gap-4 p-8 rounded-2xl bg-white shadow-xl border border-gray-200">
            <svg class="animate-spin h-12 w-12 text-indigo-600 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
            <span class="font-semibold text-gray-800 text-lg">{{ __('app.please_wait') }}</span>
        </div>
    </div>

    <main class="flex flex-col min-h-0 pb-20 px-3 min-w-0 overflow-x-hidden w-full max-w-full">
        <div class="w-full sm:max-w-[520px] sm:mx-auto flex flex-col min-h-0 min-w-0">
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
            document_uploaded: @json(__('app.driver.toast.document_uploaded')),
        };
        function showDriverToast(isError, message) {
            const root = document.getElementById('driver-toast-root');
            if (!root) return;
            const msg = message ?? (isError ? window.driverToastMessages.error : window.driverToastMessages.success);
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
            Livewire.on('driver-toast-document-uploaded', () => showDriverToast(false, window.driverToastMessages.document_uploaded));
        });
    </script>

    @livewireScripts

    <script>
    (function() {
        var overlay = document.getElementById('global-navigate-overlay');
        if (!overlay) return;
        var pending = 0;
        function showOverlay() { overlay.classList.remove('hidden'); }
        function hideOverlay() { overlay.classList.add('hidden'); }
        function done() { pending--; if (pending <= 0) { pending = 0; hideOverlay(); } }
        document.addEventListener('livewire:navigate', function() { pending++; showOverlay(); });
        document.addEventListener('livewire:navigated', done);
        document.addEventListener('livewire:request-finished', done);
        document.addEventListener('submit', function(e) {
            var form = e.target;
            if (form && form.tagName === 'FORM' && form.action && form.action.indexOf('logout') !== -1) {
                showOverlay();
            }
        });
        document.addEventListener('click', function(e) {
            var a = e.target.closest('a');
            if (!a || !a.href) return;
            if (a.target === '_blank' || a.getAttribute('href') === '#' || (a.getAttribute('href') || '').indexOf('javascript:') === 0) return;
            try {
                if (new URL(a.href).origin === window.location.origin) {
                    pending++;
                    showOverlay();
                }
            } catch (_) {}
        });
        function registerRequestHook() {
            if (window.Livewire && typeof window.Livewire.hook === 'function') {
                window.Livewire.hook('request', function(_ref) {
                    var succeed = _ref.succeed, fail = _ref.fail;
                    pending++;
                    showOverlay();
                    succeed(function() { done(); });
                    fail(function() { done(); });
                });
            }
        }
        if (document.readyState === 'loading') {
            document.addEventListener('livewire:init', registerRequestHook, { once: true });
        } else {
            registerRequestHook();
        }
    })();
    </script>

</body>
</html>
