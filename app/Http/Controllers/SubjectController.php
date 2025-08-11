<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class SubjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $subjects = Subject::orderBy('name')->get();
        return view('subjects.index', compact('subjects'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('subjects.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:subjects,name'],
            'code' => ['nullable', 'string', 'max:255', 'unique:subjects,code'],
        ]);

        Subject::create($request->all());

        return redirect()->route('subjects.index')->with('success', 'Subject created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Subject $subject)
    {
        // Not used for now
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Subject $subject): View
    {
        return view('subjects.edit', compact('subject'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Subject $subject): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:subjects,name,'.$subject->id],
            'code' => ['nullable', 'string', 'max:255', 'unique:subjects,code,'.$subject->id],
        ]);

        $subject->update($request->all());

        return redirect()->route('subjects.index')->with('success', 'Subject updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Subject $subject): RedirectResponse
    {
        $subject->delete();

        return redirect()->route('subjects.index')->with('success', 'Subject deleted successfully.');
    }

    public function managePapers(Subject $subject): View
    {
        $subject->load('papers');
        return view('subjects.manage-papers', compact('subject'));
    }

    public function storePapers(Request $request, Subject $subject): RedirectResponse
    {
        $request->validate([
            'papers' => ['nullable', 'array'],
            'papers.*.name' => ['required', 'string', 'max:255'],
            'papers.*.id' => ['nullable', 'exists:papers,id'],
        ]);

        $existingPaperIds = [];

        // Update existing papers and create new ones
        if ($request->has('papers')) {
            foreach ($request->papers as $paperData) {
                if (isset($paperData['id'])) {
                    // Update existing paper
                    $paper = \App\Models\Paper::find($paperData['id']);
                    if ($paper) {
                        $paper->update(['name' => $paperData['name']]);
                        $existingPaperIds[] = $paper->id;
                    }
                } else {
                    // Create new paper
                    $newPaper = $subject->papers()->create(['name' => $paperData['name']]);
                    $existingPaperIds[] = $newPaper->id;
                }
            }
        }

        // Delete papers that were not in the submission
        $subject->papers()->whereNotIn('id', $existingPaperIds)->delete();

        return redirect()->route('subjects.index')->with('success', 'Papers updated successfully.');
    }
}
