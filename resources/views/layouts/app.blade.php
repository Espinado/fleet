<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    {{-- PWA for Admin (НЕ laravelPWA) --}}
    <link rel="manifest" href="/admin/manifest.webmanifest">

    {{-- Apple PWA meta --}}
    <link rel="apple-touch-icon" href="/images/icons/fleet-512.png">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Fleet Manager') }} - @yield('title')</title>

    {{-- jQuery + Select2 (для всех select2-полей в админке) --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    {{-- Livewire styles --}}
    @livewireStyles

    {{-- Vite (только админские ассеты) --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100 h-screen flex overflow-hidden relative">

    {{-- Глобальный спиннер при переходах (до полной загрузки новой страницы) --}}
    <div id="global-navigate-overlay" class="fixed inset-0 z-[250] flex items-center justify-center bg-white/90 backdrop-blur-sm hidden" aria-live="polite" aria-label="{{ __('app.please_wait') }}">
        <div class="flex flex-col items-center gap-4 p-8 rounded-2xl bg-white shadow-xl border border-gray-200">
            <svg class="animate-spin h-12 w-12 text-indigo-600 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
            <span class="font-semibold text-gray-800 text-lg">{{ __('app.please_wait') }}</span>
        </div>
    </div>

    {{-- ===== Мобильная подложка ===== --}}
    <div id="overlay"
         class="fixed inset-0 bg-black/50 z-30 hidden opacity-0 transition-opacity duration-300 md:hidden"></div>

    {{-- ===== Sidebar ===== --}}
    <aside id="sidebar"
           class="w-64 bg-white shadow-md fixed md:static inset-y-0 left-0 transform -translate-x-full md:translate-x-0 transition-transform duration-300 z-40">
        <div class="p-4 border-b flex justify-between items-center gap-2">
            <div class="flex items-center gap-2 min-w-0">
                <img src="{{ asset('images/icons/fleet.png') }}" alt="" class="rounded-lg shrink-0 object-cover w-8 h-8 md:w-10 md:h-10">
                <span class="text-lg font-bold text-gray-800 truncate">{{ config('app.name', 'Fleet Manager') }}</span>
            </div>
            <button id="closeSidebar" class="md:hidden text-gray-500 hover:text-gray-700 text-xl shrink-0">✖</button>
        </div>

        @php
            $navBase   = 'block px-3 py-2 rounded transition';
            $navIdle   = 'text-gray-700 hover:bg-gray-200';
            $navActive = 'bg-blue-50 text-blue-700 font-semibold ring-1 ring-blue-200';

            // Stats dropdown behavior + active states
            $statsOpen          = request()->routeIs('stats.*');
            $statsOverviewActive = request()->routeIs('stats.index');
            $statsEventsActive   = request()->routeIs('stats.events') || request()->routeIs('stats.events.*');
             $transportOpen = request()->routeIs('trucks.*') || request()->routeIs('trailers.*');

    $trucksActive = request()->routeIs('trucks.*');
    $trailersActive = request()->routeIs('trailers.*');
        @endphp

        <nav class="p-4 space-y-2">
            <a href="{{ route('dashboard') }}" wire:navigate
               @class([$navBase, request()->routeIs('dashboard') ? $navActive : $navIdle])>
                📊 {{ __('app.nav.dashboard') }}
            </a>

            <a href="{{ route('drivers.index') }}" wire:navigate
               @class([$navBase, request()->routeIs('drivers.*') ? $navActive : $navIdle])>
                👨‍✈️ {{ __('app.nav.drivers') }}
            </a>

            <details class="rounded" @if($transportOpen) open @endif>
    <summary
        class="{{ $navBase }} cursor-pointer list-none flex items-center justify-between
               {{ $transportOpen ? $navActive : $navIdle }}"
    >
        <span>🚚 {{ __('app.nav.transport') }}</span>
        <span class="text-xs opacity-70">
            @if($transportOpen) ▲ @else ▼ @endif
        </span>
    </summary>

    <div class="mt-1 ml-3 space-y-1">

        <a href="{{ route('trucks.index') }}" wire:navigate
           @class([$navBase, $trucksActive ? $navActive : $navIdle])>
            🚛 {{ __('app.nav.trucks') }}
        </a>

        <a href="{{ route('trailers.index') }}" wire:navigate
           @class([$navBase, $trailersActive ? $navActive : $navIdle])>
            🚚 {{ __('app.nav.trailers') }}
        </a>

    </div>
</details>

            <a href="{{ route('clients.index') }}" wire:navigate
               @class([$navBase, request()->routeIs('clients.*') ? $navActive : $navIdle])>
                🏢 {{ __('app.nav.clients') }}
            </a>

            <a href="{{ route('trips.index') }}" wire:navigate
               @class([$navBase, request()->routeIs('trips.*') ? $navActive : $navIdle])>
                🧭 {{ __('app.nav.trips') }}
            </a>

            <a href="{{ route('map.index') }}" wire:navigate
               @class([$navBase, request()->routeIs('map.*') ? $navActive : $navIdle])>
                🗺️ {{ __('app.nav.map') }}
            </a>

            {{-- ✅ STATS (dropdown) --}}
            <details class="rounded" @if($statsOpen) open @endif>
                <summary
                    class="{{ $navBase }} cursor-pointer list-none flex items-center justify-between
                           {{ $statsOpen ? $navActive : $navIdle }}"
                >
                    <span>📊 {{ __('app.nav.stats') }}</span>
                    <span class="text-xs opacity-70">@if($statsOpen) ▲ @else ▼ @endif</span>
                </summary>

                <div class="mt-1 ml-3 space-y-1">
                    {{-- существующий stats.index -> внутрь как Overview --}}
                    <a href="{{ route('stats.index') }}" wire:navigate
                       @class([$navBase, $statsOverviewActive ? $navActive : $navIdle])>
                        📈 {{ __('app.nav.stats_overview') }}
                    </a>

                    {{-- новое подменю --}}
                    <a href="{{ route('stats.events') }}" wire:navigate
                       @class([$navBase, $statsEventsActive ? $navActive : $navIdle])>
                        🧾 {{ __('app.nav.stats_events') }}
                    </a>
                </div>
            </details>

            <a href="{{ route('invoices.index') }}" wire:navigate
               @class([$navBase, request()->routeIs('invoices.*') ? $navActive : $navIdle])>
                💶 {{ __('app.nav.invoices') }}
            </a>

            @if(config('webpush.vapid.public_key'))
            <div class="mt-4 pt-4 border-t border-gray-200">
                <button type="button" id="btn-enable-push"
                        class="{{ $navBase }} {{ $navIdle }} w-full text-left flex items-center gap-2">
                    🔔 Включить уведомления
                </button>
                <p id="push-status" class="text-xs text-gray-500 mt-1 px-3 hidden" role="status" aria-live="polite"></p>
            </div>
            @endif
        </nav>
    </aside>

    {{-- ===== Main content ===== --}}
    <div class="flex-1 flex flex-col min-h-0 w-0 min-w-0">

        {{-- ===== Header ===== --}}
        <header class="h-16 bg-white shadow flex items-center justify-between px-6 relative z-40 md:z-auto shrink-0">
            <button id="openSidebar" type="button" class="md:hidden text-gray-600 hover:text-gray-900 text-2xl focus:outline-none shrink-0">
                ☰
            </button>

            <h1 class="text-lg font-semibold">{{ $title ?? __('app.nav.dashboard') }}</h1>

            <div class="relative group">
                <button class="flex items-center space-x-2 text-gray-700 hover:text-gray-900 focus:outline-none">
                    <span>{{ __('app.nav.hello', ['name' => Auth::user()->name]) }}</span>
                    <svg class="w-4 h-4 text-gray-500 group-hover:text-gray-700"
                         fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <div class="absolute right-0 mt-2 w-40 bg-white border rounded-xl shadow-xl
                            opacity-0 group-hover:opacity-100 transition ease-out duration-200 z-50">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                                class="block w-full text-left px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">
                            {{ __('app.nav.logout') }}
                        </button>
                    </form>
                </div>
            </div>
        </header>

        {{-- ===== Page Content ===== --}}
        <main class="flex-1 overflow-y-auto p-6">
            {{ $slot ?? '' }}
            @yield('content')
        </main>
    </div>

    {{-- Sidebar JS: делегирование кликов, чтобы кнопка работала после Livewire morph (таблицы и др.) --}}
    <script>
        (function() {
            function openSidebarMenu() {
                var sidebar = document.getElementById('sidebar');
                var overlay = document.getElementById('overlay');
                if (sidebar) sidebar.classList.remove('-translate-x-full');
                if (overlay) {
                    overlay.classList.remove('hidden');
                    requestAnimationFrame(function() { overlay.classList.add('opacity-100'); });
                }
            }
            function closeSidebarMenu() {
                var sidebar = document.getElementById('sidebar');
                var overlay = document.getElementById('overlay');
                if (sidebar) sidebar.classList.add('-translate-x-full');
                if (overlay) {
                    overlay.classList.remove('opacity-100');
                    overlay.addEventListener('transitionend', function() { overlay.classList.add('hidden'); }, { once: true });
                }
            }
            document.addEventListener('click', function(e) {
                if (e.target.closest('#openSidebar')) {
                    e.preventDefault();
                    e.stopPropagation();
                    openSidebarMenu();
                    return;
                }
                if (e.target.closest('#closeSidebar') || e.target.id === 'overlay') {
                    closeSidebarMenu();
                }
            });
        })();
    </script>

    {{-- При 419 (Page expired) редирект на логин — перехват до загрузки Livewire --}}
    <script>
    (function() {
        window.__loginRedirectUrl = @json(route('login'));
        var f = window.fetch;
        if (typeof f !== 'function') return;
        window.fetch = function() {
            return f.apply(this, arguments).then(function(r) {
                if (r.status === 419) {
                    var url = r.headers.get('X-Redirect-To') || window.__loginRedirectUrl || '/login';
                    window.location.href = url;
                    return Promise.reject(new Error('Session expired'));
                }
                return r;
            });
        };
    })();
    </script>

    {{-- Livewire scripts --}}
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
        document.addEventListener('livewire:init', function() {
            if (!window.Livewire || typeof window.Livewire.hook !== 'function') return;

            window.Livewire.hook('request', function({ succeed, fail }) {
                var finalized = false;
                var finalize = function() {
                    if (finalized) return;
                    finalized = true;
                    done();
                };

                succeed(finalize);
                fail(finalize);
            });
        });
        document.addEventListener('submit', function(e) {
            var form = e.target;
            if (form && form.tagName === 'FORM' && form.action && form.action.indexOf('logout') !== -1) {
                pending++;
                showOverlay();
            }
        });
        document.addEventListener('click', function(e) {
            var a = e.target.closest('a');
            if (a && a.href) {
                if (a.target !== '_blank' && a.getAttribute('href') !== '#' && (a.getAttribute('href') || '').indexOf('javascript:') !== 0) {
                    try {
                        if (new URL(a.href).origin === window.location.origin) {
                            pending++;
                            showOverlay();
                        }
                    } catch (_) {}
                }
                return;
            }
            var btn = e.target.closest('button[type="submit"], input[type="submit"], [data-loading-overlay]');
            if (btn) {
                var form = btn.closest('form');
                if (form && (form.getAttribute('wire:submit') || form.getAttribute('wire:submit.prevent') || btn.getAttribute('data-loading-overlay'))) {
                    pending++;
                    showOverlay();
                }
            }
        });
        document.addEventListener('change', function(e) {
            if (e.target && e.target.tagName === 'INPUT' && e.target.type === 'file' && e.target.files && e.target.files.length > 0) {
                pending++;
                showOverlay();
            }
        });
    })();
    </script>

    {{-- Select2 init (работает и с Livewire). После каждого обновления Livewire (morph) переинициализируем Select2. Маленький спиннер у поля при отправке значения. --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const initSelect2 = () => {
                $('.js-select2').each(function () {
                    const $el = $(this);
                    const el = $el[0];
                    if (!el || !document.body.contains(el)) return;

                    // Если Select2 уже инициализирован — уничтожаем только если элемент ещё в DOM (после смены страны опции обновились, виджет пересоздаём)
                    if ($el.data('select2')) {
                        try {
                            $el.off('change.lwSelect2');
                            $el.select2('destroy');
                        } catch (e) {}
                        $el.removeData('select2');
                        $el.removeData('lw-select2-bound');
                    }

                    const $parent =
                        $el.closest('.select2-parent').length
                            ? $el.closest('.select2-parent')
                            : $('body');

                    $el.select2({
                        width: 'resolve',
                        placeholder: $el.data('placeholder') || undefined,
                        allowClear: !!$el.data('allow-clear'),
                        tags: !!$el.data('tags'),
                        dropdownParent: $parent
                    });

                    // Синхронизация с Livewire при смене значения + маленький спиннер у поля
                    if (window.Livewire) {
                        $el.off('change.lwSelect2').on('change.lwSelect2', function (e) {
                            const target = e.target;
                            const model =
                                target.getAttribute('wire:model.live') ||
                                target.getAttribute('wire:model') ||
                                target.getAttribute('wire:model.defer') ||
                                target.getAttribute('wire:model.blur');

                            if (!model) return;

                            const componentRoot = target.closest('[wire\\:id]');
                            if (!componentRoot) return;

                            const componentId = componentRoot.getAttribute('wire:id');
                            if (!componentId) return;

                            const component = window.Livewire.find(componentId);
                            if (!component || typeof component.set !== 'function') return;

                            var wrapper = target.closest('div');
                            if (wrapper && !wrapper.classList.contains('select2-spinner-wrapper')) {
                                wrapper.classList.add('select2-spinner-wrapper', 'relative');
                                var spinner = wrapper.querySelector('.select2-inline-spinner');
                                if (!spinner) {
                                    spinner = document.createElement('span');
                                    spinner.className = 'select2-inline-spinner absolute right-2 top-1/2 -translate-y-1/2 h-4 w-4 border-2 border-gray-200 border-t-blue-500 rounded-full animate-spin pointer-events-none';
                                    spinner.setAttribute('aria-hidden', 'true');
                                    wrapper.appendChild(spinner);
                                }
                                spinner.classList.remove('hidden');
                            }

                            component.set(model, $el.val());
                            if (target.blur) target.blur();
                        });
                    }
                });
                document.querySelectorAll('.select2-inline-spinner').forEach(function(el) { el.classList.add('hidden'); });
            };

            const scheduleSelect2 = () => {
                setTimeout(initSelect2, 0);
            };

            initSelect2();

            if (window.Livewire && typeof window.Livewire.hook === 'function') {
                window.Livewire.hook('morphed', scheduleSelect2);
            }
        });
    </script>

    {{-- Push: VAPID key + script (для подписки на пуши) --}}
    @if(config('webpush.vapid.public_key'))
    <script>
        window.VAPID_PUBLIC_KEY = @json(config('webpush.vapid.public_key'));
        window.fleetPushMessage = function (msg) {
            var el = document.getElementById('push-status');
            if (el) { el.textContent = msg; el.classList.remove('hidden'); }
        };
    </script>
    <script src="/pwa/push.js"></script>
    @endif

    @stack('scripts')

    {{-- Root SW (scope /) — для Web Push на /dashboard и всех страницах --}}
    <script>
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/serviceworker.js', { scope: '/' })
                .then(function () { console.log("Push SW (root) registered"); })
                .catch(function (e) { console.warn("Push SW error:", e); });
        }
    </script>
    {{-- Кнопка «Включить уведомления» — делегирование + обновление актуальных элементов из DOM --}}
    @if(config('webpush.vapid.public_key'))
    <script>
        (function() {
            function updateUi(btnText, statusText) {
                var btn = document.getElementById('btn-enable-push');
                var statusEl = document.getElementById('push-status');
                if (btn) { btn.textContent = btnText; btn.disabled = false; }
                if (statusEl) { statusEl.textContent = statusText || ''; statusEl.classList.remove('hidden'); }
            }
            function runSubscribe(btn) {
                if (!btn || !window.subscribeForPush) {
                    updateUi('🔔 Включить уведомления', 'Ошибка: скрипт не загружен. Обновите страницу (F5).');
                    return;
                }
                var origText = btn.textContent;
                btn.disabled = true;
                updateUi('Проверка…', '');
                window.fleetPushStatus = function (s) { updateUi(s || origText, s || ''); };
                window.fleetPushMessage = function (s) { updateUi(document.getElementById('btn-enable-push') ? document.getElementById('btn-enable-push').textContent : origText, s || ''); };
                window.fleetPushSuccess = function () { updateUi('🔔 Уведомления включены', 'Готово.'); };
                window.subscribeForPush().then(function (ok) {
                    if (ok) {
                        updateUi('🔔 Уведомления включены', 'Готово.');
                    } else {
                        var statusEl = document.getElementById('push-status');
                        var msg = (statusEl && statusEl.textContent) ? statusEl.textContent : 'Не удалось. Разрешите уведомления: замок в адресной строке → Настройки сайта → Уведомления → Разрешить.';
                        updateUi('🔔 Включить уведомления', msg);
                    }
                }).catch(function (err) {
                    var errMsg = (err && err.message) ? err.message : String(err);
                    console.error('Push subscribe error', err);
                    updateUi('🔔 Включить уведомления', 'Ошибка: ' + errMsg);
                });
            }
            document.body.addEventListener('click', function (e) {
                var btn = e.target && (e.target.id === 'btn-enable-push' ? e.target : e.target.closest && e.target.closest('#btn-enable-push'));
                if (btn) {
                    e.preventDefault();
                    runSubscribe(btn);
                }
            });
        })();
    </script>
    @endif

</body>
</html>
