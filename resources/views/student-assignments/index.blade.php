<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Assign Students to Streams') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form action="{{ route('student-assignments.index') }}" method="GET" class="mb-6">
                        <x-input-label for="stream" :value="__('Select a Stream to Manage')" />
                        <select id="stream" name="stream_id" class="block mt-1 w-full md:w-1/2 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" onchange="this.form.submit()">
                            <option value="">-- Select a Stream --</option>
                            @foreach($streams as $stream)
                                <option value="{{ $stream->id }}" @selected($selectedStream && $selectedStream->id == $stream->id)>
                                    {{ $stream->classLevel->name }} {{ $stream->name }}
                                </option>
                            @endforeach
                        </select>
                    </form>

                    @if($selectedStream)
                        <hr class="my-6">
                        <form action="{{ route('student-assignments.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="stream_id" value="{{ $selectedStream->id }}">

                            <h3 class="text-lg font-medium text-gray-900 mb-4">
                                Assign students to {{ $selectedStream->classLevel->name }} {{ $selectedStream->name }}
                            </h3>

                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                @foreach($students as $student)
                                    <label class="flex items-center p-2 border rounded-md">
                                        <input type="checkbox" name="students[]" value="{{ $student->id }}" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                            @checked($selectedStream->students->contains($student->id))>
                                        <span class="ml-2 text-sm text-gray-600">{{ $student->name }}</span>
                                    </label>
                                @endforeach
                            </div>

                            <div class="mt-6">
                                <x-primary-button>
                                    {{ __('Save Assignments') }}
                                </x-primary-button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
