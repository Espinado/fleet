<div class="min-h-screen bg-gray-100 pb-24">
    <div class="max-w-2xl mx-auto px-4 py-6">
        <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
            <h1 class="text-xl sm:text-2xl font-semibold text-gray-800">
                {{ __('app.maintenance_record.show_title') }}
            </h1>
            <div class="flex items-center gap-2">
                <a href="{{ route('maintenance.records.index') }}" wire:navigate
                   class="px-4 py-2 text-sm bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 min-h-[44px] inline-flex items-center">
                    ← {{ __('app.maintenance_record.journal') }}
                </a>
                <a href="{{ route('maintenance.records.edit', $record) }}" wire:navigate
                   class="px-4 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700 min-h-[44px] inline-flex items-center">
                    {{ __('app.maintenance_record.edit') }}
                </a>
            </div>
        </div>

        <div class="rounded-xl bg-white border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
                <p class="font-semibold text-gray-800">{{ $record->vehicle_name }}</p>
                <p class="text-sm text-gray-500">
                    {{ $record->vehicle_type === 'truck' ? __('app.nav.trucks') : __('app.nav.trailers') }}
                </p>
            </div>
            <dl class="divide-y divide-gray-100">
                <div class="px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4">
                    <dt class="text-sm font-medium text-gray-500">{{ __('app.maintenance_record.performed_at') }}</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0 tabular-nums">
                        {{ $record->performed_at->format('d.m.Y') }}
                    </dd>
                </div>
                @if($record->odometer_km !== null)
                    <div class="px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4">
                        <dt class="text-sm font-medium text-gray-500">{{ __('app.maintenance_record.odometer_km') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0 tabular-nums">
                            {{ number_format($record->odometer_km, 0, '.', ' ') }} km
                        </dd>
                    </div>
                @endif
                <div class="px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4">
                    <dt class="text-sm font-medium text-gray-500">{{ __('app.maintenance_record.description') }}</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0 whitespace-pre-wrap">{{ $record->description ?: '—' }}</dd>
                </div>
                <div class="px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4">
                    <dt class="text-sm font-medium text-gray-500">{{ __('app.maintenance_record.cost') }}</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0 tabular-nums">
                        @if($record->cost !== null)
                            {{ number_format((float) $record->cost, 2, '.', ' ') }}
                        @else
                            —
                        @endif
                    </dd>
                </div>
            </dl>
        </div>

        <div class="mt-4">
            @if($record->truck_id)
                <a href="{{ route('trucks.show', $record->truck_id) }}" wire:navigate class="text-sm text-blue-600 hover:underline">
                    {{ __('app.truck.show.title') }} →
                </a>
            @else
                <a href="{{ route('trailers.show', $record->trailer_id) }}" wire:navigate class="text-sm text-blue-600 hover:underline">
                    {{ __('app.trailer.show.title') }} →
                </a>
            @endif
        </div>
    </div>
</div>
