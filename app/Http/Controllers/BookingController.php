<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Resource;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class BookingController extends Controller
{
    public function __construct()
    {
        // Allow teachers to view and create bookings, but only admins to delete them.
        $this->middleware('role:root,headteacher,teacher');
        $this->middleware('role:root,headteacher')->only('destroy');
    }

    /**
     * Display a calendar of bookings.
     */
    public function index(): View
    {
        $resources = Resource::where('is_bookable', true)->get();

        $events = [];
        $bookings = Booking::with('resource', 'user')->get();
        foreach ($bookings as $booking) {
            $events[] = [
                'title' => $booking->title . ' (' . $booking->resource->name . ')',
                'start' => $booking->start_time->toIso8601String(),
                'end' => $booking->end_time->toIso8601String(),
                'extendedProps' => [
                    'user' => $booking->user->name,
                    'resource' => $booking->resource->name,
                ],
            ];
        }

        return view('resources.bookings.index', [
            'resources' => $resources,
            'events' => json_encode($events),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'resource_id' => 'required|exists:resources,id',
            'title' => 'required|string|max:255',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
        ]);

        // Check for booking conflicts
        $conflicts = Booking::where('resource_id', $request->resource_id)
            ->where(function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->where('start_time', '<', $request->end_time)
                      ->where('end_time', '>', $request->start_time);
                });
            })->exists();

        if ($conflicts) {
            return back()->with('error', 'This resource is already booked for the selected time period.');
        }

        Booking::create([
            'resource_id' => $request->resource_id,
            'user_id' => auth()->id(),
            'title' => $request->title,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
        ]);

        return redirect()->route('bookings.index')->with('success', 'Resource booked successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Booking $booking): RedirectResponse
    {
        $this->authorize('delete', $booking); // Assuming a policy might be added later
        $booking->delete();

        return redirect()->route('bookings.index')->with('success', 'Booking cancelled successfully.');
    }
}
