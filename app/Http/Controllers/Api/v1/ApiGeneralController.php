<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\AnnouncementResource;
use App\Http\Resources\CalendarEventResource;
use App\Http\Resources\NotificationResource;
use App\Models\AcademicEvent;
use App\Models\Announcement;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

/**
 * @group Universal Mobile Data v1
 *
 * API endpoint umum untuk seluruh pengguna aplikasi mobile guna mengambil pengumuman, agenda kalender akademik, dan notifikasi.
 */
class ApiGeneralController extends Controller
{
    use ApiResponseTrait;

    /**
     * Get Announcements
     *
     * Pengumuman aktif yang ditujukan untuk akun ini (peran dan kelas), yang di-pin lebih dulu,
     * beserta status baca dan konfirmasi akun ini.
     *
     * @authenticated
     */
    public function announcements(Request $request)
    {
        $user = $request->user();

        $announcements = Announcement::visibleTo($user)
            ->with(['creator', 'targetClassroom', 'reads' => fn ($q) => $q->where('user_id', $user->id)])
            ->when($request->filled('category'), fn ($q) => $q->where('category', $request->input('category')))
            ->take(30)
            ->get();

        return $this->successResponse(AnnouncementResource::collection($announcements), 'Pengumuman sekolah berhasil diambil');
    }

    /**
     * Get Announcement Detail
     *
     * Rincian satu pengumuman untuk akun ini. Membukanya dicatat sebagai sudah dibaca,
     * sama seperti di web, sehingga tanda terima admin ikut menghitung pembaca dari aplikasi.
     *
     * @authenticated
     */
    public function announcementDetail(Request $request, int $id)
    {
        $user = $request->user();
        $announcement = Announcement::visibleTo($user)->with(['creator', 'targetClassroom'])->find($id);

        if (! $announcement) {
            return $this->errorResponse('Pengumuman tidak ditemukan', 404);
        }

        $announcement->recordReadBy($user);
        $announcement->load(['reads' => fn ($q) => $q->where('user_id', $user->id)]);

        return $this->successResponse(new AnnouncementResource($announcement), 'Detail pengumuman berhasil diambil');
    }

    /**
     * Acknowledge Announcement
     *
     * Konfirmasi bahwa akun ini sudah membaca dan memahami pengumuman yang mewajibkannya.
     *
     * @authenticated
     */
    public function acknowledgeAnnouncement(Request $request, int $id)
    {
        $user = $request->user();
        $announcement = Announcement::visibleTo($user)->with(['creator', 'targetClassroom'])->find($id);

        if (! $announcement) {
            return $this->errorResponse('Pengumuman tidak ditemukan', 404);
        }
        if (! $announcement->require_acknowledgement) {
            return $this->errorResponse('Pengumuman ini tidak memerlukan konfirmasi.', 422);
        }

        $announcement->acknowledgeBy($user);
        $announcement->load(['reads' => fn ($q) => $q->where('user_id', $user->id)]);

        return $this->successResponse(new AnnouncementResource($announcement), 'Terima kasih, konfirmasi Anda sudah tercatat.');
    }

    /**
     * Get Calendar Events
     *
     * Mengambil daftar 50 agenda kalender akademik aktif sekolah.
     *
     * @authenticated
     */
    public function calendarEvents(Request $request)
    {
        $user = $request->user();

        $events = AcademicEvent::visibleTo($user)
            ->latest()
            ->take(50)
            ->get();

        return $this->successResponse(CalendarEventResource::collection($events), 'Kalender akademik berhasil diambil');
    }

    /**
     * Get Notifications
     *
     * 50 notifikasi terbaru akun ini.
     *
     * @authenticated
     */
    public function notifications(Request $request)
    {
        $notifications = $request->user()->notifications()->latest()->take(50)->get();

        return $this->successResponse(NotificationResource::collection($notifications), 'Notifikasi berhasil diambil');
    }

    /**
     * Unread Notification Count
     *
     * @authenticated
     */
    public function unreadNotificationCount(Request $request)
    {
        return $this->successResponse(['count' => $request->user()->unreadNotifications()->count()]);
    }

    /**
     * Mark Notification Read
     *
     * @authenticated
     */
    public function markNotificationRead(Request $request, string $id)
    {
        $notification = $request->user()->notifications()->find($id);
        if (! $notification) {
            return $this->errorResponse('Notifikasi tidak ditemukan', 404);
        }
        $notification->markAsRead();

        return $this->successResponse(new NotificationResource($notification), 'Notifikasi ditandai sudah dibaca');
    }

    /**
     * Mark All Notifications Read
     *
     * @authenticated
     */
    public function markAllNotificationsRead(Request $request)
    {
        $updated = $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return $this->successResponse(['updated' => $updated], 'Semua notifikasi ditandai sudah dibaca');
    }

    /**
     * Delete Notification
     *
     * @authenticated
     */
    public function deleteNotification(Request $request, string $id)
    {
        $notification = $request->user()->notifications()->find($id);
        if (! $notification) {
            return $this->errorResponse('Notifikasi tidak ditemukan', 404);
        }
        $notification->delete();

        return $this->successResponse(null, 'Notifikasi dihapus');
    }
}
