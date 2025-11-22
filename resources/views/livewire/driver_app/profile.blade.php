<div class="flex flex-col min-h-screen bg-gray-100">

    {{-- HEADER --}}
    @include('driver-app.components.topbar', [
        'back' => 0,
        'title' => 'Профиль'
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
                <p class="text-sm text-gray-500">Имя</p>
                <p class="text-lg font-semibold">{{ $driver->first_name }} {{ $driver->last_name }}</p>
            </div>

            {{-- Телефон --}}
            <div>
                <label class="text-sm">Телефон</label>
                <input type="text" wire:model="phone"
                       class="w-full border rounded px-3 py-2 mt-1">
                @error('phone') <p class="text-red-500 text-xs">{{ $message }}</p> @enderror
            </div>

            {{-- Email --}}
            <div>
                <label class="text-sm">Email</label>
                <input type="text" wire:model="email"
                       class="w-full border rounded px-3 py-2 mt-1">
                @error('email') <p class="text-red-500 text-xs">{{ $message }}</p> @enderror
            </div>

            {{-- Гражданство --}}
            <div class="space-y-1">
                <p class="text-sm text-gray-500">Гражданство</p>
                <p class="font-semibold">{{ getCountryById($driver->citizenship_id) }}</p>
            </div>

            {{-- Документы --}}
            <div class="space-y-2 pt-2">
                <h3 class="font-semibold text-sm text-gray-700">Документы</h3>

                <p class="text-sm">
                    <strong>Вод. удостоверение:</strong>
                    {{ $driver->license_number }}  
                    <span class="text-gray-500">до {{ $driver->license_end }}</span>
                </p>

                <p class="text-sm">
                    <strong>Мед. справка:</strong>
                    {{ $driver->medical_issued }} – {{ $driver->medical_expired }}
                </p>
            </div>

            {{-- Кнопки --}}
            <button class="w-full bg-blue-600 text-white py-3 rounded-lg shadow">
                🔐 Сменить PIN
            </button>

            <form action="{{ route('driver.logout') }}" method="POST">
                @csrf
                <button class="w-full bg-red-600 text-white py-3 rounded-lg shadow mt-2">
                    🚪 Выйти
                </button>
            </form>

        </div>

    </div>

</div>
