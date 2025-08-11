<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class BulkMessageController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:root,headteacher');
    }

    /**
     * Show the form for creating a new bulk message.
     */
    public function create(): View
    {
        return view('communications.bulk.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'recipients' => 'required|string',
            'message_type' => 'required|in:sms,email',
            'subject' => 'required_if:message_type,email|string|max:255',
            'message' => 'required|string',
        ]);

        // Placeholder for sending logic
        // e.g., dispatch a job to send emails/SMS messages

        $message = "Your {$request->message_type} has been queued for sending to {$request->recipients}.";

        return redirect()->route('bulk-messages.create')->with('success', $message);
    }
}
