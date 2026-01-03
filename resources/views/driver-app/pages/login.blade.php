<form wire:submit.prevent="login"
      class="bg-white p-6 rounded-xl w-full max-w-sm shadow text-center space-y-4">

    <h1 class="text-xl font-bold">Вход водителя</h1>

    @error('pin')
        <div class="text-red-600 text-sm">{{ $message }}</div>
    @enderror

    <input
        type="text"
        wire:model.defer="pin"
        inputmode="numeric"
        autocomplete="one-time-code"
        class="border rounded-xl w-full p-3 text-lg text-center focus:outline-none focus:ring-2 focus:ring-blue-500"
        placeholder="PIN"
    >

    <button
        type="submit"
        wire:loading.attr="disabled"
        wire:target="login"
        class="w-full bg-blue-600 text-white rounded-xl py-3 font-semibold
               disabled:opacity-60 disabled:cursor-not-allowed">
        <span wire:loading.remove wire:target="login">Войти</span>
        <span wire:loading wire:target="login">Вход...</span>
    </button>

</form>
