<?php

namespace App\Http\Controllers\Communication;

use App\Http\Controllers\Controller;
use App\Services\Communication\NotificationDispatchService;
use Illuminate\Http\Request;

class NotificationCenterController extends Controller
{
    /**
     * Display Enterprise Notification Inbox.
     */
    public function index(Request $request, NotificationDispatchService $dispatchService)
    {
        $userId = auth()->id();
        $category = $request->query('category');
        $unreadOnly = $request->boolean('unread_only');

        $notifications = $dispatchService->getUserInbox($userId, $category, $unreadOnly);
        $unreadCount = $dispatchService->getUnreadCount($userId);

        return view('communication.notifications.index', compact('notifications', 'unreadCount', 'category', 'unreadOnly'));
    }

    /**
     * Display Notification Preference Controls.
     */
    public function preferences(NotificationDispatchService $dispatchService)
    {
        $userId = auth()->id();
        $preferences = $dispatchService->getUserPreferences($userId);

        return view('communication.notifications.preferences', compact('preferences'));
    }

    /**
     * Update Notification Channel Preferences.
     */
    public function updatePreferences(Request $request, NotificationDispatchService $dispatchService)
    {
        $userId = auth()->id();
        $preferencesData = $request->input('preferences', []);

        $dispatchService->updatePreferences($userId, $preferencesData);

        return back()->with('success', 'Preferensi saluran notifikasi berhasil diperbarui!');
    }

    /**
     * Mark single notification as read.
     */
    public function markAsRead(int $id, NotificationDispatchService $dispatchService)
    {
        $userId = auth()->id();
        $dispatchService->markAsRead($id, $userId);

        return back()->with('success', 'Notifikasi ditandai telah dibaca.');
    }

    /**
     * Mark all unread notifications as read.
     */
    public function markAllAsRead(NotificationDispatchService $dispatchService)
    {
        $userId = auth()->id();
        $updatedCount = $dispatchService->markAllRead($userId);

        return back()->with('success', "{$updatedCount} notifikasi berhasil ditandai telah dibaca.");
    }

    /**
     * Get unread notification count via API / JSON endpoint.
     */
    public function unreadCount(NotificationDispatchService $dispatchService)
    {
        $userId = auth()->id();
        $unreadCount = $dispatchService->getUnreadCount($userId);

        return response()->json(['unread_count' => $unreadCount]);
    }

    /**
     * Dispatch test notification (Admin trigger).
     */
    public function testDispatch(Request $request, NotificationDispatchService $dispatchService)
    {
        $userId = auth()->id();
        $eventType = $request->input('event_type', 'system.alert');
        $title = $request->input('title', 'Notifikasi Uji Coba SISTA');
        $body = $request->input('body', 'Ini adalah pesan uji coba terintegrasi dari Pusat Notifikasi Terpadu SMA Islam Sultan Agung 1 Semarang.');

        $logs = $dispatchService->dispatch($userId, $eventType, $title, $body, ['test_mode' => true]);

        return back()->with('success', 'Notifikasi uji coba berhasil dipancarkan ke '.count($logs).' saluran yang aktif!');
    }
}
