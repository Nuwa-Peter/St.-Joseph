<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Student Performance Prediction') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-6">
                <a href="{{ route('ai.index') }}" class="text-sm text-gray-600 hover:text-gray-900">
                    &larr; Back to AI Reports
                </a>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">Prediction Results</h3>

                    @if(isset($error))
                        <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4" role="alert">
                            <p class="font-bold">Could not generate report</p>
                            <p>{{ $error }}</p>
                        </div>
                    @else
                        <div class="mb-6 p-4 border rounded-lg bg-blue-50">
                            <h4 class="font-semibold">Model Performance</h4>
                            <p>A Support Vector Classifier (SVC) model was trained on historical student data.</p>
                            <p class="mt-2">Prediction Accuracy on test data: <span class="font-bold text-xl">{{ number_format($accuracy * 100, 2) }}%</span></p>
                        </div>

                        <div>
                            <h4 class="font-semibold">Students Predicted to Be At-Risk</h4>
                            <p class="text-sm text-gray-600 mb-4">Based on the model, the following students from the test set were identified as potentially at risk of failing. In a real-world application, this would be run on current students.</p>

                            @if(empty($atRiskStudents))
                                <p class="text-gray-500">No students were predicted to be at-risk in the test sample. The model is performing well!</p>
                            @else
                                <div class="overflow-x-auto">
                                    <table class="min-w-full bg-white">
                                        <thead class="bg-gray-100">
                                            <tr>
                                                <th class="py-2 px-4 text-left">Features (Avg. Mark)</th>
                                                <th class="py-2 px-4 text-left">Actual Result</th>
                                                <th class="py-2 px-4 text-left">Predicted Result</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($atRiskStudents as $student)
                                            <tr class="border-b">
                                                <td class="py-2 px-4">{{ $student['features'][0] }}</td>
                                                <td class="py-2 px-4"><span class="font-semibold capitalize text-green-600">{{ $student['actual'] }}</span></td>
                                                <td class="py-2 px-4"><span class="font-semibold capitalize text-red-600">fail</span></td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
