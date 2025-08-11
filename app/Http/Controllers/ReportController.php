<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReportController extends Controller
{
    /**
     * Display a listing of available reports.
     */
    public function index(): View
    {
        return view('finance.reports.index');
    }

    /**
     * Show the outstanding balances report.
     */
    public function outstandingBalances(Request $request): View
    {
        $invoices = Invoice::with('student.streams.classLevel')
            ->whereIn('status', ['unpaid', 'partially_paid', 'overdue'])
            ->when($request->class_level_id, function ($query, $class_level_id) {
                $query->whereHas('student.streams', function ($q) use ($class_level_id) {
                    $q->where('class_level_id', $class_level_id);
                });
            })
            ->orderBy('user_id')
            ->get();

        $totalOutstanding = $invoices->sum('balance');
        $classLevels = \App\Models\ClassLevel::all();

        return view('finance.reports.outstanding_balances', compact('invoices', 'totalOutstanding', 'classLevels'));
    }

    /**
     * Show the payment summaries report.
     */
    public function paymentSummaries(Request $request): View
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $startDate = $request->start_date ?? now()->startOfMonth()->format('Y-m-d');
        $endDate = $request->end_date ?? now()->endOfMonth()->format('Y-m-d');

        $payments = Payment::with('invoice.student')
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->latest('payment_date')
            ->get();

        $totalPayments = $payments->sum('amount');

        return view('finance.reports.payment_summaries', compact('payments', 'totalPayments', 'startDate', 'endDate'));
    }

    /**
     * Show the income vs expenditure report.
     */
    public function incomeVsExpenditure(Request $request): View
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $startDate = $request->start_date ?? now()->startOfYear()->format('Y-m-d');
        $endDate = $request->end_date ?? now()->endOfYear()->format('Y-m-d');

        $totalIncome = Payment::whereBetween('payment_date', [$startDate, $endDate])->sum('amount');
        $totalExpenditure = Expense::whereBetween('expense_date', [$startDate, $endDate])->sum('amount');

        $netResult = $totalIncome - $totalExpenditure;

        return view('finance.reports.income_vs_expenditure', compact('totalIncome', 'totalExpenditure', 'netResult', 'startDate', 'endDate'));
    }
}
