<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Notifications') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="space-y-4">
                        @forelse ($notifications as $notification)
                            <div class="p-4 rounded-md {{ $notification->read_at ? 'bg-gray-50' : 'bg-blue-50 border border-blue-200' }}">
                                <a href="{{ $notification->data['action_url'] ?? '#' }}" class="block hover:bg-gray-100">
                                    <p>{{ $notification->data['message'] }}</p>
                                    <p class="text-sm text-gray-500 mt-1">
                                        {{ $notification->created_at->diffForHumans() }}
                                    </p>
                                </a>
                            </div>
                        @empty
                            <p>You have no notifications.</p>
                        @endforelse
                    </div>

                    <div class="mt-6">
                        {{ $notifications->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
