<?php

namespace App\Http\Controllers;

use App\Models\ClassLevel;
use App\Models\FeeCategory;
use App\Models\FeeStructure;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class FeeStructureController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $feeStructures = FeeStructure::with(['feeCategory', 'classLevel'])->latest()->paginate(15);
        return view('finance.fees.structures.index', compact('feeStructures'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $feeCategories = FeeCategory::all();
        $classLevels = ClassLevel::all();
        return view('finance.fees.structures.create', compact('feeCategories', 'classLevels'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'fee_category_id' => 'required|exists:fee_categories,id',
            'class_level_id' => 'required|exists:class_levels,id',
            'amount' => 'required|numeric|min:0',
            'academic_year' => 'required|string|max:255',
        ]);

        FeeStructure::create($request->all());

        return redirect()->route('fee-structures.index')
            ->with('success', 'Fee structure created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(FeeStructure $feeStructure): View
    {
        $feeCategories = FeeCategory::all();
        $classLevels = ClassLevel::all();
        return view('finance.fees.structures.edit', compact('feeStructure', 'feeCategories', 'classLevels'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, FeeStructure $feeStructure): RedirectResponse
    {
        $request->validate([
            'fee_category_id' => 'required|exists:fee_categories,id',
            'class_level_id' => 'required|exists:class_levels,id',
            'amount' => 'required|numeric|min:0',
            'academic_year' => 'required|string|max:255',
        ]);

        $feeStructure->update($request->all());

        return redirect()->route('fee-structures.index')
            ->with('success', 'Fee structure updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FeeStructure $feeStructure): RedirectResponse
    {
        $feeStructure->delete();

        return redirect()->route('fee-structures.index')
            ->with('success', 'Fee structure deleted successfully.');
    }
}
