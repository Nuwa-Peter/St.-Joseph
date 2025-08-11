<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('General School Inventory') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-semibold text-gray-700">Manage Inventory Items</h3>
                        <a href="{{ route('inventory.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                            {{ __('Add New Item') }}
                        </a>
                    </div>

                    @if(session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                    @endif

                    <!-- Search and Filter Form -->
                    <form method="GET" action="{{ route('inventory.index') }}" class="mb-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                            <div>
                                <x-input-label for="search" :value="__('Search by Name or Serial No.')" />
                                <x-text-input id="search" class="block mt-1 w-full" type="text" name="search" :value="request('search')" />
                            </div>
                            <div>
                                <x-input-label for="category" :value="__('Filter by Category')" />
                                <select name="category" id="category" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="">All Categories</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category }}" @selected(request('category') == $category)>{{ $category }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <x-primary-button>
                                    {{ __('Filter') }}
                                </x-primary-button>
                            </div>
                        </div>
                    </form>

                    <div class="overflow-x-auto bg-white rounded-lg shadow overflow-y-auto relative">
                        <table class="border-collapse table-auto w-full whitespace-no-wrap bg-white table-striped relative">
                            <thead>
                                <tr class="text-left">
                                    <th class="bg-gray-50 sticky top-0 border-b border-gray-200 px-6 py-3 text-gray-600 font-bold tracking-wider uppercase text-xs">Item Name</th>
                                    <th class="bg-gray-50 sticky top-0 border-b border-gray-200 px-6 py-3 text-gray-600 font-bold tracking-wider uppercase text-xs">Category</th>
                                    <th class="bg-gray-50 sticky top-0 border-b border-gray-200 px-6 py-3 text-gray-600 font-bold tracking-wider uppercase text-xs">Location</th>
                                    <th class="bg-gray-50 sticky top-0 border-b border-gray-200 px-6 py-3 text-gray-600 font-bold tracking-wider uppercase text-xs">Assigned To</th>
                                    <th class="bg-gray-50 sticky top-0 border-b border-gray-200 px-6 py-3 text-gray-600 font-bold tracking-wider uppercase text-xs">Condition</th>
                                    <th class="bg-gray-50 sticky top-0 border-b border-gray-200 px-6 py-3 text-gray-600 font-bold tracking-wider uppercase text-xs">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($inventoryItems as $item)
                                    <tr>
                                        <td class="border-dashed border-t border-gray-200 px-6 py-3">{{ $item->name }}</td>
                                        <td class="border-dashed border-t border-gray-200 px-6 py-3">{{ $item->category }}</td>
                                        <td class="border-dashed border-t border-gray-200 px-6 py-3">{{ $item->location }}</td>
                                        <td class="border-dashed border-t border-gray-200 px-6 py-3">{{ $item->assignedTo->name ?? 'N/A' }}</td>
                                        <td class="border-dashed border-t border-gray-200 px-6 py-3">{{ ucfirst($item->condition) }}</td>
                                        <td class="border-dashed border-t border-gray-200 px-6 py-3">
                                            <a href="{{ route('inventory.edit', $item) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-10 px-6 text-gray-500">
                                            No inventory items found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                     <div class="mt-4">
                        {{ $inventoryItems->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
