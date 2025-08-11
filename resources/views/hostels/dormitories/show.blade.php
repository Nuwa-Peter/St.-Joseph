<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Manage Dormitory: ') . $dormitory->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-6">
                <a href="{{ route('dormitories.index') }}" class="text-sm text-gray-600 hover:text-gray-900">
                    &larr; Back to All Dormitories
                </a>
            </div>

            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Room List -->
                <div class="md:col-span-2">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-white border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-700 mb-4">Rooms in {{ $dormitory->name }}</h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full bg-white">
                                    <thead class="bg-gray-100">
                                        <tr>
                                            <th class="py-2 px-4 text-left">Room Number</th>
                                            <th class="py-2 px-4 text-left">Capacity</th>
                                            <th class="py-2 px-4 text-left">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($dormitory->rooms as $room)
                                            <tr class="border-b">
                                                <td class="py-2 px-4">{{ $room->room_number }}</td>
                                                <td class="py-2 px-4">{{ $room->capacity }}</td>
                                                <td class="py-2 px-4">
                                                    <form action="{{ route('dormitory-rooms.destroy', $room) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this room?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="py-4 px-4 text-center text-gray-500">No rooms found.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Add Room Form -->
                <div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-white border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-700 mb-4">Add New Room</h3>
                            <form action="{{ route('dormitories.rooms.store', $dormitory) }}" method="POST">
                                @csrf
                                <div>
                                    <x-input-label for="room_number" :value="__('Room Number / Name')" />
                                    <x-text-input id="room_number" class="block mt-1 w-full" type="text" name="room_number" :value="old('room_number')" required />
                                    <x-input-error :messages="$errors->get('room_number')" class="mt-2" />
                                </div>
                                <div class="mt-4">
                                    <x-input-label for="capacity" :value="__('Capacity')" />
                                    <x-text-input id="capacity" class="block mt-1 w-full" type="number" name="capacity" :value="old('capacity')" required min="1" />
                                    <x-input-error :messages="$errors->get('capacity')" class="mt-2" />
                                </div>
                                <div class="flex justify-end mt-4">
                                    <x-primary-button>
                                        {{ __('Add Room') }}
                                    </x-primary-button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
