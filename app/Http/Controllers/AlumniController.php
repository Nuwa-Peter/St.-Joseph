<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class AlumniController extends Controller
{
    /**
     * Display a listing of the alumni.
     */
    public function index(Request $request): View
    {
        $alumni = User::where('is_alumni', true)
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('graduation_year', 'like', "%{$search}%");
            })
            ->orderBy('graduation_year', 'desc')
            ->orderBy('last_name')
            ->paginate(30);

        return view('alumni.index', compact('alumni'));
    }

    /**
     * Transition a student to an alumni.
     */
    public function graduate(Request $request, User $student): RedirectResponse
    {
        $request->validate([
            'graduation_year' => 'required|integer|min:1980|max:' . (date('Y') + 1),
        ]);

        $student->update([
            'is_alumni' => true,
            'graduation_year' => $request->graduation_year,
            'status' => 'graduated', // Also update their general status
        ]);

        // Remove from any active class assignments
        $student->streams()->detach();

        return redirect()->route('students.show', $student)->with('success', 'Student has been transitioned to Alumni.');
    }
}
