<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Teacher Attendance Records') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form action="{{ route('attendance.records') }}" method="GET" class="mb-6">
                        <div class="flex items-end gap-4">
                            <div>
                                <x-input-label for="date" :value="__('Filter by Date')" />
                                <x-text-input id="date" class="block mt-1 w-full" type="date" name="date" :value="request('date')" />
                            </div>
                            <div>
                                <x-primary-button>
                                    {{ __('Filter') }}
                                </x-primary-button>
                            </div>
                        </div>
                    </form>

                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white">
                            <thead class="bg-gray-800 text-white">
                                <tr>
                                    <th class="py-3 px-4 uppercase font-semibold text-sm text-left">Teacher</th>
                                    <th class="py-3 px-4 uppercase font-semibold text-sm text-left">Date</th>
                                    <th class="py-3 px-4 uppercase font-semibold text-sm text-left">Check In</th>
                                    <th class="py-3 px-4 uppercase font-semibold text-sm text-left">Check Out</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-700">
                                @forelse($attendances as $attendance)
                                    <tr class="border-b">
                                        <td class="py-3 px-4">{{ $attendance->user->name }}</td>
                                        <td class="py-3 px-4">{{ $attendance->date->format('d-m-Y') }}</td>
                                        <td class="py-3 px-4">{{ $attendance->check_in?->format('H:i:s') }}</td>
                                        <td class="py-3 px-4">{{ $attendance->check_out?->format('H:i:s') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="py-3 px-4 text-center">No attendance records found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $attendances->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
