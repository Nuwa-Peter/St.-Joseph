<?php

namespace App\Http\Controllers;

use App\Models\ExpenseCategory;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class ExpenseCategoryController extends Controller
{
    public function index(): View
    {
        $categories = ExpenseCategory::latest()->get();
        return view('finance.expenses.categories.index', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate(['name' => 'required|string|max:255|unique:expense_categories,name']);
        ExpenseCategory::create($request->all());
        return redirect()->route('expense-categories.index')->with('success', 'Expense Category created.');
    }

    public function update(Request $request, ExpenseCategory $expenseCategory): RedirectResponse
    {
        $request->validate(['name' => 'required|string|max:255|unique:expense_categories,name,' . $expenseCategory->id]);
        $expenseCategory->update($request->all());
        return redirect()->route('expense-categories.index')->with('success', 'Expense Category updated.');
    }

    public function destroy(ExpenseCategory $expenseCategory): RedirectResponse
    {
        $expenseCategory->delete();
        return redirect()->route('expense-categories.index')->with('success', 'Expense Category deleted.');
    }
}
