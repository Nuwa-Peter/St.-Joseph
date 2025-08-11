<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Assign Teacher to Paper') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form action="{{ route('teacher-assignments.store') }}" method="POST">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <x-input-label for="teacher" :value="__('Teacher')" />
                                <select id="teacher" name="user_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    @foreach($teachers as $teacher)
                                        <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <x-input-label for="stream" :value="__('Stream')" />
                                <select id="stream" name="stream_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    @foreach($streams as $stream)
                                        <option value="{{ $stream->id }}">{{ $stream->classLevel->name }} {{ $stream->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <x-input-label for="paper" :value="__('Paper')" />
                                <select id="paper" name="paper_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    @foreach($papers as $paper)
                                        <option value="{{ $paper->id }}">{{ $paper->subject->name }} - {{ $paper->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="mt-6">
                            <x-primary-button>
                                {{ __('Assign') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="mt-8 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Current Assignments</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white">
                            <thead class="bg-gray-800 text-white">
                                <tr>
                                    <th class="py-3 px-4 uppercase font-semibold text-sm text-left">Teacher</th>
                                    <th class="py-3 px-4 uppercase font-semibold text-sm text-left">Class</th>
                                    <th class="py-3 px-4 uppercase font-semibold text-sm text-left">Subject & Paper</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-700">
                                @forelse($assignments as $assignment)
                                    <tr class="border-b">
                                        <td class="py-3 px-4">{{ $assignment->first_name }} {{ $assignment->last_name }}</td>
                                        <td class="py-3 px-4">{{ $assignment->class_level_name }} {{ $assignment->stream_name }}</td>
                                        <td class="py-3 px-4">{{ $assignment->subject_name }} - {{ $assignment->paper_name }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="py-3 px-4 text-center">No assignments found.</td>
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
