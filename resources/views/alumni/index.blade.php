<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Alumni Network') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">Alumni Directory</h3>

                     <!-- Search Form -->
                    <form method="GET" action="{{ route('alumni.index') }}" class="mb-6">
                        <div class="flex items-end">
                            <div class="flex-grow">
                                <x-input-label for="search" :value="__('Search by Name or Graduation Year')" />
                                <x-text-input id="search" class="block mt-1 w-full" type="text" name="search" :value="request('search')" />
                            </div>
                            <div class="ml-4">
                                <x-primary-button>
                                    {{ __('Search') }}
                                </x-primary-button>
                            </div>
                        </div>
                    </form>

                    <div class="overflow-x-auto bg-white rounded-lg shadow overflow-y-auto relative">
                        <table class="border-collapse table-auto w-full whitespace-no-wrap bg-white table-striped relative">
                            <thead>
                                <tr class="text-left">
                                    <th class="bg-gray-50 sticky top-0 border-b border-gray-200 px-6 py-3 text-gray-600 font-bold tracking-wider uppercase text-xs">Name</th>
                                    <th class="bg-gray-50 sticky top-0 border-b border-gray-200 px-6 py-3 text-gray-600 font-bold tracking-wider uppercase text-xs">Graduation Year</th>
                                    <th class="bg-gray-50 sticky top-0 border-b border-gray-200 px-6 py-3 text-gray-600 font-bold tracking-wider uppercase text-xs">Email</th>
                                    <th class="bg-gray-50 sticky top-0 border-b border-gray-200 px-6 py-3 text-gray-600 font-bold tracking-wider uppercase text-xs">Phone Number</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($alumni as $alum)
                                    <tr>
                                        <td class="border-dashed border-t border-gray-200 px-6 py-3">{{ $alum->name }}</td>
                                        <td class="border-dashed border-t border-gray-200 px-6 py-3">{{ $alum->graduation_year }}</td>
                                        <td class="border-dashed border-t border-gray-200 px-6 py-3">{{ $alum->email }}</td>
                                        <td class="border-dashed border-t border-gray-200 px-6 py-3">{{ $alum->phone_number ?? 'N/A' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-10 px-6 text-gray-500">
                                            No alumni found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                     <div class="mt-4">
                        {{ $alumni->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
