<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('AI-Powered Intelligence') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-700 mb-6">Analytical Reports</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                        <a href="{{ route('ai.student-performance') }}" class="block p-6 bg-white border border-gray-200 rounded-lg shadow hover:bg-gray-100">
                            <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900">Student Performance Prediction</h5>
                            <p class="font-normal text-gray-700">Use historical data to identify students who may be at risk of failing.</p>
                        </a>

                        <!-- Placeholder for next report -->
                        <div class="block p-6 bg-gray-50 border border-gray-200 rounded-lg shadow">
                            <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-400">Subject Performance Analysis</h5>
                            <p class="font-normal text-gray-500">Analyze trends in subject scores over time (coming soon).</p>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
