<?php

namespace App\Http\Controllers;

use App\Models\Resource;
use App\Models\ResourceBooking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class ResourceBookingController extends Controller
{
    /**
     * Display a listing of the resource bookings.
     */
    public function index(): View
    {
        $resources = Resource::where('is_bookable', true)->get();
        $bookings = ResourceBooking::with(['resource', 'user'])->get();

        $events = $bookings->map(function ($booking) {
            return [
                'title' => $booking->resource->name . ' (' . $booking->user->name . ')',
                'start' => $booking->start_time->toIso8601String(),
                'end' => $booking->end_time->toIso8601String(),
                'id' => $booking->id,
            ];
        });

        return view('resources.bookings.index', compact('resources', 'events'));
    }

    /**
     * Store a newly created resource booking in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'resource_id' => 'required|exists:resources,id',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'notes' => 'nullable|string',
        ]);

        // Check for booking conflicts
        $isConflict = ResourceBooking::where('resource_id', $request->resource_id)
            ->where(function ($query) use ($request) {
                $query->whereBetween('start_time', [$request->start_time, $request->end_time])
                      ->orWhereBetween('end_time', [$request->start_time, $request->end_time])
                      ->orWhere(function ($q) use ($request) {
                          $q->where('start_time', '<', $request->start_time)
                            ->where('end_time', '>', $request->end_time);
                      });
            })
            ->exists();

        if ($isConflict) {
            return back()->with('error', 'This resource is already booked for the selected time slot.');
        }

        ResourceBooking::create($request->all() + ['user_id' => Auth::id()]);

        return redirect()->route('bookings.index')->with('success', 'Resource booked successfully.');
    }

    /**
     * Remove the specified resource booking from storage.
     */
    public function destroy(ResourceBooking $booking): RedirectResponse
    {
        // Optional: Add policy to ensure only the user who booked it or an admin can delete it
        $booking->delete();
        return redirect()->route('bookings.index')->with('success', 'Booking cancelled successfully.');
    }
}
