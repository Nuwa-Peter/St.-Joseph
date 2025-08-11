<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Mail\BulkMessageMail;
use App\Models\Stream;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class CommunicationController extends Controller
{
    public function create(): View
    {
        $streams = Stream::with('classLevel')->get();
        return view('communications.create', compact('streams'));
    }

    public function send(Request $request): RedirectResponse
    {
        $request->validate([
            'method' => ['required', 'string', 'in:email,sms'],
            'recipients' => ['required', 'string'],
            'subject' => ['required_if:method,email', 'string', 'max:255'],
            'message' => ['required', 'string'],
        ]);

        $recipientsQuery = User::query();

        switch ($request->recipients) {
            case 'all_teachers':
                $recipientsQuery->where('role', Role::TEACHER);
                break;
            case 'all_parents':
                // This would require linking parents to students. For now, this is a placeholder.
                // $recipientsQuery->where('role', Role::PARENT);
                break;
            case 'all_students':
                $recipientsQuery->where('role', Role::STUDENT);
                break;
            default:
                if (str_starts_with($request->recipients, 'stream_')) {
                    $streamId = substr($request->recipients, 7);
                    $recipientsQuery->whereHas('streams', fn($q) => $q->where('streams.id', $streamId));
                }
                break;
        }

        $users = $recipientsQuery->get();
        $statusMessage = 'No action taken.';

        switch ($request->method) {
            case 'email':
                $emails = $users->pluck('email')->filter()->all();
                if (!empty($emails)) {
                    Mail::bcc($emails)->queue(new BulkMessageMail($request->subject, $request->message));
                    $statusMessage = 'Email has been queued for sending to ' . count($emails) . ' recipients.';
                } else {
                    $statusMessage = 'No recipients found with valid email addresses.';
                }
                break;

            case 'sms':
                // Placeholder for SMS sending logic
                $phoneNumbers = $users->pluck('phone_number')->filter()->all();
                if (!empty($phoneNumbers)) {
                    // foreach ($phoneNumbers as $number) {
                    //     // SMS::to($number)->send($request->message);
                    // }
                    $statusMessage = 'SMS functionality is a placeholder. Messages would be sent to ' . count($phoneNumbers) . ' recipients.';
                } else {
                    $statusMessage = 'No recipients found with valid phone numbers.';
                }
                break;
        }

        return redirect()->route('communications.create')->with('success', $statusMessage);
    }
}
