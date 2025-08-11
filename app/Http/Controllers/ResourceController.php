<?php

namespace App\Http\Controllers;

use App\Models\Resource;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class ResourceController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:root,headteacher');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $resources = Resource::latest()->paginate(15);
        return view('resources.manage.index', compact('resources'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('resources.manage.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:resources,name',
            'description' => 'nullable|string',
            'is_bookable' => 'boolean',
        ]);

        Resource::create([
            'name' => $request->name,
            'description' => $request->description,
            'is_bookable' => $request->has('is_bookable'),
        ]);

        return redirect()->route('resources.index')->with('success', 'Resource created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Resource $resource): View
    {
        return view('resources.manage.edit', compact('resource'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Resource $resource): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:resources,name,' . $resource->id,
            'description' => 'nullable|string',
            'is_bookable' => 'boolean',
        ]);

        $resource->update([
            'name' => $request->name,
            'description' => $request->description,
            'is_bookable' => $request->has('is_bookable'),
        ]);

        return redirect()->route('resources.index')->with('success', 'Resource updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Resource $resource): RedirectResponse
    {
        $resource->delete();
        return redirect()->route('resources.index')->with('success', 'Resource deleted successfully.');
    }
}
