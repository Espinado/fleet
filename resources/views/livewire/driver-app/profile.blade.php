<div class="flex flex-col min-h-screen bg-gray-100">

    {{-- HEADER --}}
    @include('driver-app.components.topbar', [
        'back' => 0,
        'title' => __('app.driver.profile.title')
    ])

    <div class="flex-1 px-4 py-4 space-y-6">

        {{-- SUCCESS MESSAGE --}}
        @if (session('success'))
            <div class="bg-green-100 text-green-800 p-3 rounded shadow text-sm">
                {{ session('success') }}
            </div>
        @endif

        {{-- CARD --}}
        <div class="bg-white p-4 rounded-xl shadow space-y-6">

            {{-- Фото --}}
            <div class="flex flex-col items-center">
                <img src="{{ $driver->photo ? asset('storage/' . $driver->photo) : '/default-avatar.png' }}"
                     class="w-28 h-28 rounded-full object-cover shadow">

                <div class="mt-3">
                    <input type="file" accept="image/*" capture="user" wire:model="photo">
                    @error('photo') <p class="text-red-500 text-xs">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- ФИО --}}
            <div class="space-y-1">
                <p class="text-sm text-gray-500">{{ __('app.driver.profile.name') }}</p>
                <p class="text-lg font-semibold">{{ $driver->first_name }} {{ $driver->last_name }}</p>
            </div>

            {{-- Телефон --}}
            <div>
                <label class="text-sm">{{ __('app.driver.profile.phone') }}</label>
                <input type="text" wire:model="phone"
                       class="w-full border rounded px-3 py-2 mt-1">
                @error('phone') <p class="text-red-500 text-xs">{{ $message }}</p> @enderror
            </div>

            {{-- Email --}}
            <div>
                <label class="text-sm">{{ __('app.driver.profile.email') }}</label>
                <input type="text" wire:model="email"
                       class="w-full border rounded px-3 py-2 mt-1">
                @error('email') <p class="text-red-500 text-xs">{{ $message }}</p> @enderror
            </div>

            {{-- Гражданство --}}
            <div class="space-y-1">
                <p class="text-sm text-gray-500">{{ __('app.driver.profile.citizenship') }}</p>
                <p class="font-semibold">{{ getCountryById($driver->citizenship_id) }}</p>
            </div>

            {{-- Документы --}}
            <div class="space-y-3 pt-2">
                <h3 class="font-semibold text-sm text-gray-700">{{ __('app.driver.profile.docs') }}</h3>

                <div class="space-y-2">
                    {{-- Водительское удостоверение --}}
                    <div class="flex items-center justify-between bg-gray-50 rounded-lg px-3 py-2">
                        <div class="flex items-center gap-2">
                            <span class="text-lg">🪪</span>
                            <div>
                                <div class="text-xs text-gray-500 uppercase tracking-wide">
                                    {{ __('app.driver.profile.license') }}
                                </div>
                                <div class="text-sm font-semibold">
                                    {{ $driver->license_number ?? '—' }}
                                </div>
                            </div>
                        </div>
                        <div class="text-right text-xs text-gray-500">
                            <div class="uppercase tracking-wide">
                                {{ __('app.driver.profile.license_until') }}
                            </div>
                            <div class="text-sm font-semibold text-gray-800">
                                @php
                                    $licenseEnd = $driver->license_end ? \Illuminate\Support\Carbon::parse($driver->license_end) : null;
                                @endphp
                                {{ $licenseEnd ? $licenseEnd->format('d-m-Y') : '—' }}
                            </div>
                        </div>
                    </div>

                    {{-- Медицинская справка --}}
                    <div class="flex items-center justify-between bg-gray-50 rounded-lg px-3 py-2">
                        <div class="flex items-center gap-2">
                            <span class="text-lg">🩺</span>
                            <div>
                                <div class="text-xs text-gray-500 uppercase tracking-wide">
                                    {{ __('app.driver.profile.medical') }}
                                </div>
                                <div class="text-sm font-semibold text-gray-800">
                                    @php
                                        $medIssued = $driver->medical_issued ? \Illuminate\Support\Carbon::parse($driver->medical_issued) : null;
                                        $medExpired = $driver->medical_expired ? \Illuminate\Support\Carbon::parse($driver->medical_expired) : null;
                                    @endphp
                                    {{ $medIssued ? $medIssued->format('d-m-Y') : '—' }}
                                    <span class="text-gray-400">–</span>
                                    {{ $medExpired ? $medExpired->format('d-m-Y') : '—' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Кнопки --}}
            <button class="w-full bg-blue-600 text-white py-3 rounded-lg shadow">
                🔐 {{ __('app.driver.profile.change_pin') }}
            </button>

            <form action="{{ route('driver.logout') }}" method="POST">
                @csrf
                <button class="w-full bg-red-600 text-white py-3 rounded-lg shadow mt-2">
                    🚪 {{ __('app.driver.profile.logout') }}
                </button>
            </form>

        </div>

    </div>

</div>
