<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Send Bulk Messages') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900" x-data="{ messageType: 'email' }">
                    <form method="post" action="{{ route('bulk-messages.store') }}">
                        @csrf
                        <div class="space-y-6">
                            <div>
                                <x-input-label for="recipients" :value="__('Recipients')" />
                                <select id="recipients" name="recipients" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full" required>
                                    <option value="all_students">All Students</option>
                                    <option value="all_parents">All Parents</option>
                                    <option value="all_teachers">All Teachers</option>
                                    {{-- Add options for specific classes later --}}
                                </select>
                            </div>

                            <div>
                                <x-input-label :value="__('Message Type')" />
                                <div class="mt-2 space-x-4">
                                    <label class="inline-flex items-center">
                                        <input type="radio" x-model="messageType" name="message_type" value="email" class="form-radio">
                                        <span class="ml-2">Email</span>
                                    </label>
                                    <label class="inline-flex items-center">
                                        <input type="radio" x-model="messageType" name="message_type" value="sms" class="form-radio">
                                        <span class="ml-2">SMS</span>
                                    </label>
                                </div>
                            </div>

                            <div x-show="messageType === 'email'">
                                <x-input-label for="subject" :value="__('Subject')" />
                                <x-text-input id="subject" name="subject" type="text" class="mt-1 block w-full" :value="old('subject')" />
                                <x-input-error :messages="$errors->get('subject')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="message" :value="__('Message')" />
                                <textarea id="message" name="message" rows="8" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full" required>{{ old('message') }}</textarea>
                                <x-input-error :messages="$errors->get('message')" class="mt-2" />
                            </div>

                            <div class="flex items-center gap-4">
                                <x-primary-button>{{ __('Send Messages') }}</x-primary-button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
