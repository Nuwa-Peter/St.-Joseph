@csrf
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div class="md:col-span-2">
        <x-input-label for="name" :value="__('Item Name')" />
        <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $inventoryItem->name ?? '')" required autofocus />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="category" :value="__('Category')" />
        <x-text-input id="category" class="block mt-1 w-full" type="text" name="category" :value="old('category', $inventoryItem->category ?? '')" required placeholder="e.g., Furniture, Electronics" />
        <x-input-error :messages="$errors->get('category')" class="mt-2" />
    </div>

     <div>
        <x-input-label for="serial_number" :value="__('Serial Number (Optional)')" />
        <x-text-input id="serial_number" class="block mt-1 w-full" type="text" name="serial_number" :value="old('serial_number', $inventoryItem->serial_number ?? '')" />
        <x-input-error :messages="$errors->get('serial_number')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="location" :value="__('Location (Optional)')" />
        <x-text-input id="location" class="block mt-1 w-full" type="text" name="location" :value="old('location', $inventoryItem->location ?? '')" />
        <x-input-error :messages="$errors->get('location')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="assigned_to_id" :value="__('Assigned To (Optional)')" />
        <select name="assigned_to_id" id="assigned_to_id" class="tom-select">
            <option value="">Select a user</option>
            @foreach($users as $user)
                <option value="{{ $user->id }}" @selected(old('assigned_to_id', $inventoryItem->assigned_to_id ?? '') == $user->id)>
                    {{ $user->name }}
                </option>
            @endforeach
        </select>
         <x-input-error :messages="$errors->get('assigned_to_id')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="purchase_date" :value="__('Purchase Date (Optional)')" />
        <x-text-input id="purchase_date" class="block mt-1 w-full" type="date" name="purchase_date" :value="old('purchase_date', optional($inventoryItem->purchase_date ?? null)->format('Y-m-d'))" />
        <x-input-error :messages="$errors->get('purchase_date')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="value" :value="__('Purchase Value (Optional)')" />
        <x-text-input id="value" class="block mt-1 w-full" type="number" step="0.01" name="value" :value="old('value', $inventoryItem->value ?? '')" />
        <x-input-error :messages="$errors->get('value')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="condition" :value="__('Condition')" />
        <select name="condition" id="condition" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
            <option value="new" @selected(old('condition', $inventoryItem->condition ?? 'good') == 'new')>New</option>
            <option value="good" @selected(old('condition', $inventoryItem->condition ?? 'good') == 'good')>Good</option>
            <option value="fair" @selected(old('condition', $inventoryItem->condition ?? 'good') == 'fair')>Fair</option>
            <option value="poor" @selected(old('condition', $inventoryItem->condition ?? 'good') == 'poor')>Poor</option>
            <option value="broken" @selected(old('condition', $inventoryItem->condition ?? 'good') == 'broken')>Broken</option>
        </select>
        <x-input-error :messages="$errors->get('condition')" class="mt-2" />
    </div>

    <div class="md:col-span-2">
        <x-input-label for="description" :value="__('Description (Optional)')" />
        <textarea name="description" id="description" rows="3" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('description', $inventoryItem->description ?? '') }}</textarea>
        <x-input-error :messages="$errors->get('description')" class="mt-2" />
    </div>
</div>

<div class="flex items-center justify-end mt-6">
    <a href="{{ route('inventory.index') }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4">
        {{ __('Cancel') }}
    </a>

    <x-primary-button>
        {{ $buttonText ?? 'Save Item' }}
    </x-primary-button>
</div>
