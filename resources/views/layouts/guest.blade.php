<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'Fleet Manager') }}</title>

    @livewireStyles
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans text-gray-900 antialiased bg-gray-100">

    {{-- Спиннер до полной загрузки новой страницы после входа --}}
    <div id="login-navigate-overlay" class="fixed inset-0 z-[300] flex items-center justify-center bg-white/95 backdrop-blur-sm hidden" aria-live="polite" aria-label="{{ __('app.please_wait') }}">
        <div class="flex flex-col items-center gap-4 p-8 rounded-2xl bg-white shadow-xl border border-gray-200">
            <svg class="animate-spin h-12 w-12 text-indigo-600 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="font-semibold text-gray-800 text-lg">{{ __('app.please_wait') }}</span>
        </div>
    </div>

    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0">
        <div>{{-- logo here if needed --}}</div>

        <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md sm:rounded-lg">
            {{ $slot }}
        </div>
    </div>

   @livewireScripts

   <script>
   (function() {
       var overlay = document.getElementById('login-navigate-overlay');
       if (!overlay) return;
       var pending = 0;
       function showOverlay() { overlay.classList.remove('hidden'); }
       function hideOverlay() { overlay.classList.add('hidden'); }
       function done() { pending--; if (pending <= 0) { pending = 0; hideOverlay(); } }

       var form = document.querySelector('form[action*="login"]');
       if (form) form.addEventListener('submit', function() { pending++; showOverlay(); });

       document.addEventListener('livewire:navigate', function() { pending++; showOverlay(); });
       document.addEventListener('livewire:navigated', done);
       document.addEventListener('livewire:request-finished', done);
       document.addEventListener('submit', function(e) {
           var form = e.target;
           if (form && form.tagName === 'FORM' && form.action && form.action.indexOf('logout') !== -1) {
               showOverlay();
           }
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

       window.addEventListener('pageshow', function(e) {
           if (e.persisted) { pending = 0; hideOverlay(); }
       });
   })();
   </script>

</body>
</html>
