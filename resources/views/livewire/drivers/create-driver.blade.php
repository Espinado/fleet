<div class="max-w-5xl mx-auto bg-white p-6 rounded shadow space-y-6">
    <h2 class="text-xl font-bold">✏️ Edit Driver</h2>

    @if(session()->has('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
            {{ session('success') }}
        </div>
    @endif

    <form wire:submit.prevent="save" class="space-y-4">

        {{-- Личные данные --}}
        <h3 class="font-semibold">Personal Information</h3>
        <div class="grid grid-cols-3 gap-4">
            <div>
                <label>First Name</label>
                <input type="text" wire:model="first_name" class="w-full border rounded p-2">
                @error('first_name') <span class="text-red-500">{{ $message }}</span> @enderror
            </div>
            <div>
                <label>Last Name</label>
                <input type="text" wire:model="last_name" class="w-full border rounded p-2">
                @error('last_name') <span class="text-red-500">{{ $message }}</span> @enderror
            </div>
            <div>
                <label>Personal Code</label>
                <input type="text" wire:model="pers_code" class="w-full border rounded p-2">
                @error('pers_code') <span class="text-red-500">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="grid grid-cols-3 gap-4">
            <div>
                <label>Citizenship</label>
                <input type="text" wire:model="citizenship" class="w-full border rounded p-2">
                @error('citizenship') <span class="text-red-500">{{ $message }}</span> @enderror
            </div>
            <div>
                <label>Phone</label>
                <input type="text" wire:model="phone" class="w-full border rounded p-2">
                @error('phone') <span class="text-red-500">{{ $message }}</span> @enderror
            </div>
            <div>
                <label>Email</label>
                <input type="email" wire:model="email" class="w-full border rounded p-2">
                @error('email') <span class="text-red-500">{{ $message }}</span> @enderror
            </div>
        </div>

        {{-- Declared Address --}}
        <h3 class="font-semibold mt-4">Declared Address</h3>
        <div class="grid grid-cols-3 gap-4">
            <div><label>Country</label><input type="text" wire:model="declared_country" class="w-full border rounded p-2">@error('declared_country') <span class="text-red-500">{{ $message }}</span> @enderror</div>
            <div><label>City</label><input type="text" wire:model="declared_city" class="w-full border rounded p-2">@error('declared_city') <span class="text-red-500">{{ $message }}</span> @enderror</div>
            <div><label>Street</label><input type="text" wire:model="declared_street" class="w-full border rounded p-2">@error('declared_street') <span class="text-red-500">{{ $message }}</span> @enderror</div>
        </div>
        <div class="grid grid-cols-3 gap-4">
            <div><label>Building</label><input type="text" wire:model="declared_building" class="w-full border rounded p-2">@error('declared_building') <span class="text-red-500">{{ $message }}</span> @enderror</div>
            <div><label>Room</label><input type="text" wire:model="declared_room" class="w-full border rounded p-2">@error('declared_room') <span class="text-red-500">{{ $message }}</span> @enderror</div>
            <div><label>Postcode</label><input type="text" wire:model="declared_postcode" class="w-full border rounded p-2">@error('declared_postcode') <span class="text-red-500">{{ $message }}</span> @enderror</div>
        </div>

        {{-- Actual Address --}}
        <h3 class="font-semibold mt-4">Actual Address</h3>
        <div class="grid grid-cols-3 gap-4">
            <div><label>Country</label><input type="text" wire:model="actual_country" class="w-full border rounded p-2">@error('actual_country') <span class="text-red-500">{{ $message }}</span> @enderror</div>
            <div><label>City</label><input type="text" wire:model="actual_city" class="w-full border rounded p-2">@error('actual_city') <span class="text-red-500">{{ $message }}</span> @enderror</div>
            <div><label>Street</label><input type="text" wire:model="actual_street" class="w-full border rounded p-2">@error('actual_street') <span class="text-red-500">{{ $message }}</span> @enderror</div>
        </div>
        <div class="grid grid-cols-3 gap-4">
            <div><label>Building</label><input type="text" wire:model="actual_building" class="w-full border rounded p-2">@error('actual_building') <span class="text-red-500">{{ $message }}</span> @enderror</div>
            <div><label>Room</label><input type="text" wire:model="actual_room" class="w-full border rounded p-2">@error('actual_room') <span class="text-red-500">{{ $message }}</span> @enderror</div>
            <div><label>Status</label>
                <select wire:model="status" class="w-full border rounded p-2">
                    @foreach(\App\Enums\DriverStatus::options() as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
                @error('status') <span class="text-red-500">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="flex items-center gap-2 mt-2">
            <input type="checkbox" wire:model="is_active" class="mr-2">
            <span>Active</span>
        </div>

        {{-- Документы и даты --}}
        @include('livewire.drivers.partials.documents-edit') {{-- Вставляем все поля License, 95 Code, Permit, Medical, Declaration как отдельный partial --}}

        {{-- Фото --}}
        <h3 class="font-semibold mt-4">Photos</h3>
        <div class="grid grid-cols-3 gap-4">
            <div>
                <label>Driver Photo</label>
                <input type="file" wire:model="photo" class="w-full border rounded p-2">
                @if($photo instanceof \Livewire\TemporaryUploadedFile)
                    <img src="{{ $photo->temporaryUrl() }}" class="w-32 mt-2 rounded">
                @elseif($photo)
                    <img src="{{ Storage::url($photo) }}" class="w-32 mt-2 rounded">
                @endif
                @error('photo') <span class="text-red-500">{{ $message }}</span> @enderror
            </div>
            <div>
                <label>License Photo</label>
                <input type="file" wire:model="license_photo" class="w-full border rounded p-2">
                @if($license_photo instanceof \Livewire\TemporaryUploadedFile)
                    <img src="{{ $license_photo->temporaryUrl() }}" class="w-32 mt-2 rounded">
                @elseif($license_photo)
                    <img src="{{ Storage::url($license_photo) }}" class="w-32 mt-2 rounded">
                @endif
                @error('license_photo') <span class="text-red-500">{{ $message }}</span> @enderror
            </div>
            <div>
                <label>Medical Certificate Photo</label>
                <input type="file" wire:model="medical_certificate_photo" class="w-full border rounded p-2">
                @if($medical_certificate_photo instanceof \Livewire\TemporaryUploadedFile)
                    <img src="{{ $medical_certificate_photo->temporaryUrl() }}" class="w-32 mt-2 rounded">
                @elseif($medical_certificate_photo)
                    <img src="{{ Storage::url($medical_certificate_photo) }}" class="w-32 mt-2 rounded">
                @endif
                @error('medical_certificate_photo') <span class="text-red-500">{{ $message }}</span> @enderror
            </div>
        </div>

        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded mt-4">💾 Save</button>
    </form>
</div>
