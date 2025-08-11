<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Video Library') }}
            </h2>
            @if(Auth::user()->role->value === 'teacher')
                <a href="{{ route('videos.create') }}" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                    Upload New Video
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($videos as $video)
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-white border-b border-gray-200">
                            <video controls class="w-full">
                                <source src="{{ asset('storage/' . $video->path) }}" type="video/mp4">
                                Your browser does not support the video tag.
                            </video>
                            <h3 class="text-lg font-medium text-gray-900 mt-4">{{ $video->title }}</h3>
                            <p class="mt-1 text-sm text-gray-600">{{ $video->description }}</p>
                            @if(Auth::user()->role->value === 'teacher')
                                <p class="mt-2 text-xs text-gray-500">Uploaded by: You</p>
                            @else
                                <p class="mt-2 text-xs text-gray-500">Uploaded by: {{ $video->uploader->name }}</p>
                            @endif
                        </div>
                    </div>
                @empty
                    <p>No videos found.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
