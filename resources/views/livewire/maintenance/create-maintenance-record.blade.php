<div class="min-h-screen bg-gray-100 pb-24">
    <div class="max-w-2xl mx-auto px-4 py-6">
        <h1 class="text-xl sm:text-2xl font-semibold text-gray-800 mb-6">
            {{ __('app.maintenance_record.create_title') }}
        </h1>

        <form wire:submit="save" class="space-y-6 rounded-xl bg-white border border-gray-200 shadow-sm p-6">
            @if(!$truck_id && !$trailer_id)
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('app.maintenance_record.vehicle') }} <span class="text-red-500">*</span></label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">{{ __('app.nav.trucks') }}</label>
                            <select wire:model="truck_id" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 min-h-[44px]">
                                <option value="">{{ __('app.maintenance_record.select_truck') }}</option>
                                @foreach($this->trucksOptions as $opt)
                                    <option value="{{ $opt['id'] }}">{{ $opt['label'] }}</option>
                                @endforeach
                            </select>
                            @error('truck_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">{{ __('app.nav.trailers') }}</label>
                            <select wire:model="trailer_id" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 min-h-[44px]">
                                <option value="">{{ __('app.maintenance_record.select_trailer') }}</option>
                                @foreach($this->trailersOptions as $opt)
                                    <option value="{{ $opt['id'] }}">{{ $opt['label'] }}</option>
                                @endforeach
                            </select>
                            @error('trailer_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <p class="text-xs text-amber-600 mt-1">{{ __('app.maintenance_record.vehicle_required') }}</p>
                </div>
            @else
                <div class="rounded-lg bg-gray-50 border border-gray-200 px-4 py-3">
                    <p class="text-sm text-gray-600">
                        {{ $truck_id ? \App\Models\Truck::find($truck_id)?->display_name : \App\Models\Trailer::find($trailer_id)?->brand . ' ' . \App\Models\Trailer::find($trailer_id)?->plate }}
                    </p>
                </div>
            @endif

            <div>
                <label for="performed_at" class="block text-sm font-medium text-gray-700 mb-1">{{ __('app.maintenance_record.performed_at') }} <span class="text-red-500">*</span></label>
                <input type="date" id="performed_at" wire:model="performed_at"
                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 min-h-[44px]">
                @error('performed_at') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="odometer_km" class="block text-sm font-medium text-gray-700 mb-1">{{ __('app.maintenance_record.odometer_km') }}</label>
                <input type="number" id="odometer_km" wire:model="odometer_km" min="0" step="1" placeholder="—"
                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 min-h-[44px]">
                @error('odometer_km') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">{{ __('app.maintenance_record.description') }} <span class="text-red-500">*</span></label>
                <textarea id="description" wire:model="description" rows="4"
                          class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 min-h-[44px]"
                          placeholder="{{ __('app.maintenance_record.description_placeholder') }}"></textarea>
                @error('description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="cost" class="block text-sm font-medium text-gray-700 mb-1">{{ __('app.maintenance_record.cost') }}</label>
                <input type="text" id="cost" wire:model="cost" inputmode="decimal" placeholder="—"
                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 min-h-[44px]">
                <p class="text-xs text-gray-500 mt-1">{{ __('app.maintenance_record.cost_hint') }}</p>
                @error('cost') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="space-y-4">
                <p class="text-sm font-medium text-gray-700">{{ __('app.maintenance_record.next_service_title') }}</p>
                <p class="text-xs text-amber-600">{{ __('app.maintenance_record.next_service_one_required') }}</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="next_service_date" class="block text-xs font-medium text-gray-600 mb-1">{{ __('app.maintenance_record.next_service_date') }}</label>
                        <input type="date" id="next_service_date" wire:model="next_service_date"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 min-h-[44px]">
                        @error('next_service_date') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="next_service_km" class="block text-xs font-medium text-gray-600 mb-1">{{ __('app.maintenance_record.next_service_km') }}</label>
                        <input type="number" id="next_service_km" wire:model="next_service_km" min="0" step="1" placeholder="—"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 min-h-[44px]">
                        <p class="text-xs text-gray-500 mt-0.5">{{ __('app.maintenance_record.next_service_km_hint') }}</p>
                        @error('next_service_km') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap gap-3 pt-2">
                <button type="submit" wire:loading.attr="disabled"
                        class="px-5 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition min-h-[44px] disabled:opacity-50">
                    <span wire:loading.remove wire:target="save">{{ __('app.maintenance_record.save') }}</span>
                    <span wire:loading wire:target="save">{{ __('app.maintenance_record.saving') }}</span>
                </button>
                <a href="{{ route('maintenance.records.index') }}" wire:navigate
                   class="px-5 py-2.5 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition min-h-[44px] inline-flex items-center">
                    {{ __('app.common.cancel') }}
                </a>
            </div>
        </form>
    </div>
</div>
