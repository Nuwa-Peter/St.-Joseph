<div class="space-y-6">
    <div>
        <x-input-label for="name" :value="__('Item Name')" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $inventory->name ?? '')" required autofocus />
        <x-input-error class="mt-2" :messages="$errors->get('name')" />
    </div>

    <div>
        <x-input-label for="description" :value="__('Description (Optional)')" />
        <textarea id="description" name="description" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full">{{ old('description', $inventory->description ?? '') }}</textarea>
        <x-input-error class="mt-2" :messages="$errors->get('description')" />
    </div>

    <div>
        <x-input-label for="quantity" :value="__('Quantity')" />
        <x-text-input id="quantity" name="quantity" type="number" class="mt-1 block w-full" :value="old('quantity', $inventory->quantity ?? 1)" required min="0" />
        <x-input-error class="mt-2" :messages="$errors->get('quantity')" />
    </div>

    <div>
        <x-input-label for="condition" :value="__('Condition')" />
        <x-text-input id="condition" name="condition" type="text" class="mt-1 block w-full" :value="old('condition', $inventory->condition ?? 'Good')" required />
        <x-input-error class="mt-2" :messages="$errors->get('condition')" />
    </div>

    <div>
        <x-input-label for="location" :value="__('Location (Optional)')" />
        <x-text-input id="location" name="location" type="text" class="mt-1 block w-full" :value="old('location', $inventory->location ?? '')" />
        <x-input-error class="mt-2" :messages="$errors->get('location')" />
    </div>

    <div>
        <x-input-label for="purchase_date" :value="__('Purchase Date (Optional)')" />
        <x-text-input id="purchase_date" name="purchase_date" type="date" class="mt-1 block w-full" :value="old('purchase_date', $inventory->purchase_date ? $inventory->purchase_date->format('Y-m-d') : '')" />
        <x-input-error class="mt-2" :messages="$errors->get('purchase_date')" />
    </div>

    <div>
        <x-input-label for="purchase_price" :value="__('Purchase Price (Optional)')" />
        <x-text-input id="purchase_price" name="purchase_price" type="number" step="0.01" class="mt-1 block w-full" :value="old('purchase_price', $inventory->purchase_price ?? '')" />
        <x-input-error class="mt-2" :messages="$errors->get('purchase_price')" />
    </div>

    <div class="flex items-center gap-4">
        <x-primary-button>{{ __('Save Item') }}</x-primary-button>
    </div>
</div>
