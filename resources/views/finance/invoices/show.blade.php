<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Invoice Details') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Action Buttons -->
            <div class="flex justify-between items-center mb-4">
                <a href="{{ route('invoices.index') }}" class="text-sm text-gray-600 hover:text-gray-900">
                    &larr; Back to All Invoices
                </a>
                <div class="flex space-x-2">
                    {{-- Export buttons will be added in a later step --}}
                </div>
            </div>

            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif
             @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <!-- Invoice Header -->
                    <div class="flex justify-between items-start mb-6">
                        <div>
                            <h3 class="text-2xl font-semibold text-gray-800">Invoice #{{ $invoice->id }}</h3>
                            <p class="text-sm text-gray-500">
                                {{ $invoice->term }}, {{ $invoice->academic_year }}
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-lg font-semibold text-gray-700">St. Joseph's Vocational SS</p>
                            <p class="text-sm text-gray-500">Nyamityobora, Mbarara</p>
                        </div>
                    </div>

                    <!-- Student and Invoice Details -->
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-6 mb-8">
                        <div>
                            <h4 class="font-semibold text-gray-600">Billed To:</h4>
                            <p>{{ $invoice->student->name }}</p>
                            <p>Student ID: {{ $invoice->student->unique_id }}</p>
                            <p>{{ $invoice->student->email }}</p>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-600">Invoice Date:</h4>
                            <p>{{ $invoice->created_at->format('M d, Y') }}</p>
                            <h4 class="font-semibold text-gray-600 mt-2">Due Date:</h4>
                            <p>{{ $invoice->due_date->format('M d, Y') }}</p>
                        </div>
                        <div class="text-right">
                             <h4 class="font-semibold text-gray-600">Status:</h4>
                             <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full
                                @switch($invoice->status)
                                    @case('paid') bg-green-100 text-green-800 @break
                                    @case('partially_paid') bg-yellow-100 text-yellow-800 @break
                                    @case('unpaid') bg-red-100 text-red-800 @break
                                    @case('overdue') bg-purple-100 text-purple-800 @break
                                @endswitch">
                                {{ str_replace('_', ' ', Str::title($invoice->status)) }}
                            </span>
                        </div>
                    </div>

                    <!-- Invoice Items -->
                    <h4 class="font-semibold text-gray-600 mb-2">Invoice Items</h4>
                    <div class="overflow-x-auto mb-8">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($invoice->items as $item)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $item->description }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">{{ number_format($item->amount, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Invoice Totals -->
                    <div class="flex justify-end mb-8">
                        <div class="w-full max-w-xs">
                            <div class="flex justify-between py-2 border-b">
                                <span class="font-semibold text-gray-600">Subtotal:</span>
                                <span>{{ number_format($invoice->total_amount, 2) }}</span>
                            </div>
                            <div class="flex justify-between py-2 border-b">
                                <span class="font-semibold text-gray-600">Amount Paid:</span>
                                <span>-{{ number_format($invoice->amount_paid, 2) }}</span>
                            </div>
                            <div class="flex justify-between py-2 text-lg font-bold text-gray-800">
                                <span>Balance Due:</span>
                                <span>{{ number_format($invoice->balance, 2) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Record Payment Form -->
                    @if($invoice->status !== 'paid')
                    <div x-data="{ open: false }" class="my-6 p-4 border rounded-lg">
                        <button @click="open = !open" class="w-full flex justify-between items-center text-lg font-semibold text-left text-gray-800">
                            <span>Record a New Payment</span>
                            <span x-show="!open">&darr;</span>
                            <span x-show="open">&uarr;</span>
                        </button>
                        <div x-show="open" x-collapse class="mt-4">
                            <form action="{{ route('invoices.payments.store', $invoice) }}" method="POST">
                                @csrf
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <x-input-label for="amount" :value="__('Amount Paid')" />
                                        <x-text-input id="amount" class="block mt-1 w-full" type="number" name="amount" :value="old('amount', number_format($invoice->balance, 2, '.', ''))" required step="0.01" max="{{ $invoice->balance }}" />
                                        <x-input-error :messages="$errors->get('amount')" class="mt-2" />
                                    </div>
                                    <div>
                                        <x-input-label for="payment_date" :value="__('Payment Date')" />
                                        <x-text-input id="payment_date" class="block mt-1 w-full" type="date" name="payment_date" :value="old('payment_date', now()->format('Y-m-d'))" required />
                                        <x-input-error :messages="$errors->get('payment_date')" class="mt-2" />
                                    </div>
                                    <div>
                                        <x-input-label for="payment_method" :value="__('Payment Method')" />
                                        <select name="payment_method" id="payment_method" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                            <option value="Cash" @selected(old('payment_method') == 'Cash')>Cash</option>
                                            <option value="Bank Transfer" @selected(old('payment_method') == 'Bank Transfer')>Bank Transfer</option>
                                            <option value="Mobile Money" @selected(old('payment_method') == 'Mobile Money')>Mobile Money</option>
                                        </select>
                                        <x-input-error :messages="$errors->get('payment_method')" class="mt-2" />
                                    </div>
                                    <div>
                                        <x-input-label for="transaction_reference" :value="__('Transaction Reference (Optional)')" />
                                        <x-text-input id="transaction_reference" class="block mt-1 w-full" type="text" name="transaction_reference" :value="old('transaction_reference')" />
                                        <x-input-error :messages="$errors->get('transaction_reference')" class="mt-2" />
                                    </div>
                                    <div class="md:col-span-2">
                                        <x-input-label for="notes" :value="__('Notes (Optional)')" />
                                        <textarea name="notes" id="notes" rows="3" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('notes') }}</textarea>
                                        <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                                    </div>
                                </div>
                                <div class="flex justify-end mt-6">
                                    <x-primary-button>
                                        {{ __('Record Payment') }}
                                    </x-primary-button>
                                </div>
                            </form>
                        </div>
                    </div>
                    @endif

                    <!-- Payment History -->
                    <h4 class="font-semibold text-gray-600 mb-2">Payment History</h4>
                    @if($invoice->payments->isNotEmpty())
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Method</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($invoice->payments as $payment)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $payment->payment_date->format('M d, Y') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $payment->payment_method }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $payment->transaction_reference ?? 'N/A' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right">{{ number_format($payment->amount, 2) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-gray-500">No payments have been recorded for this invoice.</p>
                    @endif

                    <!-- Delete Button for Unpaid Invoices -->
                    @if($invoice->status === 'unpaid')
                        <div class="mt-8 border-t pt-4">
                             <form action="{{ route('invoices.destroy', $invoice) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this invoice? This action cannot be undone.');">
                                @csrf
                                @method('DELETE')
                                <x-danger-button>
                                    Delete Invoice
                                </x-danger-button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
