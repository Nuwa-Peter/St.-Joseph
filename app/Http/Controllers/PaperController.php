<?php

namespace App\Http\Controllers;

use App\Models\Paper;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class PaperController extends Controller
{
    public function index(Subject $subject): View
    {
        return view('papers.index', [
            'subject' => $subject,
            'papers' => $subject->papers()->orderBy('name')->get(),
        ]);
    }

    public function create(Subject $subject): View
    {
        return view('papers.create', compact('subject'));
    }

    public function store(Request $request, Subject $subject): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:papers,name,NULL,id,subject_id,'.$subject->id],
        ]);

        $subject->papers()->create($request->all());

        return redirect()->route('subjects.papers.index', $subject)->with('success', 'Paper created successfully.');
    }

    public function edit(Paper $paper): View
    {
        return view('papers.edit', compact('paper'));
    }

    public function update(Request $request, Paper $paper): RedirectResponse
    {
        $subject = $paper->subject;
        $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:papers,name,'.$paper->id.',id,subject_id,'.$subject->id],
        ]);

        $paper->update($request->all());

        return redirect()->route('subjects.papers.index', $subject)->with('success', 'Paper updated successfully.');
    }

    public function destroy(Paper $paper): RedirectResponse
    {
        $subject = $paper->subject;
        $paper->delete();

        return redirect()->route('subjects.papers.index', $subject)->with('success', 'Paper deleted successfully.');
    }
}
