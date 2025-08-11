<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('User Management') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-end mb-4">
                        <a href="{{ route('users.create') }}" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">Create New User</a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white">
                            <thead class="bg-gray-800 text-white">
                                <tr>
                                    <th class="w-1/4 py-3 px-4 uppercase font-semibold text-sm text-left">Name</th>
                                    <th class="w-1/4 py-3 px-4 uppercase font-semibold text-sm text-left">Email</th>
                                    <th class="py-3 px-4 uppercase font-semibold text-sm text-left">Role</th>
                                    <th class="py-3 px-4 uppercase font-semibold text-sm text-left">Status</th>
                                    <th class="py-3 px-4 uppercase font-semibold text-sm text-left">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-700">
                                @foreach($users as $user)
                                    <tr class="border-b">
                                        <td class="py-3 px-4">{{ $user->name }}</td>
                                        <td class="py-3 px-4">{{ $user->email }}</td>
                                        <td class="py-3 px-4 capitalize">{{ $user->role->value }}</td>
                                        <td class="py-3 px-4">
                                            <span class="px-2 py-1 font-semibold leading-tight {{ $user->status === 'active' ? 'text-green-700 bg-green-100' : 'text-red-700 bg-red-100' }} rounded-full">
                                                {{ $user->status }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 flex items-center space-x-2">
                                            <a href="{{ route('users.edit', $user) }}" class="text-blue-600 hover:text-blue-900">
                                                <x-heroicon-o-pencil-square class="w-5 h-5" />
                                            </a>
                                            <button x-data x-on:click.prevent="$dispatch('open-delete-modal', { action: '{{ route('users.destroy', $user) }}' })" class="text-red-600 hover:text-red-900">
                                                <x-heroicon-o-trash class="w-5 h-5" />
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $users->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
