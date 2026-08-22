<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

// Kontroler za prikaz i ažuriranje notifikacija u aplikaciji.
class NotificationController extends Controller
{
    /**
     * Display a paginated list of notifications for the current user.
     */
    public function index()
    {
        $user = Auth::user();
        // Pull both read and unread notifications, latest first
        $notifications = $user->notifications()->orderBy('created_at', 'desc')->paginate(10);
        return view('notifications.index', compact('notifications'));
    }

    /**
     * Mark a single notification as read.
     */
    public function markAsRead(string $id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        if ($notification->read_at === null) {
            $notification->markAsRead();
        }
        return back();
    }

    /**
     * Mark all notifications for the current user as read.
     */
    public function markAllAsRead()
    {
        $user = Auth::user();
        $user->unreadNotifications->markAsRead();
        return back()->with('success', 'Sve notifikacije su označene kao pročitane.');
    }
}