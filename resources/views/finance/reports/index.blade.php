<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Financial Reports') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-700 mb-6">Select a Report to View</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <!-- Outstanding Balances Report -->
                        <a href="{{ route('reports.outstanding-balances') }}" class="block p-6 bg-white border border-gray-200 rounded-lg shadow hover:bg-gray-100">
                            <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900">Outstanding Balances</h5>
                            <p class="font-normal text-gray-700">View a list of all students with outstanding fee balances.</p>
                        </a>

                        <!-- Payment Summaries Report -->
                        <a href="{{ route('reports.payment-summaries') }}" class="block p-6 bg-white border border-gray-200 rounded-lg shadow hover:bg-gray-100">
                            <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900">Payment Summaries</h5>
                            <p class="font-normal text-gray-700">See all payments received within a specific date range.</p>
                        </a>

                        <!-- Income vs. Expenditure Report -->
                        <a href="{{ route('reports.income-vs-expenditure') }}" class="block p-6 bg-white border border-gray-200 rounded-lg shadow hover:bg-gray-100">
                            <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900">Income vs. Expenditure</h5>
                            <p class="font-normal text-gray-700">Compare total income from fees against total school expenditures.</p>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
