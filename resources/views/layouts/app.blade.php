<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Fleet Manager') }} - @yield('title')</title>

    {{-- ✅ Livewire styles --}}
    @livewireStyles

    {{-- ✅ Vite assets --}}
    @if(app()->environment('production'))
        @php
            $manifestPath = public_path('build/manifest.json');
            $manifest = file_exists($manifestPath) ? json_decode(file_get_contents($manifestPath), true) : [];
        @endphp

        @if(!empty($manifest))
            <link rel="stylesheet" href="{{ asset('build/' . $manifest['resources/css/app.css']['file']) }}">
            <script defer src="{{ asset('build/' . $manifest['resources/js/app.js']['file']) }}"></script>
        @endif
    @else
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>

<body class="bg-gray-100 h-screen flex overflow-hidden">

    {{-- === Sidebar === --}}
    <aside class="w-64 bg-white shadow-md hidden md:block">
        <div class="p-4 text-xl font-bold border-b">🚚 Fleet Manager</div>
        <nav class="p-4 space-y-2">
            <a href="{{ route('dashboard') }}" class="block px-3 py-2 rounded hover:bg-gray-200">📊 Dashboard</a>
            <a href="{{ route('drivers.index') }}" class="block px-3 py-2 rounded hover:bg-gray-200">👨‍✈️ Drivers</a>
            <a href="{{ route('trucks.index') }}" class="block px-3 py-2 rounded hover:bg-gray-200">🚛 Trucks</a>
            <a href="{{ route('trailers.index') }}" class="block px-3 py-2 rounded hover:bg-gray-200">🚚 Trailers</a>
            <a href="{{ route('clients.index') }}" class="block px-3 py-2 rounded hover:bg-gray-200">🏢 Clients</a>
            <a href="{{ route('trips.index') }}" class="block px-3 py-2 rounded hover:bg-gray-200">🧭 Trips</a>
        </nav>
    </aside>

    {{-- === Main Content === --}}
    <div class="flex-1 flex flex-col">

        {{-- === Header === --}}
        <header class="h-16 bg-white shadow flex items-center justify-between px-6">
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

        {{-- === Content === --}}
        <main class="flex-1 overflow-y-auto p-6">
            @if (isset($slot))
                {{ $slot }}
            @else
                @yield('content')
            @endif
        </main>
    </div>

    {{-- ✅ Livewire scripts --}}
    {{-- Отключаем navigate для корректной работы wire:model на селектах --}}
    @livewireScripts(['navigate' => false])

    {{-- Если нужны доп. скрипты --}}
    @stack('scripts')

</body>
</html>
