<?php

namespace App\Http\Controllers;

use App\Models\Club;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class ClubController extends Controller
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
        $clubs = Club::with('teacher', 'members')->latest()->paginate(15);
        return view('welfare.clubs.index', compact('clubs'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $teachers = User::where('role', \App\Enums\Role::TEACHER)->orderBy('last_name')->get();
        return view('welfare.clubs.create', compact('teachers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:clubs,name',
            'description' => 'nullable|string',
            'teacher_id' => 'nullable|exists:users,id',
        ]);

        Club::create($request->all());

        return redirect()->route('clubs.index')->with('success', 'Club created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Club $club): View
    {
        $club->load('members');
        $students = User::where('role', \App\Enums\Role::STUDENT)->orderBy('last_name')->get();
        return view('welfare.clubs.show', compact('club', 'students'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Club $club): View
    {
        $teachers = User::where('role', \App\Enums\Role::TEACHER)->orderBy('last_name')->get();
        return view('welfare.clubs.edit', compact('club', 'teachers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Club $club): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:clubs,name,' . $club->id,
            'description' => 'nullable|string',
            'teacher_id' => 'nullable|exists:users,id',
        ]);

        $club->update($request->all());

        return redirect()->route('clubs.index')->with('success', 'Club updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Club $club): RedirectResponse
    {
        $club->delete();
        return redirect()->route('clubs.index')->with('success', 'Club deleted successfully.');
    }

    /**
     * Add a member to the club.
     */
    public function addMember(Request $request, Club $club): RedirectResponse
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $club->members()->syncWithoutDetaching($request->user_id);

        return redirect()->route('clubs.show', $club)->with('success', 'Member added successfully.');
    }

    /**
     * Remove a member from the club.
     */
    public function removeMember(Club $club, User $member): RedirectResponse
    {
        $club->members()->detach($member->id);

        return redirect()->route('clubs.show', $club)->with('success', 'Member removed successfully.');
    }
}
