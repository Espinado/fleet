@php
    $active = request()->route()->getName();
@endphp

<div class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 shadow-lg z-50">
    <div class="flex justify-around py-2">

        {{-- Dashboard --}}
        <a href="{{ route('driver.dashboard') }}"
           class="flex flex-col items-center text-xs {{ $active === 'driver.dashboard' ? 'text-blue-600 font-semibold' : 'text-gray-500' }}">
            <div class="text-2xl leading-none">🏠</div>
            Главная
        </a>

        {{-- Trip --}}
        @if(!empty($currentTripId))
            <a href="{{ route('driver.trip', $currentTripId) }}"
               class="flex flex-col items-center text-xs {{ $active === 'driver.trip' ? 'text-blue-600 font-semibold' : 'text-gray-500' }}">
                <div class="text-2xl leading-none">🚛</div>
                Рейс
            </a>
        @else
            <div class="flex flex-col items-center text-xs text-gray-300 opacity-50 cursor-not-allowed">
                <div class="text-2xl leading-none">🚛</div>
                Рейс
            </div>
        @endif

        {{-- LOGOUT (CENTER) --}}
        <form method="POST" action="{{ route('driver.logout') }}" class="flex flex-col items-center text-xs">
            @csrf
            <button type="submit"
                class="flex flex-col items-center text-xs text-red-500 hover:text-red-600 font-semibold">
                <div class="text-2xl leading-none">🚪</div>
                Выйти
            </button>
        </form>

    </div>
</div>
