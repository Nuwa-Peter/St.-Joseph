<?php

namespace App\Http\Controllers;

use App\Models\ClassLevel;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClassLevelController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $classLevels = ClassLevel::withCount('streams')->orderBy('name')->get();

        return view('class-levels.index', compact('classLevels'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('class-levels.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:class_levels,name'],
        ]);

        ClassLevel::create($request->all());

        return redirect()->route('class-levels.index')->with('success', 'Class level created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // We will not be using this method for now.
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ClassLevel $classLevel): View
    {
        return view('class-levels.edit', compact('classLevel'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ClassLevel $classLevel): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:class_levels,name,'.$classLevel->id],
        ]);

        $classLevel->update($request->all());

        return redirect()->route('class-levels.index')->with('success', 'Class level updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ClassLevel $classLevel): \Illuminate\Http\RedirectResponse
    {
        $classLevel->delete();

        return redirect()->route('class-levels.index')->with('success', 'Class level deleted successfully.');
    }
}
