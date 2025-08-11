<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Enter Marks') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form action="{{ route('marks.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="assignment_id" value="{{ $assignment->id }}">

                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white">
                                <thead class="bg-gray-800 text-white">
                                    <tr>
                                        <th class="py-3 px-4 uppercase font-semibold text-sm text-left">Student Name</th>
                                        <th class="py-3 px-4 uppercase font-semibold text-sm text-left">Score</th>
                                    </tr>
                                </thead>
                                <tbody class="text-gray-700">
                                    @foreach($students as $student)
                                        <tr class="border-b">
                                            <td class="py-3 px-4">{{ $student->name }}</td>
                                            <td class="py-3 px-4">
                                                <x-text-input type="number" name="marks[{{ $student->id }}]" class="w-full" :value="$marks[$student->id] ?? ''" />
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-6">
                            <x-primary-button>
                                {{ __('Save Marks') }}
                            </x-primary-button>
                            <a href="{{ route('marks.index') }}" class="ml-4 text-gray-600 hover:underline">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
