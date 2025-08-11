<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Manage School Resources') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Resource List -->
                <div class="md:col-span-2">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-white border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-700 mb-4">Available Resources</h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full bg-white">
                                    <thead class="bg-gray-100">
                                        <tr>
                                            <th class="py-2 px-4 text-left">Name</th>
                                            <th class="py-2 px-4 text-left">Type</th>
                                            <th class="py-2 px-4 text-left">Location</th>
                                            <th class="py-2 px-4 text-left">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($resources as $resource)
                                            <tr class="border-b">
                                                <td class="py-2 px-4">{{ $resource->name }}</td>
                                                <td class="py-2 px-4">{{ $resource->type }}</td>
                                                <td class="py-2 px-4">{{ $resource->location }}</td>
                                                <td class="py-2 px-4">
                                                    <form action="{{ route('resources.destroy', $resource) }}" method="POST" onsubmit="return confirm('Are you sure?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="py-4 px-4 text-center text-gray-500">No resources found.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Add Resource Form -->
                <div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-white border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-700 mb-4">Add New Resource</h3>
                            <form action="{{ route('resources.store') }}" method="POST">
                                @csrf
                                <div>
                                    <x-input-label for="name" :value="__('Resource Name')" />
                                    <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required />
                                </div>
                                <div class="mt-4">
                                    <x-input-label for="type" :value="__('Type (e.g., Lab, Projector)')" />
                                    <x-text-input id="type" class="block mt-1 w-full" type="text" name="type" :value="old('type')" required />
                                </div>
                                <div class="mt-4">
                                    <x-input-label for="location" :value="__('Location (Optional)')" />
                                    <x-text-input id="location" class="block mt-1 w-full" type="text" name="location" :value="old('location')" />
                                </div>
                                 <div class="mt-4">
                                    <x-input-label for="description" :value="__('Description (Optional)')" />
                                    <textarea name="description" id="description" rows="3" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('description') }}</textarea>
                                </div>
                                <div class="flex justify-end mt-4">
                                    <x-primary-button>
                                        {{ __('Add Resource') }}
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
