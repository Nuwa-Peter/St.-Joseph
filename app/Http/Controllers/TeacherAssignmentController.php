<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Models\Paper;
use App\Models\Stream;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class TeacherAssignmentController extends Controller
{
    public function create(): View
    {
        $teachers = User::whereIn('role', [Role::TEACHER, Role::HEADTEACHER])->orderBy('first_name')->get();
        $streams = Stream::with('classLevel')->get();
        $papers = Paper::with('subject')->get();

        // Also get existing assignments to display them
        $assignments = DB::table('paper_stream_user')
            ->join('users', 'paper_stream_user.user_id', '=', 'users.id')
            ->join('streams', 'paper_stream_user.stream_id', '=', 'streams.id')
            ->join('papers', 'paper_stream_user.paper_id', '=', 'papers.id')
            ->join('subjects', 'papers.subject_id', '=', 'subjects.id')
            ->join('class_levels', 'streams.class_level_id', '=', 'class_levels.id')
            ->select(
                'users.first_name',
                'users.last_name',
                'streams.name as stream_name',
                'class_levels.name as class_level_name',
                'subjects.name as subject_name',
                'papers.name as paper_name'
            )
            ->get();

        return view('teacher-assignments.create', compact('teachers', 'streams', 'papers', 'assignments'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'stream_id' => ['required', 'exists:streams,id'],
            'paper_id' => ['required', 'exists:papers,id'],
        ]);

        // Use the DB facade to insert into the pivot table
        DB::table('paper_stream_user')->insert([
            'user_id' => $request->user_id,
            'stream_id' => $request->stream_id,
            'paper_id' => $request->paper_id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);


        return redirect()->route('teacher-assignments.create')->with('success', 'Teacher assigned successfully.');
    }
}
