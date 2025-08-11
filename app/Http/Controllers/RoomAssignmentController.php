<?php

namespace App\Http\Controllers;

use App\Models\DormitoryRoom;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class RoomAssignmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:root,headteacher');
    }

    /**
     * Display the room assignment interface.
     */
    public function index(): View
    {
        $students = User::where('role', \App\Enums\Role::STUDENT)
                        ->whereDoesntHave('dormitoryRoom') // Only show unassigned students
                        ->orderBy('last_name')
                        ->get();

        $rooms = DormitoryRoom::with('dormitory')->get()->filter(function ($room) {
            return $room->occupants->count() < $room->capacity;
        });

        return view('welfare.assignments.index', compact('students', 'rooms'));
    }

    /**
     * Store a new room assignment.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'dormitory_room_id' => 'required|exists:dormitory_rooms,id',
            'academic_year' => 'required|string|max:255',
        ]);

        $room = DormitoryRoom::findOrFail($request->dormitory_room_id);
        $user = User::findOrFail($request->user_id);

        if ($room->occupants()->count() >= $room->capacity) {
            return back()->with('error', 'This room is already at full capacity.');
        }

        // Use syncWithoutDetaching to avoid duplicate entries and handle existing ones gracefully
        $user->dormitoryRoom()->syncWithoutDetaching([
            $room->id => ['academic_year' => $request->academic_year]
        ]);

        return redirect()->route('dormitories.show', $room->dormitory)->with('success', 'Student assigned to room successfully.');
    }

    /**
     * Remove a student from a room.
     */
    public function destroy($userId, $roomId): RedirectResponse
    {
        $user = User::findOrFail($userId);
        $room = DormitoryRoom::findOrFail($roomId);

        $user->dormitoryRoom()->detach($room->id);

        return back()->with('success', 'Student unassigned from room successfully.');
    }
}
