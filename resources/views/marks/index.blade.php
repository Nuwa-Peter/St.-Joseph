<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('My Mark Entry Assignments') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white">
                            <thead class="bg-gray-800 text-white">
                                <tr>
                                    <th class="py-3 px-4 uppercase font-semibold text-sm text-left">Class</th>
                                    <th class="py-3 px-4 uppercase font-semibold text-sm text-left">Subject & Paper</th>
                                    <th class="py-3 px-4 uppercase font-semibold text-sm text-left">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-700">
                                @forelse($assignments as $assignment)
                                    <tr class="border-b">
                                        <td class="py-3 px-4">{{ $assignment->class_level_name }} {{ $assignment->stream_name }}</td>
                                        <td class="py-3 px-4">{{ $assignment->subject_name }} - {{ $assignment->paper_name }}</td>
                                        <td class="py-3 px-4">
                                            <a href="{{ route('marks.enter', $assignment->assignment_id) }}" class="text-blue-500 hover:underline">Enter Marks</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="py-3 px-4 text-center">You have no mark entry assignments.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
