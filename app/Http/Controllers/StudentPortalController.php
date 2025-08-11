<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class StudentPortalController extends Controller
{
    /**
     * Show the student portal dashboard.
     */
    public function dashboard(): View
    {
        $student = Auth::user();
        $student->load([
            'streams.classLevel',
            'streams.subjects',
            'streams.videos' => fn($q) => $q->latest(),
            'invoices' => fn($q) => $q->latest()->first(),
        ]);

        $announcements = \App\Models\Announcement::where('start_date', '<=', now())
            ->where(fn($q) => $q->whereNull('end_date')->orWhere('end_date', '>=', now()))
            ->latest()
            ->take(5)
            ->get();

        return view('portals.student.dashboard', compact('student', 'announcements'));
    }
}
