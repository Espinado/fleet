<div class="p-4 sm:p-6 max-w-2xl mx-auto">
    @if(session('success'))
        <div class="mb-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-green-800">{{ session('success') }}</div>
    @endif

    <div class="bg-white rounded-xl shadow border border-gray-200 p-6">
        <h1 class="text-xl font-semibold text-gray-900 mb-4">{{ __('app.carriers.edit_title') }} — {{ $carrier->name }}</h1>

        <form wire:submit.prevent="save" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('app.carriers.name') }} <span class="text-red-500">*</span></label>
                <input type="text" wire:model="name" class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-amber-500">
                @error('name') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('app.carriers.reg_nr') }}</label>
                    <input type="text" wire:model="reg_nr" class="w-full rounded-lg border border-gray-300 px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('app.carriers.country') }}</label>
                    <input type="text" wire:model="country" class="w-full rounded-lg border border-gray-300 px-3 py-2">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('app.carriers.city') }}</label>
                <input type="text" wire:model="city" class="w-full rounded-lg border border-gray-300 px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('app.carriers.address') }}</label>
                <input type="text" wire:model="address" class="w-full rounded-lg border border-gray-300 px-3 py-2">
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('app.carriers.email') }}</label>
                    <input type="email" wire:model="email" class="w-full rounded-lg border border-gray-300 px-3 py-2">
                    @error('email') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('app.carriers.phone') }}</label>
                    <input type="text" wire:model="phone" class="w-full rounded-lg border border-gray-300 px-3 py-2">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('app.carriers.contact_person') }}</label>
                <input type="text" wire:model="contact_person" class="w-full rounded-lg border border-gray-300 px-3 py-2">
            </div>
            {{-- Рейтинг пока скрыт --}}
            {{-- <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('app.carriers.rating') }}</label>
                <select wire:model="rating" class="w-full rounded-lg border border-gray-300 px-3 py-2">
                    <option value="">{{ __('app.carriers.rating_empty') }}</option>
                    @for($i = 1; $i <= 5; $i++)
                        <option value="{{ $i }}">{{ $i }}</option>
                    @endfor
                </select>
            </div> --}}
            <div class="flex items-center gap-2">
                <input type="checkbox" wire:model="is_active" id="carrier_is_active" class="rounded border-gray-300">
                <label for="carrier_is_active" class="text-sm font-medium text-gray-700">{{ __('app.carriers.is_active') }}</label>
            </div>
            <div class="flex gap-2 pt-2">
                <button type="submit" class="px-4 py-2 rounded-lg bg-amber-600 text-white font-medium hover:bg-amber-700">{{ __('app.carriers.save') }}</button>
                <a href="{{ route('carriers.show', $carrier) }}" wire:navigate class="px-4 py-2 rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200">{{ __('app.carriers.back') }}</a>
            </div>
        </form>
    </div>
</div>
