<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Assign Students to Dormitory Rooms') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Assignment Form -->
            <div class="md:col-span-1">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">New Assignment</h3>
                        <form action="{{ route('room-assignments.store') }}" method="POST">
                            @csrf
                            <div class="space-y-4">
                                <div>
                                    <x-input-label for="user_id" :value="__('Unassigned Student')" />
                                    <select id="user_id" name="user_id" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full" required>
                                        <option value="">Select a student</option>
                                        @foreach($students as $student)
                                            <option value="{{ $student->id }}">{{ $student->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <x-input-label for="dormitory_room_id" :value="__('Available Room')" />
                                    <select id="dormitory_room_id" name="dormitory_room_id" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full" required>
                                        <option value="">Select a room</option>
                                        @foreach($rooms as $room)
                                            <option value="{{ $room->id }}">{{ $room->dormitory->name }} - Room {{ $room->room_number }} ({{ $room->occupants->count() }}/{{ $room->capacity }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <x-input-label for="academic_year" :value="__('Academic Year')" />
                                    <x-text-input id="academic_year" name="academic_year" type="text" class="mt-1 block w-full" :value="date('Y')" required />
                                </div>
                                <div>
                                    <x-primary-button>
                                        {{ __('Assign Student') }}
                                    </x-primary-button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- This part is for showing already assigned students. It requires a different query in the controller. -->
            <!-- For now, I will leave this part out to keep the controller simple, but it can be added later. -->
            <div class="md:col-span-2">
                 <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <p class="text-gray-600">View assigned students by visiting the "Manage Dormitories" page and viewing a specific dormitory's rooms.</p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
