<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Student Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-2xl font-semibold text-gray-800">Welcome, {{ $student->name }}</h3>
                    <p class="text-gray-600">Here is your summary and quick access to your resources.</p>
                </div>
            </div>

            <div class="mt-8 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Academic Summary -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h4 class="font-semibold text-lg mb-2">My Class & Subjects</h4>
                    <p class="text-gray-600">Class: {{ $student->streams->first()->classLevel->name ?? 'N/A' }} {{ $student->streams->first()->name ?? '' }}</p>
                    <p class="font-semibold mt-2">Subjects:</p>
                    <ul class="list-disc pl-5 text-sm">
                    @forelse($student->streams->first()->subjects ?? [] as $subject)
                        <li>{{ $subject->name }}</li>
                    @empty
                        <li>No subjects assigned.</li>
                    @endforelse
                    </ul>
                </div>

                <!-- Fee Status -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                     <h4 class="font-semibold text-lg mb-2">My Fee Status</h4>
                     @if($student->invoices->first())
                        <p>Latest Invoice Balance: <span class="font-bold">${{ number_format($student->invoices->first()->balance, 2) }}</span></p>
                        <p>Status: <span class="font-semibold capitalize">{{ str_replace('_', ' ', $student->invoices->first()->status) }}</span></p>
                     @else
                        <p>No invoices found.</p>
                     @endif
                </div>

                <!-- Educational Videos -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h4 class="font-semibold text-lg mb-2">Recent Videos</h4>
                    <ul class="list-disc pl-5">
                    @forelse($student->streams->first()->videos ?? [] as $video)
                        <li><a href="{{ route('videos.index') }}" class="text-blue-600 hover:underline">{{ $video->title }}</a></li>
                    @empty
                        <li>No recent videos.</li>
                    @endforelse
                    </ul>
                </div>

                <!-- Announcements -->
                <div class="lg:col-span-3 bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
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
        </div>
    </div>
</x-app-layout>
