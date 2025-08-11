<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Models\Message; // Import the Message model
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:Teacher,Headteacher']);
    }

    public function index()
    {
        // Group chat messages
        $messages = Message::where('channel', 'teachers_chat')
                            ->with('sender')
                            ->latest()
                            ->take(50)
                            ->get()
                            ->reverse();

        // Users available for DM
        $users = \App\Models\User::whereIn('role', [\App\Enums\Role::TEACHER, \App\Enums\Role::HEADTEACHER])
                                 ->where('id', '!=', Auth::id())
                                 ->orderBy('first_name')
                                 ->get();

        return view('chat.index', compact('messages', 'users'));
    }

    public function getGroupMessages()
    {
        $messages = Message::where('channel', 'teachers_chat')
                            ->with('sender')
                            ->latest()
                            ->take(50)
                            ->get()
                            ->reverse();

        return response()->json($messages);
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $user = Auth::user();

        $message = $user->messages()->create([
            'content' => $request->input('message'),
            'channel' => 'teachers_chat',
        ]);

        // The sender relationship is automatically loaded when creating via relationship
        // but let's be explicit for the broadcast event.
        $message->load('sender');

        broadcast(new MessageSent($message))->toOthers();

        return response()->json(['status' => 'Message sent!']);
    }
}
