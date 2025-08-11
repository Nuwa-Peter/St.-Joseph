<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Manage Club: ') . $club->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-6">
                <a href="{{ route('clubs.index') }}" class="text-sm text-gray-600 hover:text-gray-900">
                    &larr; Back to All Clubs
                </a>
            </div>

            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Member List -->
                <div class="md:col-span-2">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-white border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-700 mb-4">Club Members ({{ $club->members->count() }})</h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full bg-white">
                                    <thead class="bg-gray-100">
                                        <tr>
                                            <th class="py-2 px-4 text-left">Student Name</th>
                                            <th class="py-2 px-4 text-left">Student ID</th>
                                            <th class="py-2 px-4 text-left">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($club->members as $member)
                                            <tr class="border-b">
                                                <td class="py-2 px-4">{{ $member->name }}</td>
                                                <td class="py-2 px-4">{{ $member->unique_id }}</td>
                                                <td class="py-2 px-4">
                                                    <form action="{{ route('clubs.members.destroy', ['club' => $club, 'student' => $member]) }}" method="POST" onsubmit="return confirm('Are you sure you want to remove this member?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-red-600 hover:text-red-900">Remove</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="py-4 px-4 text-center text-gray-500">No members found.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Add Member Form -->
                <div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-white border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-700 mb-4">Add New Member</h3>
                            <form action="{{ route('clubs.members.store', $club) }}" method="POST">
                                @csrf
                                <div>
                                    <x-input-label for="user_id" :value="__('Select Student')" />
                                    <select name="user_id" id="user_id" class="tom-select" required>
                                        <option value="">Select a student</option>
                                        @foreach($students as $student)
                                            @if(!$club->members->contains($student))
                                                <option value="{{ $student->id }}">{{ $student->name }}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('user_id')" class="mt-2" />
                                </div>
                                <div class="flex justify-end mt-4">
                                    <x-primary-button>
                                        {{ __('Add Member') }}
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
