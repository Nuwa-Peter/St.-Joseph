<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Manage Health & Medical Record') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">
                            Editing Health Record for: <span class="font-bold">{{ $student->name }}</span>
                        </h3>
                        <a href="{{ route('students.show', $student) }}" class="text-sm text-gray-600 hover:text-gray-900">
                            &larr; Back to Profile
                        </a>
                    </div>

                    <form action="{{ route('students.health-record.update', $student) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Emergency Contact -->
                            <div class="md:col-span-2">
                                <h4 class="text-md font-semibold text-gray-700 border-b pb-2 mb-4">Emergency Contact Information</h4>
                            </div>
                            <div>
                                <x-input-label for="emergency_contact_name" :value="__('Emergency Contact Full Name')" />
                                <x-text-input id="emergency_contact_name" class="block mt-1 w-full" type="text" name="emergency_contact_name" :value="old('emergency_contact_name', $healthRecord->emergency_contact_name)" required />
                                <x-input-error :messages="$errors->get('emergency_contact_name')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="emergency_contact_phone" :value="__('Emergency Contact Phone Number')" />
                                <x-text-input id="emergency_contact_phone" class="block mt-1 w-full" type="text" name="emergency_contact_phone" :value="old('emergency_contact_phone', $healthRecord->emergency_contact_phone)" required />
                                <x-input-error :messages="$errors->get('emergency_contact_phone')" class="mt-2" />
                            </div>

                            <!-- Medical Information -->
                            <div class="md:col-span-2 mt-6">
                                <h4 class="text-md font-semibold text-gray-700 border-b pb-2 mb-4">Medical Details</h4>
                            </div>
                            <div class="md:col-span-2">
                                <x-input-label for="allergies" :value="__('Known Allergies (leave blank if none)')" />
                                <textarea name="allergies" id="allergies" rows="3" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('allergies', $healthRecord->allergies) }}</textarea>
                                <x-input-error :messages="$errors->get('allergies')" class="mt-2" />
                            </div>
                            <div class="md:col-span-2">
                                <x-input-label for="chronic_conditions" :value="__('Chronic Conditions (e.g., Asthma, Diabetes - leave blank if none)')" />
                                <textarea name="chronic_conditions" id="chronic_conditions" rows="3" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('chronic_conditions', $healthRecord->chronic_conditions) }}</textarea>
                                <x-input-error :messages="$errors->get('chronic_conditions')" class="mt-2" />
                            </div>
                            <div class="md:col-span-2">
                                <x-input-label for="notes" :value="__('General Medical Notes (Optional)')" />
                                <textarea name="notes" id="notes" rows="4" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('notes', $healthRecord->notes) }}</textarea>
                                <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <x-primary-button>
                                {{ __('Save Health Record') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
