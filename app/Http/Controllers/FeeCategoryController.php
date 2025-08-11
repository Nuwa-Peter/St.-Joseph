<?php

namespace App\Http\Controllers;

use App\Models\FeeCategory;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class FeeCategoryController extends Controller
{
    public function index(): View
    {
        $categories = FeeCategory::latest()->get();
        return view('finance.fees.categories.index', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate(['name' => 'required|string|max:255|unique:fee_categories,name']);
        FeeCategory::create($request->all());
        return redirect()->route('fee-categories.index')->with('success', 'Fee Category created.');
    }

    public function update(Request $request, FeeCategory $feeCategory): RedirectResponse
    {
        $request->validate(['name' => 'required|string|max:255|unique:fee_categories,name,' . $feeCategory->id]);
        $feeCategory->update($request->all());
        return redirect()->route('fee-categories.index')->with('success', 'Fee Category updated.');
    }

    public function destroy(FeeCategory $feeCategory): RedirectResponse
    {
        $feeCategory->delete();
        return redirect()->route('fee-categories.index')->with('success', 'Fee Category deleted.');
    }
}
