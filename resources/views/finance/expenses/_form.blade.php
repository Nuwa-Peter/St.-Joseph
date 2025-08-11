@csrf
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div>
        <x-input-label for="expense_date" :value="__('Expense Date')" />
        <x-text-input id="expense_date" class="block mt-1 w-full" type="date" name="expense_date" :value="old('expense_date', isset($expense) ? $expense->expense_date->format('Y-m-d') : now()->format('Y-m-d'))" required />
        <x-input-error :messages="$errors->get('expense_date')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="expense_category_id" :value="__('Expense Category')" />
        <select name="expense_category_id" id="expense_category_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
            <option value="">Select a category</option>
            @foreach($categories as $category)
                <option value="{{ $category->id }}" @selected(old('expense_category_id', $expense->expense_category_id ?? '') == $category->id)>
                    {{ $category->name }}
                </option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('expense_category_id')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="amount" :value="__('Amount')" />
        <x-text-input id="amount" class="block mt-1 w-full" type="number" step="0.01" name="amount" :value="old('amount', $expense->amount ?? '')" required />
        <x-input-error :messages="$errors->get('amount')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="receipt_number" :value="__('Receipt Number (Optional)')" />
        <x-text-input id="receipt_number" class="block mt-1 w-full" type="text" name="receipt_number" :value="old('receipt_number', $expense->receipt_number ?? '')" />
        <x-input-error :messages="$errors->get('receipt_number')" class="mt-2" />
    </div>

    <div class="md:col-span-2">
        <x-input-label for="description" :value="__('Description')" />
        <textarea name="description" id="description" rows="4" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>{{ old('description', $expense->description ?? '') }}</textarea>
        <x-input-error :messages="$errors->get('description')" class="mt-2" />
    </div>
</div>

<div class="flex items-center justify-end mt-6">
    <a href="{{ route('expenses.index') }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4">
        {{ __('Cancel') }}
    </a>

    <x-primary-button>
        {{ $buttonText ?? 'Save Expense' }}
    </x-primary-button>
</div>
