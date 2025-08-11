<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Assign Student to Room') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    @if(session('error'))
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <span class="block sm:inline">{{ session('error') }}</span>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('room-assignments.store') }}">
                        @csrf
                        <div class="grid grid-cols-1 gap-6">
                            <div>
                                <x-input-label for="user_id" :value="__('Select Student')" />
                                <select name="user_id" id="user_id" class="tom-select" required>
                                    <option value="">Select a student</option>
                                    @foreach($students as $student)
                                        <option value="{{ $student->id }}" @selected(old('user_id') == $student->id)>
                                            {{ $student->name }} ({{ $student->unique_id }})
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('user_id')" class="mt-2" />
                                <p class="text-sm text-gray-500 mt-1">Only students not currently assigned to a room are shown.</p>
                            </div>

                            <div>
                                <x-input-label for="dormitory_room_id" :value="__('Select Room')" />
                                <select name="dormitory_room_id" id="dormitory_room_id" class="tom-select" required>
                                    <option value="">Select a room</option>
                                    @foreach($rooms->groupBy('dormitory.name') as $dormitoryName => $dormRooms)
                                        <optgroup label="{{ $dormitoryName }}">
                                            @foreach($dormRooms as $room)
                                                <option value="{{ $room->id }}" @selected(old('dormitory_room_id') == $room->id)>
                                                    Room {{ $room->room_number }} (Capacity: {{ $room->capacity }})
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('dormitory_room_id')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="academic_year" :value="__('Academic Year')" />
                                <x-text-input id="academic_year" class="block mt-1 w-full" type="text" name="academic_year" :value="old('academic_year', date('Y').'/'.(date('Y')+1))" required placeholder="e.g., 2024/2025" />
                                <x-input-error :messages="$errors->get('academic_year')" class="mt-2" />
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('room-assignments.index') }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4">
                                {{ __('Cancel') }}
                            </a>
                            <x-primary-button>
                                {{ __('Assign Student') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
