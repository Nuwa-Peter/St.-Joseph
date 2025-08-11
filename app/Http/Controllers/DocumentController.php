<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Stream;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DocumentController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:root,headteacher');
    }

    public function selectIdCard(): View
    {
        $students = User::where('role', \App\Enums\Role::STUDENT)->orderBy('last_name')->get();
        return view('documents.select-id-card', compact('students'));
    }

    public function generateIdCard(Request $request)
    {
        $request->validate(['student_id' => 'required|exists:users,id']);
        $student = User::find($request->student_id);

        // Redirect to the generation route from StudentController
        return redirect()->route('students.id-card', $student);
    }

    public function selectReportCard(): View
    {
        $students = User::where('role', \App\Enums\Role::STUDENT)->orderBy('last_name')->get();
        // For the dropdown, we only need streams that actually have students.
        $streams = Stream::whereHas('students')->with('classLevel')->get();
        return view('documents.select-report-card', compact('students', 'streams'));
    }

    public function generateReportCard(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:users,id',
            'stream_id' => 'required|exists:streams,id',
        ]);

        $student = User::find($request->student_id);
        $stream = Stream::find($request->stream_id);

        // Ensure the student is actually in the selected stream
        if (!$student->streams->contains($stream)) {
            return back()->withErrors(['stream_id' => 'The selected student is not in the selected stream.'])->withInput();
        }

        return redirect()->route('students.report-card', ['user' => $student, 'stream' => $stream]);
    }
}
