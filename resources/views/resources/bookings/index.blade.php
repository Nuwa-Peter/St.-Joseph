<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Resource Booking System') }}
        </h2>
    </x-slot>

    {{-- Add FullCalendar CSS and JS --}}
    @push('styles')
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js'></script>
    @endpush

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Booking Form -->
                <div class="md:col-span-1">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Book a Resource</h3>
                            <form action="{{ route('bookings.store') }}" method="POST">
                                @csrf
                                <div class="space-y-4">
                                    <div>
                                        <x-input-label for="resource_id" :value="__('Resource')" />
                                        <select id="resource_id" name="resource_id" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full" required>
                                            @foreach($resources as $resource)
                                                <option value="{{ $resource->id }}">{{ $resource->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <x-input-label for="title" :value="__('Booking Title / Purpose')" />
                                        <x-text-input id="title" name="title" type="text" class="mt-1 block w-full" :value="old('title')" required />
                                        <x-input-error :messages="$errors->get('title')" class="mt-2" />
                                    </div>
                                    <div>
                                        <x-input-label for="start_time" :value="__('Start Time')" />
                                        <x-text-input id="start_time" name="start_time" type="datetime-local" class="mt-1 block w-full" :value="old('start_time')" required />
                                        <x-input-error :messages="$errors->get('start_time')" class="mt-2" />
                                    </div>
                                    <div>
                                        <x-input-label for="end_time" :value="__('End Time')" />
                                        <x-text-input id="end_time" name="end_time" type="datetime-local" class="mt-1 block w-full" :value="old('end_time')" required />
                                        <x-input-error :messages="$errors->get('end_time')" class="mt-2" />
                                    </div>
                                    <div>
                                        <x-primary-button>
                                            {{ __('Book Now') }}
                                        </x-primary-button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Calendar Display -->
                <div class="md:col-span-2">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900">
                            <div id='calendar'></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                events: {!! $events !!},
                eventDidMount: function(info) {
                    // Tooltip for event details
                    var tooltip = new Tooltip(info.el, {
                        title: `Booked by: ${info.event.extendedProps.user}<br>Resource: ${info.event.extendedProps.resource}`,
                        html: true,
                        placement: 'top',
                        trigger: 'hover',
                        container: 'body'
                    });
                }
            });
            calendar.render();
        });
    </script>
    @endpush
</x-app-layout>
