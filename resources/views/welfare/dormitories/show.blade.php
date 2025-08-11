<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Manage Rooms for') }} <span class="font-bold">{{ $dormitory->name }}</span>
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Add New Room Form -->
            <div class="md:col-span-1">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Add New Room</h3>
                        <form action="{{ route('dormitories.rooms.store', $dormitory) }}" method="POST">
                            @csrf
                            <div class="space-y-4">
                                <div>
                                    <x-input-label for="room_number" :value="__('Room Number / Name')" />
                                    <x-text-input id="room_number" name="room_number" type="text" class="mt-1 block w-full" :value="old('room_number')" required />
                                    <x-input-error :messages="$errors->get('room_number')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="capacity" :value="__('Capacity')" />
                                    <x-text-input id="capacity" name="capacity" type="number" min="1" class="mt-1 block w-full" :value="old('capacity')" required />
                                    <x-input-error :messages="$errors->get('capacity')" class="mt-2" />
                                </div>
                                <div>
                                    <x-primary-button>
                                        {{ __('Add Room') }}
                                    </x-primary-button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Rooms List -->
            <div class="md:col-span-2">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Existing Rooms</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Room</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Capacity</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Occupants</th>
                                        <th class="relative px-6 py-3"><span class="sr-only">Delete</span></th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse ($dormitory->rooms as $room)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">{{ $room->room_number }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">{{ $room->capacity }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">{{ $room->occupants->count() }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <form action="{{ route('dormitory-rooms.destroy', $room) }}" method="POST" onsubmit="return confirm('Are you sure?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-6 py-4 text-center text-gray-500">No rooms added yet.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
