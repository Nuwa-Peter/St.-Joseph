<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Income vs. Expenditure Report') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-semibold text-gray-700">Income vs. Expenditure</h3>
                        <a href="{{ route('reports.index') }}" class="text-sm text-gray-600 hover:text-gray-900">
                            &larr; Back to Reports
                        </a>
                    </div>

                    <!-- Filter Form -->
                    <form method="GET" action="{{ route('reports.income-vs-expenditure') }}" class="mb-6 p-4 bg-gray-50 rounded-lg">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                            <div>
                                <x-input-label for="start_date" :value="__('Start Date')" />
                                <x-text-input id="start_date" class="block mt-1 w-full" type="date" name="start_date" :value="$startDate" />
                            </div>
                            <div>
                                <x-input-label for="end_date" :value="__('End Date')" />
                                <x-text-input id="end_date" class="block mt-1 w-full" type="date" name="end_date" :value="$endDate" />
                            </div>
                            <div>
                                <x-primary-button>
                                    {{ __('Generate Report') }}
                                </x-primary-button>
                            </div>
                        </div>
                    </form>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-center">
                        <div class="bg-green-100 p-6 rounded-lg">
                            <h4 class="text-lg font-semibold text-green-800">Total Income</h4>
                            <p class="text-3xl font-bold text-green-900 mt-2">{{ number_format($totalIncome, 2) }}</p>
                        </div>
                        <div class="bg-red-100 p-6 rounded-lg">
                            <h4 class="text-lg font-semibold text-red-800">Total Expenditure</h4>
                            <p class="text-3xl font-bold text-red-900 mt-2">{{ number_format($totalExpenditure, 2) }}</p>
                        </div>
                        <div class="p-6 rounded-lg {{ $netResult >= 0 ? 'bg-blue-100' : 'bg-orange-100' }}">
                            <h4 class="text-lg font-semibold {{ $netResult >= 0 ? 'text-blue-800' : 'text-orange-800' }}">Net Result</h4>
                            <p class="text-3xl font-bold {{ $netResult >= 0 ? 'text-blue-900' : 'text-orange-900' }} mt-2">{{ number_format($netResult, 2) }}</p>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
