<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Models\Stream;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class StudentAssignmentController extends Controller
{
    public function index(Request $request): View
    {
        $streams = Stream::with('classLevel')->get();
        $students = User::where('role', Role::STUDENT)->orderBy('last_name')->get();

        $selectedStream = null;
        if ($request->has('stream_id')) {
            $selectedStream = Stream::with('students')->findOrFail($request->stream_id);
        }

        return view('student-assignments.index', compact('streams', 'students', 'selectedStream'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'stream_id' => ['required', 'exists:streams,id'],
            'students' => ['array'],
            'students.*' => ['exists:users,id'],
        ]);

        $stream = Stream::find($request->stream_id);
        $stream->students()->sync($request->students ?? []);

        return redirect()->route('student-assignments.index', ['stream_id' => $stream->id])->with('success', 'Students assigned successfully.');
    }
}
