<div class="bg-white p-6 rounded-xl w-full max-w-full sm:max-w-sm shadow text-center">

    <h1 class="text-xl font-bold mb-4">{{ __('app.driver.login.title') }}</h1>

    @error('pin')
        <div class="text-red-600 mb-2">{{ $message }}</div>
    @enderror

    <input type="number"
           inputmode="numeric"
           wire:model="pin"
           class="border rounded-xl w-full p-3 text-lg mb-4 text-center"
           placeholder="{{ __('app.driver.login.pin') }}">

    <button wire:click="login"
            class="w-full bg-blue-600 text-white rounded-xl py-3 font-semibold">
        {{ __('app.driver.login.button') }}
    </button>

</div>
