<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChatAdminController extends Controller
{
    /**
     * Display a list of all chat conversations.
     */
    public function index()
    {
        // Get all distinct DM channels
        $dmChannels = Message::whereNotNull('receiver_id')
            ->select('channel')
            ->distinct()
            ->pluck('channel');

        $conversations = collect([
            ['name' => 'Teacher Group Chat', 'channel' => 'teachers_chat']
        ]);

        foreach ($dmChannels as $channel) {
            $userIds = explode('-', str_replace('dm.', '', $channel));
            $users = User::whereIn('id', $userIds)->get();
            if ($users->count() === 2) {
                $conversations->push([
                    'name' => $users[0]->name . ' & ' . $users[1]->name,
                    'channel' => $channel
                ]);
            }
        }

        return view('admin.chat.index', compact('conversations'));
    }

    /**
     * Show the messages for a specific conversation.
     */
    public function showConversation($channel)
    {
        $messages = Message::where('channel', $channel)
            ->with('sender')
            ->withTrashed() // Include soft-deleted messages
            ->latest()
            ->get();

        return response()->json($messages);
    }

    /**
     * Permanently delete a message.
     */
    public function forceDelete($messageId)
    {
        $message = Message::withTrashed()->find($messageId);

        if (!$message) {
            return response()->json(['error' => 'Message not found'], 404);
        }

        $message->forceDelete();

        return response()->json(['status' => 'Message permanently deleted']);
    }
}
