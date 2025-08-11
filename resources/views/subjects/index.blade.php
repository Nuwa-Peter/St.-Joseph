<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Subjects') }}
            </h2>
            <a href="{{ route('subjects.create') }}" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                Add New Subject
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
                                    <th class="py-3 px-4 uppercase font-semibold text-sm text-left">Code</th>
                                    <th class="py-3 px-4 uppercase font-semibold text-sm text-left">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-700">
                                @forelse($subjects as $subject)
                                    <tr class="border-b">
                                        <td class="py-3 px-4">{{ $subject->name }}</td>
                                        <td class="py-3 px-4">{{ $subject->code }}</td>
                                        <td class="py-3 px-4">
                                            <a href="{{ route('subjects.manage-papers', $subject) }}" class="text-blue-500 hover:underline">Manage Papers</a>
                                            <a href="{{ route('subjects.edit', $subject) }}" class="text-yellow-500 hover:underline ml-4">Edit</a>
                                            <button @click.prevent="$dispatch('open-delete-modal', { action: '{{ route('subjects.destroy', $subject) }}' })" class="text-red-500 hover:underline ml-4">Delete</button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="py-3 px-4 text-center">No subjects found.</td>
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
