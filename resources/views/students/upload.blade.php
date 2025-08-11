<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Upload Students from Excel') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Instructions</h3>
                        <p class="mt-1 text-sm text-gray-600">
                            Please ensure your Excel file has the following columns in this exact order:
                            <code class="text-sm font-mono bg-gray-200 p-1 rounded">first_name</code>,
                            <code class="text-sm font-mono bg-gray-200 p-1 rounded">last_name</code>,
                            <code class="text-sm font-mono bg-gray-200 p-1 rounded">other_name</code>,
                            <code class="text-sm font-mono bg-gray-200 p-1 rounded">lin</code>,
                            <code class="text-sm font-mono bg-gray-200 p-1 rounded">email</code>,
                            <code class="text-sm font-mono bg-gray-200 p-1 rounded">gender</code>.
                        </p>
                        <p class="mt-2 text-sm text-gray-600">The first row of the file should be the header row with these names.</p>
                    </div>
                    <form action="{{ route('students.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div>
                            <x-input-label for="file" :value="__('Excel File')" />
                            <input id="file" name="file" type="file" class="block mt-1 w-full" required accept=".xlsx, .xls">
                            <x-input-error :messages="$errors->get('file')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <x-primary-button>
                                {{ __('Upload and Import') }}
                            </x-primary-button>
                            <a href="{{ route('students.index') }}" class="ml-4 text-gray-600 hover:underline">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
