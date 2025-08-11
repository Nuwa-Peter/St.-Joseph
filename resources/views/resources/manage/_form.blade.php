<div class="space-y-6">
    <div>
        <x-input-label for="name" :value="__('Resource Name')" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $resource->name ?? '')" required autofocus />
        <x-input-error class="mt-2" :messages="$errors->get('name')" />
    </div>

    <div>
        <x-input-label for="description" :value="__('Description (Optional)')" />
        <textarea id="description" name="description" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full">{{ old('description', $resource->description ?? '') }}</textarea>
        <x-input-error class="mt-2" :messages="$errors->get('description')" />
    </div>

    <div class="block mt-4">
        <label for="is_bookable" class="inline-flex items-center">
            <input id="is_bookable" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="is_bookable" value="1" {{ old('is_bookable', isset($resource) && $resource->is_bookable) ? 'checked' : '' }}>
            <span class="ml-2 text-sm text-gray-600">{{ __('This resource can be booked by staff') }}</span>
        </label>
    </div>

    <div class="flex items-center gap-4">
        <x-primary-button>{{ __('Save Resource') }}</x-primary-button>
    </div>
</div>
