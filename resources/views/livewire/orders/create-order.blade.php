<div class="p-4 sm:p-6 w-full max-w-[1600px] mx-auto">
    @if (session('success'))
        <div class="mb-4 p-4 rounded bg-green-100 border border-green-400 text-green-800">{{ session('success') }}</div>
    @endif

    <div class="bg-white shadow rounded-xl border border-gray-200 p-4 sm:p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('app.orders.create.title') }}</h2>

        <form wire:submit.prevent="save" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('app.orders.col_order_date') }} *</label>
                    <input type="date" wire:model="order_date" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    @error('order_date') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('app.orders.col_expeditor') }} *</label>
                    <select wire:model="expeditor_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        <option value="">—</option>
                        @foreach($companies as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                    @error('expeditor_id') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('app.orders.col_status') }}</label>
                    <select wire:model="status" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        @foreach($statuses as $s)
                            <option value="{{ $s->value }}">{{ $s->label() }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('app.trips.details') }} / {{ __('app.orders.show.title') }}</label>
                <textarea wire:model="notes" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" placeholder="..."></textarea>
                @error('notes') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- TIR / Таможня (как в создании рейса) --}}
            <div class="border-t border-gray-200 pt-4">
                <h3 class="text-sm font-semibold text-gray-800 mb-2">{{ __('app.orders.customs_title') }}</h3>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 items-start">
                    <div class="sm:col-span-1">
                        <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                            <input type="checkbox" wire:model.live="customs" class="rounded border-gray-300">
                            {{ __('app.orders.customs_checkbox') }}
                        </label>
                        <p class="text-xs text-gray-500 mt-1">{{ __('app.orders.customs_hint') }}</p>
                    </div>
                    @if($customs)
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('app.orders.customs_address_label') }} *</label>
                            <input type="text" wire:model="customs_address" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" placeholder="{{ __('app.orders.customs_address_placeholder') }}">
                            @error('customs_address') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    @else
                        <div class="sm:col-span-2 text-xs text-gray-500">{{ __('app.orders.customs_address_after') }}</div>
                    @endif
                </div>
            </div>

            {{-- Маршрут (шаги) --}}
            <div class="border-t border-gray-200 pt-4">
                <h3 class="text-sm font-semibold text-gray-800 mb-1">{{ __('app.orders.steps_title') }}</h3>
                <p class="text-xs text-gray-500 mb-2">{{ __('app.orders.steps_hint') }}</p>
                @foreach($steps as $idx => $step)
                    <div class="mb-4 p-3 bg-gray-50 rounded-lg border border-gray-200" wire:key="step-{{ $step['uid'] }}">
                        <div class="flex items-center justify-between gap-2 mb-2">
                            <span class="text-xs font-medium text-gray-500">{{ __('app.trip.show.step_column') }} {{ $idx + 1 }}</span>
                            <button type="button" wire:click="removeStep({{ $idx }})" class="text-red-600 hover:text-red-800 text-xs">✖</button>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            <div>
                                <label class="block text-xs text-gray-600">Тип</label>
                                <select wire:model="steps.{{ $idx }}.type" class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm">
                                    <option value="loading">{{ __('app.orders.step_type_loading') }}</option>
                                    <option value="unloading">{{ __('app.orders.step_type_unloading') }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs text-gray-600">Страна</label>
                                <select wire:model.live="steps.{{ $idx }}.country_id" class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm">
                                    <option value="">—</option>
                                    @foreach($countries as $cid => $c)
                                        <option value="{{ $cid }}">{{ $c['name'] ?? $cid }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs text-gray-600">Город</label>
                                <select wire:model="steps.{{ $idx }}.city_id" class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm">
                                    <option value="">—</option>
                                    @foreach($stepCities[$idx]['cities'] ?? [] as $cityId => $city)
                                        <option value="{{ $cityId }}">{{ is_array($city) ? ($city['name'] ?? $cityId) : (string) $city }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs text-gray-600">Адрес</label>
                                <input type="text" wire:model="steps.{{ $idx }}.address" class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm" placeholder="—">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-600">Дата</label>
                                <input type="date" wire:model="steps.{{ $idx }}.date" class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-600">Время</label>
                                <input type="time" wire:model="steps.{{ $idx }}.time" class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-600">Контакт (телефон)</label>
                                <input type="text" wire:model="steps.{{ $idx }}.contact_phone" class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm" placeholder="+371 ...">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-xs text-gray-600">Заметки по шагу</label>
                                <input type="text" wire:model="steps.{{ $idx }}.notes" class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm" placeholder="—">
                            </div>
                        </div>
                    </div>
                @endforeach
                <button type="button" wire:click="addStep" class="text-sm text-blue-600 hover:underline">
                    + {{ __('app.orders.add_step') }}
                </button>
            </div>

            {{-- Грузы: у каждого клиента своя цена (сборный груз), как в создании рейса --}}
            <div class="border-t border-gray-200 pt-4">
                <h3 class="text-sm font-semibold text-gray-800 mb-1">{{ __('app.orders.cargos_title') }}</h3>
                <p class="text-xs text-gray-500 mb-2">{{ __('app.orders.cargos_hint') }} {{ __('app.orders.cargos_price_per_client_hint') }}</p>
                @foreach($cargos as $idx => $cargo)
                    <div class="mb-4 p-3 bg-gray-50 rounded-lg border border-gray-200" wire:key="cargo-{{ $cargo['uid'] }}">
                        <div class="flex items-center justify-between gap-2 mb-2">
                            <span class="text-xs font-medium text-gray-500">{{ __('app.stats.clients.col_cargos') }} {{ $idx + 1 }}</span>
                            <button type="button" wire:click="removeCargo({{ $idx }})" class="text-red-600 hover:text-red-800 text-xs">✖</button>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            <div>
                                <label class="block text-xs text-gray-600">{{ __('app.orders.col_customer') }}</label>
                                <select wire:model="cargos.{{ $idx }}.customer_id" class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm">
                                    <option value="">—</option>
                                    @foreach($clients as $client)
                                        <option value="{{ $client->id }}">{{ $client->company_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs text-gray-600">{{ __('app.orders.col_quoted_price') }} ({{ $currency }})</label>
                                <input type="text" inputmode="decimal" wire:model="cargos.{{ $idx }}.quoted_price" class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm font-medium" placeholder="0.00">
                                <p class="text-[11px] text-gray-500">{{ __('app.orders.price_per_cargo_hint') }}</p>
                            </div>
                            <div>
                                <label class="block text-xs text-gray-600">{{ __('app.orders.cargo_requested_date_from') }}</label>
                                <input type="date" wire:model="cargos.{{ $idx }}.requested_date_from" class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-600">{{ __('app.orders.cargo_requested_date_to') }}</label>
                                <input type="date" wire:model="cargos.{{ $idx }}.requested_date_to" class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-xs text-gray-600">{{ __('app.orders.col_description') }}</label>
                                <input type="text" wire:model="cargos.{{ $idx }}.description" class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm" placeholder="—">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-600">{{ __('app.orders.col_shipper') }}</label>
                                <select wire:model="cargos.{{ $idx }}.shipper_id" class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm">
                                    <option value="">—</option>
                                    @foreach($clients as $client)
                                        <option value="{{ $client->id }}">{{ $client->company_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs text-gray-600">{{ __('app.orders.col_consignee') }}</label>
                                <select wire:model="cargos.{{ $idx }}.consignee_id" class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm">
                                    <option value="">—</option>
                                    @foreach($clients as $client)
                                        <option value="{{ $client->id }}">{{ $client->company_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            {{-- Характеристики груза как при создании рейса --}}
                            <div class="sm:col-span-2 grid grid-cols-2 sm:grid-cols-4 gap-2">
                                <div>
                                    <label class="block text-[11px] text-gray-500">{{ __('app.orders.col_weight_kg') }}</label>
                                    <input type="number" step="0.001" wire:model="cargos.{{ $idx }}.weight_kg" class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm" placeholder="—">
                                </div>
                                <div>
                                    <label class="block text-[11px] text-gray-500">Net kg</label>
                                    <input type="number" step="0.001" wire:model="cargos.{{ $idx }}.net_weight" class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm" placeholder="—">
                                </div>
                                <div>
                                    <label class="block text-[11px] text-gray-500">Gross kg</label>
                                    <input type="number" step="0.001" wire:model="cargos.{{ $idx }}.gross_weight" class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm" placeholder="—">
                                </div>
                                <div>
                                    <label class="block text-[11px] text-gray-500">Tonnes</label>
                                    <input type="number" step="0.001" wire:model="cargos.{{ $idx }}.tonnes" class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm" placeholder="—">
                                </div>
                            </div>
                            <div class="sm:col-span-2 grid grid-cols-2 sm:grid-cols-6 gap-2">
                                <div>
                                    <label class="block text-[11px] text-gray-500">HS / Customs</label>
                                    <input type="text" wire:model="cargos.{{ $idx }}.customs_code" class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm" placeholder="—">
                                </div>
                                <div>
                                    <label class="block text-[11px] text-gray-500">Packages</label>
                                    <input type="number" step="1" wire:model="cargos.{{ $idx }}.packages" class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm" placeholder="—">
                                </div>
                                <div>
                                    <label class="block text-[11px] text-gray-500">{{ __('app.orders.col_pallets') }}</label>
                                    <input type="number" step="1" wire:model="cargos.{{ $idx }}.pallets" class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm" placeholder="—">
                                </div>
                                <div>
                                    <label class="block text-[11px] text-gray-500">Units</label>
                                    <input type="number" step="1" wire:model="cargos.{{ $idx }}.units" class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm" placeholder="—">
                                </div>
                                <div>
                                    <label class="block text-[11px] text-gray-500">{{ __('app.orders.col_volume_m3') }}</label>
                                    <input type="number" step="0.001" wire:model="cargos.{{ $idx }}.volume_m3" class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm" placeholder="—">
                                </div>
                                <div>
                                    <label class="block text-[11px] text-gray-500">LM</label>
                                    <input type="number" step="0.001" wire:model="cargos.{{ $idx }}.loading_meters" class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm" placeholder="—">
                                </div>
                            </div>
                            <div class="sm:col-span-2 grid grid-cols-1 sm:grid-cols-4 gap-2">
                                <div>
                                    <label class="block text-[11px] text-gray-500">ADR / Hazmat</label>
                                    <input type="text" wire:model="cargos.{{ $idx }}.hazmat" class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm" placeholder="—">
                                </div>
                                <div>
                                    <label class="block text-[11px] text-gray-500">Temperature</label>
                                    <input type="text" wire:model="cargos.{{ $idx }}.temperature" class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm" placeholder="+2..+6">
                                </div>
                                <div class="flex items-end">
                                    <label class="inline-flex items-center gap-2 text-xs text-gray-600">
                                        <input type="checkbox" wire:model="cargos.{{ $idx }}.stackable" class="rounded border-gray-300"> Stackable
                                    </label>
                                </div>
                                <div class="sm:col-span-1"></div>
                            </div>
                            <div class="sm:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-[11px] text-gray-500">Instructions</label>
                                    <input type="text" wire:model="cargos.{{ $idx }}.instructions" class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm" placeholder="—">
                                </div>
                                <div>
                                    <label class="block text-[11px] text-gray-500">Remarks</label>
                                    <input type="text" wire:model="cargos.{{ $idx }}.remarks" class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm" placeholder="—">
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
                <button type="button" wire:click="addCargo" class="text-sm text-blue-600 hover:underline">
                    + {{ __('app.orders.add_cargo') }}
                </button>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700">
                    {{ __('app.orders.add') }}
                </button>
                <a href="{{ route('orders.index') }}" wire:navigate class="text-gray-600 hover:text-gray-900 text-sm">
                    {{ __('app.orders.show.back') }}
                </a>
            </div>
        </form>
    </div>
</div>
