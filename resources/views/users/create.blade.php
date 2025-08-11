<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create New User') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form action="{{ route('users.store') }}" method="POST" x-data="{ role: '{{ old('role', 'student') }}', selectedClass: null, streams: {{ json_encode($streams) }} }">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <x-input-label for="first_name" :value="__('First Name')" />
                                <x-text-input id="first_name" class="block mt-1 w-full" type="text" name="first_name" :value="old('first_name')" required autofocus />
                                <x-input-error :messages="$errors->get('first_name')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="last_name" :value="__('Last Name')" />
                                <x-text-input id="last_name" class="block mt-1 w-full" type="text" name="last_name" :value="old('last_name')" required />
                                <x-input-error :messages="$errors->get('last_name')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="email" :value="__('Email')" />
                                <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required />
                                <x-input-error :messages="$errors->get('email')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="role" :value="__('Role')" />
                                <select id="role" name="role" x-model="role" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    @foreach(\App\Enums\Role::cases() as $role)
                                        @if(\Illuminate\Support\Facades\Auth::user()->role === \App\Enums\Role::ROOT || $role->value !== 'root')
                                            <option value="{{ $role->value }}">{{ ucfirst($role->name) }}</option>
                                        @endif
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('role')" class="mt-2" />
                            </div>
                            <div x-show="role === 'student'" class="md:col-span-2 grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <x-input-label for="lin" :value="__('Learner ID Number (LIN)')" />
                                    <x-text-input id="lin" class="block mt-1 w-full" type="text" name="lin" :value="old('lin')" />
                                    <x-input-error :messages="$errors->get('lin')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="class_level_id" :value="__('Class')" />
                                    <select id="class_level_id" name="class_level_id" x-model="selectedClass" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                        <option value="">Select a Class</option>
                                        @foreach($classLevels as $classLevel)
                                            <option value="{{ $classLevel->id }}">{{ $classLevel->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <x-input-label for="stream_id" :value="__('Stream')" />
                                    <select id="stream_id" name="stream_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" :disabled="!selectedClass">
                                        <option value="">Select a stream</option>
                                        <template x-for="stream in streams.filter(s => s.class_level_id == selectedClass)">
                                            <option :value="stream.id" x-text="stream.name"></option>
                                        </template>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6">
                            <p class="text-sm text-gray-600">A default password ('password') will be set for the user.</p>
                        </div>

                        <div class="mt-4">
                            <x-primary-button>
                                {{ __('Create User') }}
                            </x-primary-button>
                            <a href="{{ route('users.index') }}" class="ml-4 text-gray-600 hover:underline">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
