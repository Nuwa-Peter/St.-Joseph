<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Book Details') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-6">
                <a href="{{ route('books.index') }}" class="text-sm text-gray-600 hover:text-gray-900">
                    &larr; Back to Catalog
                </a>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="text-2xl font-bold text-gray-900">{{ $book->title }}</h3>
                            <p class="text-lg text-gray-600">{{ $book->author }}</p>
                        </div>
                        <div class="text-right">
                            <a href="{{ route('books.edit', $book) }}" class="inline-flex items-center px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                                Edit Book
                            </a>
                        </div>
                    </div>

                    <div class="mt-6 border-t pt-6">
                        <dl class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">ISBN</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $book->isbn ?? 'N/A' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Publisher</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $book->publisher ?? 'N/A' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Published Year</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $book->published_year ?? 'N/A' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Total Quantity</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $book->quantity }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Quantity Available</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $book->available_quantity }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>

            <div class="mt-8">
                <h3 class="text-lg font-semibold text-gray-700 mb-4">Checkout History</h3>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="py-2 px-4 text-left">Student</th>
                                    <th class="py-2 px-4 text-left">Checkout Date</th>
                                    <th class="py-2 px-4 text-left">Due Date</th>
                                    <th class="py-2 px-4 text-left">Return Date</th>
                                    <th class="py-2 px-4 text-left">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($book->checkouts->sortByDesc('checkout_date') as $checkout)
                                    <tr class="border-b">
                                        <td class="py-2 px-4">{{ $checkout->student->name }}</td>
                                        <td class="py-2 px-4">{{ $checkout->checkout_date->format('M d, Y') }}</td>
                                        <td class="py-2 px-4">{{ $checkout->due_date->format('M d, Y') }}</td>
                                        <td class="py-2 px-4">{{ $checkout->returned_date ? $checkout->returned_date->format('M d, Y') : '' }}</td>
                                        <td class="py-2 px-4">
                                            @if($checkout->returned_date)
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                    Returned
                                                </span>
                                            @elseif($checkout->due_date->isPast())
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                    Overdue
                                                </span>
                                            @else
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                    Checked Out
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-4 px-4 text-center text-gray-500">This book has no checkout history.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
