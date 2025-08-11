<x-guest-layout>
    <div class="p-6 bg-white border-b border-gray-200">
        <div class="text-center">
            <img class="h-32 w-32 rounded-full object-cover mx-auto mb-4" src="{{ $user->photo ? asset('storage/' . $user->photo) : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&color=7F9CF5&background=EBF4FF&size=128' }}" alt="{{ $user->name }}">
            <h2 class="text-2xl font-bold">{{ $user->name }}</h2>
            <p class="text-gray-600">{{ $user->unique_id }}</p>
            <p class="text-gray-600 capitalize">{{ $user->role->value }}</p>
        </div>

        <div class="mt-6 border-t border-gray-200 pt-6">
            <dl class="grid grid-cols-1 gap-x-4 gap-y-8 sm:grid-cols-2">
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500">Full Name</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $user->name }}</dd>
                </div>
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500">Email</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $user->email }}</dd>
                </div>
                @if($user->lin)
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500">Learner ID (LIN)</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $user->lin }}</dd>
                </div>
                @endif
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500">Gender</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $user->gender }}</dd>
                </div>
            </dl>
        </div>
    </div>
</x-guest-layout>
