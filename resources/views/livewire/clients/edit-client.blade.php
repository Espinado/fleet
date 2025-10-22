<div class="p-6">
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white shadow rounded-lg p-6 max-w-4xl mx-auto">
        <h2 class="text-2xl font-semibold mb-6">✏️ Edit Client — {{ $client->company_name }}</h2>

        <form wire:submit.prevent="save" class="space-y-6">
            {{-- Основные данные --}}
            <div>
                <label class="block text-sm font-medium mb-1">Company Name *</label>
                <input type="text" wire:model="form.company_name" class="w-full border rounded px-3 py-2">
                @error('client.company_name') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Reg. Nr</label>
                    <input type="text" wire:model="form.reg_nr" class="w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Representative</label>
                    <input type="text" wire:model=form.representative" class="w-full border rounded px-3 py-2">
                </div>
            </div>

            {{-- Контакты --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Email</label>
                    <input type="email" wire:model="form.email" class="w-full border rounded px-3 py-2">
                    @error('client.email') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Phone</label>
                    <input type="text" wire:model="form.phone" class="w-full border rounded px-3 py-2">
                </div>
            </div>

            {{-- Юридический адрес --}}
            <div class="border-t pt-4">
                <h3 class="text-lg font-semibold mb-3">Legal (Jur.) Address</h3>
                <div class="grid grid-cols-2 gap-4 mb-3">
                    <input type="text" wire:model="form.jur_country" placeholder="Country" class="border rounded px-3 py-2">
                    <input type="text" wire:model="form.jur_city" placeholder="City" class="border rounded px-3 py-2">
                </div>
                <div class="grid grid-cols-2 gap-4 mb-3">
                <input type="text" wire:model="form.jur_address" placeholder="Street, house, apt"
                       class="border rounded px-3 py-2">
                <input type="text" wire:model="form.jur_post_code" placeholder="Post code"
                       class="border rounded px-3 py-2">
                 </div>
            </div>

            {{-- Физический адрес --}}
            <div class="border-t pt-4">
                <h3 class="text-lg font-semibold mb-3">Physical (Fiz.) Address</h3>
                <div class="grid grid-cols-2 gap-4 mb-3">
                    <input type="text" wire:model="form.fiz_country" placeholder="Country" class="border rounded px-3 py-2">
                    <input type="text" wire:model="form.fiz_city" placeholder="City" class="border rounded px-3 py-2">
                </div>
                <div class="grid grid-cols-2 gap-4 mb-3">
                <input type="text" wire:model="form.fiz_address" placeholder="Street, house, apt"
                       class="border rounded px-3 py-2">
                <input type="text" wire:model="form.fiz_post_code" placeholder="Post code"
                       class="border rounded px-3 py-2">
</div>
            </div>

            {{-- Банковские данные --}}
            <div class="border-t pt-4">
                <h3 class="text-lg font-semibold mb-3">Bank Details</h3>
                <div class="grid grid-cols-2 gap-4">
                    <input type="text" wire:model="form.bank_name" placeholder="Bank name" class="border rounded px-3 py-2">
                    <input type="text" wire:model="form.swift" placeholder="SWIFT code" class="border rounded px-3 py-2">
                </div>
            </div>

            {{-- Кнопки --}}
            <div class="flex justify-end gap-3 pt-6">
                <a href="{{ route('clients.show', $client->id) }}" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">
                    Cancel
                </a>
                <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
