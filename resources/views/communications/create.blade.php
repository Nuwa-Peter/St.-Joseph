<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Send Bulk Communication') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form action="{{ route('communications.send') }}" method="POST">
                        @csrf

                        <!-- Delivery Method -->
                        <div class="mb-4">
                            <x-input-label for="method" :value="__('Delivery Method')" />
                            <select id="method" name="method" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="email">Email</option>
                                <option value="sms">SMS</option>
                            </select>
                            <x-input-error :messages="$errors->get('method')" class="mt-2" />
                        </div>

                        <!-- Recipients -->
                        <div class="mb-4">
                            <x-input-label for="recipients" :value="__('Recipients')" />
                            <select id="recipients" name="recipients" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="all_teachers">All Teachers</option>
                                <option value="all_parents">All Parents</option>
                                <option value="all_students">All Students</option>
                                <optgroup label="Streams">
                                    @foreach($streams as $stream)
                                        <option value="stream_{{ $stream->id }}">{{ $stream->classLevel->name }} {{ $stream->name }}</option>
                                    @endforeach
                                </optgroup>
                            </select>
                            <x-input-error :messages="$errors->get('recipients')" class="mt-2" />
                        </div>

                        <!-- Subject -->
                        <div class="mb-4">
                            <x-input-label for="subject" :value="__('Subject')" />
                            <x-text-input id="subject" class="block mt-1 w-full" type="text" name="subject" :value="old('subject')" required />
                            <x-input-error :messages="$errors->get('subject')" class="mt-2" />
                        </div>

                        <!-- Message -->
                        <div class="mb-4">
                            <x-input-label for="message" :value="__('Message')" />
                            <textarea id="message" name="message" rows="10" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('message') }}</textarea>
                            <x-input-error :messages="$errors->get('message')" class="mt-2" />
                        </div>

                        <div class="mt-6">
                            <x-primary-button>
                                {{ __('Send Message') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
