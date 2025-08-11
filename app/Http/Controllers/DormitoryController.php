<?php

namespace App\Http\Controllers;

use App\Models\Dormitory;
use App\Models\DormitoryRoom;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class DormitoryController extends Controller
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
        $dormitories = Dormitory::with('rooms')->latest()->paginate(10);
        return view('welfare.dormitories.index', compact('dormitories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('welfare.dormitories.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:dormitories,name',
            'description' => 'nullable|string',
        ]);

        Dormitory::create($request->all());

        return redirect()->route('dormitories.index')->with('success', 'Dormitory created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Dormitory $dormitory): View
    {
        $dormitory->load('rooms.occupants');
        return view('welfare.dormitories.show', compact('dormitory'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Dormitory $dormitory): View
    {
        return view('welfare.dormitories.edit', compact('dormitory'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Dormitory $dormitory): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:dormitories,name,' . $dormitory->id,
            'description' => 'nullable|string',
        ]);

        $dormitory->update($request->all());

        return redirect()->route('dormitories.index')->with('success', 'Dormitory updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Dormitory $dormitory): RedirectResponse
    {
        $dormitory->delete();

        return redirect()->route('dormitories.index')->with('success', 'Dormitory deleted successfully.');
    }

    /**
     * Store a new room for a dormitory.
     */
    public function storeRoom(Request $request, Dormitory $dormitory): RedirectResponse
    {
        $request->validate([
            'room_number' => 'required|string|max:255',
            'capacity' => 'required|integer|min:1',
        ]);

        $dormitory->rooms()->create($request->all());

        return redirect()->route('dormitories.show', $dormitory)->with('success', 'Room added successfully.');
    }

    /**
     * Delete a room from a dormitory.
     */
    public function destroyRoom(DormitoryRoom $room): RedirectResponse
    {
        $dormitory = $room->dormitory;
        // Add check if room has occupants before deleting
        if ($room->occupants()->exists()) {
            return redirect()->route('dormitories.show', $dormitory)->with('error', 'Cannot delete room with assigned students.');
        }
        $room->delete();

        return redirect()->route('dormitories.show', $dormitory)->with('success', 'Room deleted successfully.');
    }
}
