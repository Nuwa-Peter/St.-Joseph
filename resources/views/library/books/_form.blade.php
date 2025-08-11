<div class="space-y-6">
    <div>
        <x-input-label for="title" :value="__('Title')" />
        <x-text-input id="title" name="title" type="text" class="mt-1 block w-full" :value="old('title', $book->title ?? '')" required autofocus />
        <x-input-error class="mt-2" :messages="$errors->get('title')" />
    </div>

    <div>
        <x-input-label for="author" :value="__('Author')" />
        <x-text-input id="author" name="author" type="text" class="mt-1 block w-full" :value="old('author', $book->author ?? '')" required />
        <x-input-error class="mt-2" :messages="$errors->get('author')" />
    </div>

    <div>
        <x-input-label for="isbn" :value="__('ISBN')" />
        <x-text-input id="isbn" name="isbn" type="text" class="mt-1 block w-full" :value="old('isbn', $book->isbn ?? '')" required />
        <x-input-error class="mt-2" :messages="$errors->get('isbn')" />
    </div>

    <div>
        <x-input-label for="quantity" :value="__('Total Quantity')" />
        <x-text-input id="quantity" name="quantity" type="number" class="mt-1 block w-full" :value="old('quantity', $book->quantity ?? 1)" required min="1" />
        <x-input-error class="mt-2" :messages="$errors->get('quantity')" />
    </div>

    <div>
        <x-input-label for="shelf_location" :value="__('Shelf Location (Optional)')" />
        <x-text-input id="shelf_location" name="shelf_location" type="text" class="mt-1 block w-full" :value="old('shelf_location', $book->shelf_location ?? '')" />
        <x-input-error class="mt-2" :messages="$errors->get('shelf_location')" />
    </div>

    <div>
        <x-input-label for="published_date" :value="__('Published Date (Optional)')" />
        <x-text-input id="published_date" name="published_date" type="date" class="mt-1 block w-full" :value="old('published_date', $book->published_date ?? '')" />
        <x-input-error class="mt-2" :messages="$errors->get('published_date')" />
    </div>

    <div class="flex items-center gap-4">
        <x-primary-button>{{ __('Save') }}</x-primary-button>
    </div>
</div>
