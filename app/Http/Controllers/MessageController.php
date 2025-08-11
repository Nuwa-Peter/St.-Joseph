<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Message  $message
     * @return \Illuminate\Http\Response
     */
    public function destroy(Message $message)
    {
        // Authorize that the user deleting the message is the sender
        if ($message->sender_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $message->delete(); // This performs a soft delete

        // We can also broadcast an event here to notify clients to update the message UI
        // For now, we'll let the client handle the UI update on success.

        return response()->json(['status' => 'Message deleted successfully']);
    }
}
