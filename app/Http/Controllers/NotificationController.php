<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Display a listing of the notifications.
     */
    public function index()
    {
        $user = Auth::user();
        $notifications = $user->notifications()->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    /**
     * Mark all unread notifications as read.
     */
    public function markAllAsRead()
    {
        Auth::user()->unreadNotifications->markAsRead();

        return back()->with('success', 'Semua notifikasi telah ditandai sebagai dibaca.');
    }

    /**
     * Mark a specific notification as read and redirect to its action URL.
     */
    public function read(string $id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);

        $notification->markAsRead();

        $actionUrl = $notification->data['action_url'] ?? '/dashboard';

        return redirect($actionUrl);
    }
}
