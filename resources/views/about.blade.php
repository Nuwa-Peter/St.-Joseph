<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('About This System') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">St. Joseph's VSS Management System</h3>
                    <p class="text-gray-600 mb-4">
                        This is a comprehensive school management system designed to streamline the administrative and academic processes of St. Joseph's Vocational Secondary School.
                    </p>
                    <h4 class="text-lg font-semibold text-gray-800 mt-6 mb-2">Version</h4>
                    <p class="text-gray-600">
                        Version 1.1.0
                    </p>
                    <h4 class="text-lg font-semibold text-gray-800 mt-6 mb-2">Modules Included:</h4>
                    <ul class="list-disc pl-5 space-y-2 text-gray-600">
                        <li>User Management (Students, Teachers, Staff)</li>
                        <li>Academic Core (Classes, Streams, Subjects)</li>
                        <li>Student Lifecycle & Documents (Report Cards, ID Cards)</li>
                        <li>Financial Management (Fees, Invoices, Expenses, Reports)</li>
                        <li>Student Welfare & Activities (Discipline, Health, Hostels, Clubs)</li>
                        <li>Library & Resource Management</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
