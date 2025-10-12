<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Fleet Manager') }} - @yield('title')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-gray-100 h-screen flex overflow-hidden">

    <!-- Sidebar -->
    <aside class="w-64 bg-white shadow-md hidden md:block">
        <div class="p-4 text-xl font-bold border-b">
            🚚 Fleet Manager
        </div>
        <nav class="p-4 space-y-2">
            <a href="{{ route('dashboard') }}" class="block px-3 py-2 rounded hover:bg-gray-200">📊 Dashboard</a>
            <a href="{{ route('drivers.index') }}" class="block px-3 py-2 rounded hover:bg-gray-200">👨‍✈️ Drivers</a>
            <a href="{{ route('trucks.index') }}" class="block px-3 py-2 rounded hover:bg-gray-200">🚛 Trucks</a>
            <a href="{{ route('trailers.index') }}" class="block px-3 py-2 rounded hover:bg-gray-200">🚚 Trailers</a>
        </nav>
    </aside>

    <!-- Main content -->
    <div class="flex-1 flex flex-col">
        <!-- Top bar -->
        <header class="h-16 bg-white shadow flex items-center justify-between px-6">
            <h1 class="text-lg font-semibold">@yield('title', 'Dashboard')</h1>
            <div>
                <span class="text-gray-600">Hello, Admin</span>
            </div>
        </header>

        <!-- Content -->
        <main class="flex-1 overflow-y-auto p-6">
            {{-- Вставка Livewire Page Component --}}
            @if (isset($slot))
                {{ $slot }}
            @else
                @yield('content')
            @endif
        </main>
    </div>

   @livewireScripts
    @stack('scripts')
</body>
</html>
