<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Generate New Invoice') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Invoice Details</h3>

                    @if(session('error'))
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <span class="block sm:inline">{{ session('error') }}</span>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('invoices.store') }}">
                        @csrf
                        <div class="grid grid-cols-1 gap-6">
                            <div>
                                <x-input-label for="user_id" :value="__('Select Student')" />
                                <select name="user_id" id="user_id" class="tom-select" required>
                                    <option value="">Search for a student...</option>
                                    @foreach($students as $student)
                                        <option value="{{ $student->id }}" @selected(old('user_id') == $student->id)>
                                            {{ $student->last_name }}, {{ $student->first_name }} ({{ $student->unique_id }})
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('user_id')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="academic_year" :value="__('Academic Year')" />
                                <x-text-input id="academic_year" class="block mt-1 w-full" type="text" name="academic_year" :value="old('academic_year', date('Y').'/'.(date('Y')+1))" required placeholder="e.g., 2024/2025" />
                                <x-input-error :messages="$errors->get('academic_year')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="term" :value="__('Term')" />
                                <select name="term" id="term" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                    <option value="Term 1" @selected(old('term') == 'Term 1')>Term 1</option>
                                    <option value="Term 2" @selected(old('term') == 'Term 2')>Term 2</option>
                                    <option value="Term 3" @selected(old('term') == 'Term 3')>Term 3</option>
                                </select>
                                <x-input-error :messages="$errors->get('term')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="due_date" :value="__('Due Date')" />
                                <x-text-input id="due_date" class="block mt-1 w-full" type="date" name="due_date" :value="old('due_date')" required />
                                <x-input-error :messages="$errors->get('due_date')" class="mt-2" />
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('invoices.index') }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4">
                                {{ __('Cancel') }}
                            </a>
                            <x-primary-button>
                                {{ __('Generate Invoice') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
