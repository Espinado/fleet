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
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'Fleet Manager') }} - @yield('title')</title>

    {{-- Livewire styles --}}
    @livewireStyles

    {{-- Vite (только админские ассеты) --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100 h-screen flex overflow-hidden relative">

    {{-- ===== Мобильная подложка ===== --}}
    <div id="overlay"
         class="fixed inset-0 bg-black/50 z-30 hidden opacity-0 transition-opacity duration-300 md:hidden"></div>

    {{-- ===== Sidebar ===== --}}
    <aside id="sidebar"
           class="w-64 bg-white shadow-md fixed md:static inset-y-0 left-0 transform -translate-x-full md:translate-x-0 transition-transform duration-300 z-40">
        <div class="p-4 text-xl font-bold border-b flex justify-between items-center">
            🚚 Fleet Manager
            <button id="closeSidebar" class="md:hidden text-gray-500 hover:text-gray-700 text-xl">✖</button>
        </div>

        <nav class="p-4 space-y-2">
            <a href="{{ route('dashboard') }}" class="block px-3 py-2 rounded hover:bg-gray-200">📊 Dashboard</a>
            <a href="{{ route('drivers.index') }}" class="block px-3 py-2 rounded hover:bg-gray-200">👨‍✈️ Drivers</a>
            <a href="{{ route('trucks.index') }}" class="block px-3 py-2 rounded hover:bg-gray-200">🚛 Trucks</a>
            <a href="{{ route('trailers.index') }}" class="block px-3 py-2 rounded hover:bg-gray-200">🚚 Trailers</a>
            <a href="{{ route('clients.index') }}" class="block px-3 py-2 rounded hover:bg-gray-200">🏢 Clients</a>
            <a href="{{ route('trips.index') }}" class="block px-3 py-2 rounded hover:bg-gray-200">🧭 Trips</a>
        </nav>
    </aside>

    {{-- ===== Main content ===== --}}
    <div class="flex-1 flex flex-col">

        {{-- ===== Header ===== --}}
        <header class="h-16 bg-white shadow flex items-center justify-between px-6">
            <button id="openSidebar" class="md:hidden text-gray-600 hover:text-gray-900 text-2xl focus:outline-none">
                ☰
            </button>

            <h1 class="text-lg font-semibold">@yield('title', 'Dashboard')</h1>

            <div class="relative group">
                <button class="flex items-center space-x-2 text-gray-700 hover:text-gray-900 focus:outline-none">
                    <span>Hello, {{ Auth::user()->name }}</span>
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
                            Logout
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

    {{-- Sidebar JS --}}
    <script>
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');
        const openBtn = document.getElementById('openSidebar');
        const closeBtn = document.getElementById('closeSidebar');

        function openSidebarMenu() {
            sidebar.classList.remove('-translate-x-full');
            overlay.classList.remove('hidden');
            requestAnimationFrame(() => overlay.classList.add('opacity-100'));
        }

        function closeSidebarMenu() {
            sidebar.classList.add('-translate-x-full');
            overlay.classList.remove('opacity-100');
            overlay.addEventListener('transitionend', () => overlay.classList.add('hidden'), { once: true });
        }

        openBtn?.addEventListener('click', openSidebarMenu);
        closeBtn?.addEventListener('click', closeSidebarMenu);
        overlay?.addEventListener('click', closeSidebarMenu);
    </script>

    {{-- Livewire scripts --}}
    @livewireScripts

    {{-- Push notifications --}}
    <script src="/pwa/push.js"></script>

    @stack('scripts')

    {{-- Register Service Worker only for admin --}}
    <script>
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/admin/serviceworker.js', { scope: '/admin/' })
                .then(r => console.log("Admin SW loaded"))
                .catch(e => console.warn("Admin SW error:", e));
        }
    </script>

</body>
</html>
