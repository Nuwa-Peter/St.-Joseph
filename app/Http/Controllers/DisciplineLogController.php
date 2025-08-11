<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;

class DisciplineLogController extends Controller
{
    /**
     * Store a newly created discipline log in storage.
     */
    public function store(Request $request, User $student): RedirectResponse
    {
        $request->validate([
            'type' => 'required|in:commendation,incident',
            'log_date' => 'required|date',
            'description' => 'required|string',
        ]);

        $student->disciplineLogs()->create([
            'recorded_by_id' => Auth::id(),
            'type' => $request->type,
            'log_date' => $request->log_date,
            'description' => $request->description,
        ]);

        return redirect()->route('users.show', $student)
            ->with('success', 'Discipline log recorded successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(\App\Models\DisciplineLog $disciplineLog): RedirectResponse
    {
        $student = $disciplineLog->student;
        $disciplineLog->delete();

        return redirect()->route('users.show', $student)
            ->with('success', 'Discipline log deleted successfully.');
    }
}
