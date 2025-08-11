<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice #{{ $invoice->id }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        .container { max-width: 800px; margin: auto; }
        .header { display: flex; justify-content: space-between; margin-bottom: 20px; }
        .header h1 { margin: 0; }
        .details { margin-bottom: 20px; }
        .details table { width: 100%; }
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .items-table th, .items-table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .items-table th { background-color: #f2f2f2; }
        .totals { float: right; width: 300px; }
        .totals table { width: 100%; }
        .totals td { padding: 5px; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
    <div class="container">
        <table style="width: 100%; margin-bottom: 20px;">
            <tr>
                <td style="width: 50%;">
                    <h1>Invoice #{{ $invoice->id }}</h1>
                    <p>{{ $invoice->term }}, {{ $invoice->academic_year }}</p>
                </td>
                <td style="width: 50%; text-align: right;">
                    <strong>St. Joseph's Vocational SS</strong><br>
                    Nyamityobora, Mbarara
                </td>
            </tr>
        </table>

        <table style="width: 100%; margin-bottom: 30px;">
            <tr>
                <td style="width: 33%;">
                    <strong>Billed To:</strong><br>
                    {{ $invoice->student->name }}<br>
                    Student ID: {{ $invoice->student->unique_id }}
                </td>
                <td style="width: 33%;">
                    <strong>Invoice Date:</strong> {{ $invoice->created_at->format('M d, Y') }}<br>
                    <strong>Due Date:</strong> {{ $invoice->due_date->format('M d, Y') }}
                </td>
                <td style="width: 33%; text-align: right;">
                    <strong>Status:</strong> {{ str_replace('_', ' ', Str::title($invoice->status)) }}
                </td>
            </tr>
        </table>

        <table class="items-table">
            <thead>
                <tr>
                    <th>Description</th>
                    <th class="text-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $item)
                    <tr>
                        <td>{{ $item->description }}</td>
                        <td class="text-right">{{ number_format($item->amount, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals">
            <table style="width: 100%;">
                <tr>
                    <td><strong>Subtotal:</strong></td>
                    <td class="text-right">{{ number_format($invoice->total_amount, 2) }}</td>
                </tr>
                <tr>
                    <td><strong>Amount Paid:</strong></td>
                    <td class="text-right">-{{ number_format($invoice->amount_paid, 2) }}</td>
                </tr>
                <tr>
                    <td style="border-top: 1px solid #333;"><strong>Balance Due:</strong></td>
                    <td class="text-right" style="border-top: 1px solid #333;"><strong>{{ number_format($invoice->balance, 2) }}</strong></td>
                </tr>
            </table>
        </div>

        <div style="clear:both;"></div>

        @if($invoice->payments->isNotEmpty())
            <h3 style="margin-top: 40px;">Payment History</h3>
            <table class="items-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Method</th>
                        <th>Reference</th>
                        <th class="text-right">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoice->payments as $payment)
                        <tr>
                            <td>{{ $payment->payment_date->format('M d, Y') }}</td>
                            <td>{{ $payment->payment_method }}</td>
                            <td>{{ $payment->transaction_reference ?? 'N/A' }}</td>
                            <td class="text-right">{{ number_format($payment->amount, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</body>
</html>
