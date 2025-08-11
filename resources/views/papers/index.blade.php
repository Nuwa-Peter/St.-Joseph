<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Papers for') }} {{ $subject->name }}
            </h2>
            <a href="{{ route('subjects.papers.create', $subject) }}" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                Add New Paper
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
                                    <th class="py-3 px-4 uppercase font-semibold text-sm text-left">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-700">
                                @forelse($papers as $paper)
                                    <tr class="border-b">
                                        <td class="py-3 px-4">{{ $paper->name }}</td>
                                        <td class="py-3 px-4">
                                            <a href="{{ route('papers.edit', $paper) }}" class="text-yellow-500 hover:underline ml-4">Edit</a>
                                            <form action="{{ route('papers.destroy', $paper) }}" method="POST" class="inline-block ml-4" onsubmit="return confirm('Are you sure you want to delete this paper?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-500 hover:underline">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="py-3 px-4 text-center">No papers found for this subject.</td>
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
