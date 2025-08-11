<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Class Levels') }}
            </h2>
            <a href="{{ route('class-levels.create') }}" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                Add New Class Level
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white">
                            <thead class="bg-gray-800 text-white">
                                <tr>
                                    <th class="py-3 px-4 uppercase font-semibold text-sm text-left">Name</th>
                                    <th class="py-3 px-4 uppercase font-semibold text-sm text-left">Number of Streams</th>
                                    <th class="py-3 px-4 uppercase font-semibold text-sm text-left">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-700">
                                @forelse($classLevels as $classLevel)
                                    <tr class="border-b">
                                        <td class="py-3 px-4">{{ $classLevel->name }}</td>
                                        <td class="py-3 px-4">{{ $classLevel->streams_count }}</td>
                                        <td class="py-3 px-4">
                                            <a href="{{ route('class-levels.streams.index', $classLevel) }}" class="text-blue-500 hover:underline">View Streams</a>
                                            <a href="{{ route('class-levels.edit', $classLevel) }}" class="text-yellow-500 hover:underline ml-4">Edit</a>
                                            <button @click.prevent="$dispatch('open-delete-modal', { action: '{{ route('class-levels.destroy', $classLevel) }}' })" class="text-red-500 hover:underline ml-4">Delete</button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="py-3 px-4 text-center">No class levels found.</td>
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
