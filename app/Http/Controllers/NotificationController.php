<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();
        $notifications = $user->notifications()->paginate(20);

        // Mark notifications as read
        $user->unreadNotifications->markAsRead();

        return view('notifications.index', compact('notifications'));
    }
}
