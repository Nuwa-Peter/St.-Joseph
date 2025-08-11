@csrf
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div>
        <x-input-label for="academic_year" :value="__('Academic Year')" />
        <x-text-input id="academic_year" class="block mt-1 w-full" type="text" name="academic_year" :value="old('academic_year', $feeStructure->academic_year ?? '')" required autofocus placeholder="e.g., 2024/2025" />
        <x-input-error :messages="$errors->get('academic_year')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="fee_category_id" :value="__('Fee Category')" />
        <select name="fee_category_id" id="fee_category_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
            <option value="">Select a category</option>
            @foreach($feeCategories as $category)
                <option value="{{ $category->id }}" @selected(old('fee_category_id', $feeStructure->fee_category_id ?? '') == $category->id)>
                    {{ $category->name }}
                </option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('fee_category_id')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="class_level_id" :value="__('Class Level')" />
        <select name="class_level_id" id="class_level_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
            <option value="">Select a class level</option>
            @foreach($classLevels as $level)
                <option value="{{ $level->id }}" @selected(old('class_level_id', $feeStructure->class_level_id ?? '') == $level->id)>
                    {{ $level->name }}
                </option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('class_level_id')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="amount" :value="__('Amount')" />
        <x-text-input id="amount" class="block mt-1 w-full" type="number" step="0.01" name="amount" :value="old('amount', $feeStructure->amount ?? '')" required />
        <x-input-error :messages="$errors->get('amount')" class="mt-2" />
    </div>
</div>

<div class="flex items-center justify-end mt-6">
    <a href="{{ route('fee-structures.index') }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4">
        {{ __('Cancel') }}
    </a>

    <x-primary-button>
        {{ $buttonText ?? 'Save' }}
    </x-primary-button>
</div>
