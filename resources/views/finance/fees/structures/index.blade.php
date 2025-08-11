<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Fee Structures') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-semibold text-gray-700">Manage Fee Structures</h3>
                        <a href="{{ route('fee-structures.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                            {{ __('Add New Structure') }}
                        </a>
                    </div>

                    @if(session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                    @endif

                    <div class="overflow-x-auto bg-white rounded-lg shadow overflow-y-auto relative">
                        <table class="border-collapse table-auto w-full whitespace-no-wrap bg-white table-striped relative">
                            <thead>
                                <tr class="text-left">
                                    <th class="bg-gray-50 sticky top-0 border-b border-gray-200 px-6 py-3 text-gray-600 font-bold tracking-wider uppercase text-xs">
                                        Academic Year
                                    </th>
                                    <th class="bg-gray-50 sticky top-0 border-b border-gray-200 px-6 py-3 text-gray-600 font-bold tracking-wider uppercase text-xs">
                                        Fee Category
                                    </th>
                                    <th class="bg-gray-50 sticky top-0 border-b border-gray-200 px-6 py-3 text-gray-600 font-bold tracking-wider uppercase text-xs">
                                        Class Level
                                    </th>
                                    <th class="bg-gray-50 sticky top-0 border-b border-gray-200 px-6 py-3 text-gray-600 font-bold tracking-wider uppercase text-xs">
                                        Amount
                                    </th>
                                    <th class="bg-gray-50 sticky top-0 border-b border-gray-200 px-6 py-3 text-gray-600 font-bold tracking-wider uppercase text-xs">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($feeStructures as $structure)
                                    <tr>
                                        <td class="border-dashed border-t border-gray-200 px-6 py-3">
                                            {{ $structure->academic_year }}
                                        </td>
                                        <td class="border-dashed border-t border-gray-200 px-6 py-3">
                                            {{ $structure->feeCategory->name }}
                                        </td>
                                        <td class="border-dashed border-t border-gray-200 px-6 py-3">
                                            {{ $structure->classLevel->name }}
                                        </td>
                                        <td class="border-dashed border-t border-gray-200 px-6 py-3">
                                            {{ number_format($structure->amount, 2) }}
                                        </td>
                                        <td class="border-dashed border-t border-gray-200 px-6 py-3">
                                            <a href="{{ route('fee-structures.edit', $structure) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                            <form action="{{ route('fee-structures.destroy', $structure) }}" method="POST" class="inline-block ml-4" onsubmit="return confirm('Are you sure you want to delete this fee structure?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-10 px-6 text-gray-500">
                                            No fee structures found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $feeStructures->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
