<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\User;
use App\Models\Stream;
use App\Models\FeeStructure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $invoices = Invoice::with('student')
            ->when($request->search, function ($query, $search) {
                $query->whereHas('student', function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('unique_id', 'like', "%{$search}%");
                });
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->latest()
            ->paginate(20);

        return view('finance.invoices.index', compact('invoices'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $students = User::where('role', 'student')->orderBy('last_name')->get();
        return view('finance.invoices.create', compact('students'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'academic_year' => 'required|string|max:255',
            'term' => 'required|string|max:255',
            'due_date' => 'required|date',
        ]);

        $student = User::with('streams.classLevel')->find($request->user_id);
        if (!$student || !$student->streams->first()) {
            return back()->with('error', 'Student is not assigned to a class.');
        }

        $classLevel = $student->streams->first()->classLevel;

        $feeStructures = FeeStructure::where('class_level_id', $classLevel->id)
            ->where('academic_year', $request->academic_year)
            ->get();

        if ($feeStructures->isEmpty()) {
            return back()->with('error', 'No fee structures found for the student\'s class and academic year.');
        }

        DB::beginTransaction();
        try {
            $totalAmount = $feeStructures->sum('amount');

            $invoice = Invoice::create([
                'user_id' => $student->id,
                'total_amount' => $totalAmount,
                'due_date' => $request->due_date,
                'academic_year' => $request->academic_year,
                'term' => $request->term,
                'status' => 'unpaid',
            ]);

            foreach ($feeStructures as $structure) {
                $invoice->items()->create([
                    'fee_structure_id' => $structure->id,
                    'description' => $structure->feeCategory->name,
                    'amount' => $structure->amount,
                ]);
            }

            DB::commit();

            return redirect()->route('invoices.show', $invoice)->with('success', 'Invoice created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to create invoice. Error: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Invoice $invoice): View
    {
        $invoice->load(['student', 'items.feeStructure.feeCategory', 'payments.recordedBy']);
        return view('finance.invoices.show', compact('invoice'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Invoice $invoice): RedirectResponse
    {
        if ($invoice->status !== 'unpaid') {
            return redirect()->route('invoices.show', $invoice)
                ->with('error', 'Cannot delete an invoice that has payments or is not in unpaid status.');
        }

        $invoice->delete();

        return redirect()->route('invoices.index')
            ->with('success', 'Invoice deleted successfully.');
    }
}
