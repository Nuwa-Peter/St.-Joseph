<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Events\MessageSent;

class DirectMessageController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:Teacher,Headteacher']);
    }

    /**
     * Fetch messages for a DM conversation.
     */
    public function show(User $receiver)
    {
        $sender = Auth::user();

        $messages = Message::where(function ($query) use ($sender, $receiver) {
            $query->where('sender_id', $sender->id)
                  ->where('receiver_id', $receiver->id);
        })->orWhere(function ($query) use ($sender, $receiver) {
            $query->where('sender_id', $receiver->id)
                  ->where('receiver_id', $sender->id);
        })
        ->with('sender')
        ->latest()
        ->take(50)
        ->get()
        ->reverse();

        return response()->json($messages);
    }

    /**
     * Store a new message for a DM conversation.
     */
    public function store(Request $request, User $receiver)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $sender = Auth::user();

        $message = Message::create([
            'sender_id' => $sender->id,
            'receiver_id' => $receiver->id,
            'content' => $request->input('message'),
            'channel' => $this->getDmChannel($sender, $receiver),
        ]);

        $message->load('sender');

        broadcast(new MessageSent($message))->toOthers();

        return response()->json(['status' => 'Message sent!', 'message' => $message]);
    }

    /**
     * Generate a consistent channel name for a DM conversation.
     */
    private function getDmChannel(User $user1, User $user2): string
    {
        // Sort IDs to ensure consistency
        $ids = [$user1->id, $user2->id];
        sort($ids);
        return 'dm.' . implode('-', $ids);
    }
}
