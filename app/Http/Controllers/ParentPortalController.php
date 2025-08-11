<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ParentPortalController extends Controller
{
    /**
     * Show the parent portal dashboard.
     */
    public function dashboard(): View
    {
        $parent = Auth::user();
        $children = $parent->children()->with([
            'streams.classLevel',
            'invoices' => fn($q) => $q->latest(),
            'disciplineLogs' => fn($q) => $q->latest()->limit(5),
            'attendances' => fn($q) => $q->latest()->limit(5)
        ])->get();

        // For simplicity, we'll work with the first child if multiple exist.
        // A real-world app would have a child switcher UI.
        $student = $children->first();

        $announcements = \App\Models\Announcement::where('start_date', '<=', now())
            ->where(fn($q) => $q->whereNull('end_date')->orWhere('end_date', '>=', now()))
            ->latest()
            ->take(5)
            ->get();

        return view('portals.parent.dashboard', compact('parent', 'student', 'announcements'));
    }
}
