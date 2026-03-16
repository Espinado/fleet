<div class="p-4 sm:p-6 max-w-4xl mx-auto">
    @if(session('success'))
        <div class="mb-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-green-800">{{ session('success') }}</div>
    @endif

    <div class="bg-white rounded-xl shadow border border-gray-200 overflow-hidden">
        <div class="px-4 py-5 sm:px-6 border-b border-gray-200 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-xl font-semibold text-gray-900">{{ $carrier->name }}</h1>
                <p class="text-sm text-gray-500 mt-0.5">
                    {{ $carrier->reg_nr ? __('app.carriers.reg_nr') . ': ' . $carrier->reg_nr : '' }}
                    {{ $carrier->country ? ' · ' . $carrier->country : '' }}
                </p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('carriers.edit', $carrier) }}" wire:navigate class="px-3 py-1.5 rounded-lg bg-amber-100 text-amber-800 text-sm font-medium hover:bg-amber-200">{{ __('app.carriers.edit') }}</a>
                <a href="{{ route('carriers.index') }}" wire:navigate class="px-3 py-1.5 rounded-lg bg-gray-100 text-gray-700 text-sm hover:bg-gray-200">{{ __('app.carriers.back') }}</a>
            </div>
        </div>
        <div class="px-4 py-4 sm:px-6 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            @if($carrier->contact_person)
                <div><span class="text-gray-500">{{ __('app.carriers.contact_person') }}:</span> {{ $carrier->contact_person }}</div>
            @endif
            @if($carrier->phone)
                <div><span class="text-gray-500">{{ __('app.carriers.phone') }}:</span> {{ $carrier->phone }}</div>
            @endif
            @if($carrier->email)
                <div><span class="text-gray-500">{{ __('app.carriers.email') }}:</span> {{ $carrier->email }}</div>
            @endif
            @if($carrier->city || $carrier->address)
                <div><span class="text-gray-500">{{ __('app.carriers.address') }}:</span> {{ trim(implode(', ', array_filter([$carrier->city, $carrier->address]))) ?: '—' }}</div>
            @endif
            {{-- Рейтинг пока скрыт --}}
            {{-- @if($carrier->rating)
                <div><span class="text-gray-500">{{ __('app.carriers.rating') }}:</span> <span class="font-medium text-amber-600">{{ $carrier->rating }}/5</span></div>
            @endif --}}
        </div>
        <div class="px-4 py-4 sm:px-6 border-t border-gray-200">
            <h2 class="text-base font-medium text-gray-800 mb-2">{{ __('app.carriers.trips_count') }}: {{ $carrier->trips_count }}</h2>
            @if($trips->isNotEmpty())
                <ul class="space-y-2">
                    @foreach($trips as $trip)
                        <li>
                            <a href="{{ route('trips.show', $trip) }}" wire:navigate class="text-amber-700 hover:underline">
                                {{ __('app.trip.show.cmr_trip') }} #{{ $trip->id }}
                                @if($trip->start_date) · {{ $trip->start_date->format('d.m.Y') }}@endif
                            </a>
                        </li>
                    @endforeach
                </ul>
                {{ $trips->links() }}
            @else
                <p class="text-gray-500 text-sm">{{ __('app.carriers.no_trips') }}</p>
            @endif
        </div>
    </div>
</div>
