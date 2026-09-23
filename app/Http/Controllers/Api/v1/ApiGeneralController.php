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
     * Mengambil daftar 20 pengumuman aktif sekolah terbaru yang didelegasikan untuk tipe peran Anda.
     *
     * @authenticated
     */
    public function announcements(Request $request)
    {
        $user = $request->user();

        $announcements = Announcement::where('is_active', true)
            ->where(function ($query) use ($user) {
                $query->whereNull('target_role')
                    ->orWhere('target_role', $user->role);
            })
            ->when($request->filled('category'), function ($query) use ($request) {
                $query->where('category', $request->input('category'));
            })
            ->latest()
            ->take(20)
            ->get();

        return $this->successResponse(AnnouncementResource::collection($announcements), 'Pengumuman sekolah berhasil diambil');
    }

    /**
     * Get Announcement Detail
     *
     * Mengambil rincian satu pengumuman, dengan validasi peran yang sama seperti daftar pengumuman.
     *
     * @authenticated
     */
    public function announcementDetail(Request $request, int $id)
    {
        $user = $request->user();

        $announcement = Announcement::where('is_active', true)
            ->where(function ($query) use ($user) {
                $query->whereNull('target_role')
                    ->orWhere('target_role', $user->role);
            })
            ->find($id);

        if (!$announcement) {
            return $this->errorResponse('Pengumuman tidak ditemukan', 404);
        }

        return $this->successResponse(new AnnouncementResource($announcement), 'Detail pengumuman berhasil diambil');
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

        $events = AcademicEvent::active()
            ->forRole($user->role)
            ->latest()
            ->take(50)
            ->get();

        return $this->successResponse(CalendarEventResource::collection($events), 'Kalender akademik berhasil diambil');
    }

    /**
     * Get Notifications
     *
     * Mengambil seluruh riwayat notifikasi in-app pengguna.
     *
     * @authenticated
     */
    public function notifications(Request $request)
    {
        $user = $request->user();
        $notifications = $user->notifications()
            ->latest()
            ->take(50)
            ->get();

        return $this->successResponse(NotificationResource::collection($notifications), 'Notifikasi berhasil diambil');
    }
}
