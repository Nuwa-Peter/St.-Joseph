<?php

namespace App\Http\Controllers;

use App\Models\Mark;
use App\Models\Stream;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class MarkController extends Controller
{
    public function index(): View
    {
        $teacher = Auth::user();
        $assignments = DB::table('paper_stream_user')
            ->where('user_id', $teacher->id)
            ->join('streams', 'paper_stream_user.stream_id', '=', 'streams.id')
            ->join('papers', 'paper_stream_user.paper_id', '=', 'papers.id')
            ->join('subjects', 'papers.subject_id', '=', 'subjects.id')
            ->join('class_levels', 'streams.class_level_id', '=', 'class_levels.id')
            ->select(
                'paper_stream_user.id as assignment_id',
                'streams.name as stream_name',
                'class_levels.name as class_level_name',
                'subjects.name as subject_name',
                'papers.name as paper_name'
            )
            ->get();

        return view('marks.index', compact('assignments'));
    }

    public function enter($assignment_id): View
    {
        $assignment = DB::table('paper_stream_user')->find($assignment_id);

        // Security check: ensure the logged-in teacher is the one assigned
        if ($assignment->user_id !== Auth::id()) {
            abort(403);
        }

        $stream = Stream::with('students')->find($assignment->stream_id);
        $students = $stream->students()->orderBy('last_name')->get();

        // Get existing marks for these students
        $marks = Mark::where('paper_id', $assignment->paper_id)
                     ->where('stream_id', $assignment->stream_id)
                     ->pluck('score', 'user_id');

        return view('marks.enter', compact('assignment', 'students', 'marks'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'assignment_id' => ['required', 'exists:paper_stream_user,id'],
            'marks' => ['required', 'array'],
            'marks.*' => ['nullable', 'integer', 'min:0', 'max:100'],
        ]);

        $assignment = DB::table('paper_stream_user')->find($request->assignment_id);

        // Security check
        if ($assignment->user_id !== Auth::id()) {
            abort(403);
        }

        foreach ($request->marks as $student_id => $score) {
            Mark::updateOrCreate(
                [
                    'user_id' => $student_id,
                    'paper_id' => $assignment->paper_id,
                    'stream_id' => $assignment->stream_id,
                ],
                ['score' => $score]
            );
        }

        return redirect()->route('marks.index')->with('success', 'Marks saved successfully.');
    }
}
