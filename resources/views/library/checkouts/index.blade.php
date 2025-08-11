<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Book Checkouts') }}
            </h2>
            <a href="{{ route('checkouts.create') }}" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                New Checkout
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Filter Links -->
            <div class="mb-4">
                <a href="{{ route('checkouts.index') }}" class="px-3 py-1 rounded-md text-sm font-medium {{ !request('status') ? 'bg-blue-500 text-white' : 'text-gray-700' }}">All</a>
                <a href="{{ route('checkouts.index', ['status' => 'checked_out']) }}" class="ml-2 px-3 py-1 rounded-md text-sm font-medium {{ request('status') == 'checked_out' ? 'bg-yellow-500 text-white' : 'text-gray-700' }}">Checked Out</a>
                <a href="{{ route('checkouts.index', ['status' => 'overdue']) }}" class="ml-2 px-3 py-1 rounded-md text-sm font-medium {{ request('status') == 'overdue' ? 'bg-red-500 text-white' : 'text-gray-700' }}">Overdue</a>
                <a href="{{ route('checkouts.index', ['status' => 'returned']) }}" class="ml-2 px-3 py-1 rounded-md text-sm font-medium {{ request('status') == 'returned' ? 'bg-green-500 text-white' : 'text-gray-700' }}">Returned</a>
            </div>

            <!-- Checkouts Table -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Book Title</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Checkout Date</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Due Date</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th scope="col" class="relative px-6 py-3">
                                        <span class="sr-only">Actions</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($checkouts as $checkout)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $checkout->book->title }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $checkout->user->name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $checkout->checkout_date->format('d-m-Y') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $checkout->due_date->format('d-m-Y') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if ($checkout->status === 'returned')
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                    Returned
                                                </span>
                                            @elseif ($checkout->due_date < now() && $checkout->status !== 'returned')
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                    Overdue
                                                </span>
                                            @else
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                    Checked Out
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            @if ($checkout->status !== 'returned')
                                                <form action="{{ route('checkouts.update', $checkout) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="text-green-600 hover:text-green-900">Mark as Returned</button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-4 whitespace-nowrap text-center text-gray-500">
                                            No checkout records found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $checkouts->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
