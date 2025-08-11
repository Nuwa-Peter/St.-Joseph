<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Parent Portal') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-2xl font-semibold text-gray-800">Welcome, {{ $parent->name }}</h3>
                    <p class="text-gray-600">This is the portal for your child, {{ $student->name ?? 'N/A' }}.</p>
                </div>
            </div>

            @if($student)
            <div class="mt-8 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Academic Summary -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h4 class="font-semibold text-lg mb-2">Academic Snapshot</h4>
                    <p class="text-gray-600">Class: {{ $student->streams->first()->classLevel->name ?? 'N/A' }} {{ $student->streams->first()->name ?? '' }}</p>
                    {{-- Placeholder for grades --}}
                    <p class="mt-2 text-sm text-gray-500">Recent grades will be shown here.</p>
                </div>

                <!-- Fee Status -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                     <h4 class="font-semibold text-lg mb-2">Fee Status</h4>
                     @if($student->invoices->first())
                        <p>Latest Invoice Balance: <span class="font-bold">${{ number_format($student->invoices->first()->balance, 2) }}</span></p>
                        <p>Status: <span class="font-semibold capitalize">{{ str_replace('_', ' ', $student->invoices->first()->status) }}</span></p>
                     @else
                        <p>No invoices found.</p>
                     @endif
                </div>

                <!-- Recent Attendance -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h4 class="font-semibold text-lg mb-2">Recent Attendance</h4>
                    <ul class="list-disc pl-5">
                    @forelse($student->attendances as $attendance)
                        <li>{{ $attendance->created_at->format('M d, Y') }}: <span class="font-semibold">{{ ucfirst($attendance->status) }}</span></li>
                    @empty
                        <li>No recent attendance records.</li>
                    @endforelse
                    </ul>
                </div>

                <!-- Discipline -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h4 class="font-semibold text-lg mb-2">Recent Discipline Logs</h4>
                     <ul class="list-disc pl-5">
                    @forelse($student->disciplineLogs as $log)
                        <li>{{ $log->log_date->format('M d, Y') }}: <span class="font-semibold capitalize">{{ $log->type }}</span> - {{ Str::limit($log->description, 30) }}</li>
                    @empty
                        <li>No recent discipline records.</li>
                    @endforelse
                    </ul>
                </div>

                <!-- Announcements -->
                <div class="lg:col-span-2 bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h4 class="font-semibold text-lg mb-2">School Announcements</h4>
                    <div class="space-y-4">
                    @forelse($announcements as $announcement)
                        <div>
                            <h5 class="font-bold">{{ $announcement->title }}</h5>
                            <p class="text-sm text-gray-600">{{ $announcement->content }}</p>
                            <p class="text-xs text-gray-400 mt-1">Posted on {{ $announcement->start_date->format('M d, Y') }}</p>
                        </div>
                    @empty
                        <p>No current announcements.</p>
                    @endforelse
                    </div>
                </div>

            </div>
            @else
            <div class="mt-8 bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4" role="alert">
                <p class="font-bold">Awaiting Student Link</p>
                <p>Your account is not yet linked to a student. Please contact the school administration to complete your registration.</p>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>
