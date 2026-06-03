<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\AttendanceResource;
use App\Http\Resources\BillingResource;
use App\Http\Resources\GradeResource;
use App\Http\Resources\NotificationResource;
use App\Http\Resources\PointLogResource;
use App\Http\Resources\ScheduleResource;
use App\Http\Resources\StudentResource;
use App\Models\Grade;
use App\Models\Schedule;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

/**
 * @group Student Scoped Data v1
 *
 * API endpoint khusus untuk peran Siswa guna melihat data akademik, jadwal, kehadiran, BK, dan keuangan mereka.
 */
class ApiStudentController extends Controller
{
    use ApiResponseTrait;

    /**
     * Get Student Profile
     *
     * Mendapatkan profil lengkap akademik diri sendiri.
     *
     * @authenticated
     */
    public function profile(Request $request)
    {
        $student = $request->user()->student;

        if (!$student) {
            return $this->errorResponse('Data siswa tidak ditemukan', 404);
        }

        return $this->successResponse(new StudentResource($student), 'Profil siswa berhasil diambil');
    }

    /**
     * Get Student Schedule
     *
     * Mengambil jadwal pelajaran siswa untuk tahun akademik saat ini berdasarkan kelasnya.
     *
     * @authenticated
     */
    public function schedule(Request $request)
    {
        $student = $request->user()->student;

        if (!$student || !$student->classroom_id) {
            return $this->successResponse([], 'Belum terdaftar di kelas manapun');
        }

        $schedules = Schedule::where('classroom_id', $student->classroom_id)
            ->with(['subject', 'teacher', 'classroom'])
            ->get();

        return $this->successResponse(ScheduleResource::collection($schedules), 'Jadwal pelajaran berhasil diambil');
    }

    /**
     * Get Student Grades
     *
     * Mengambil riwayat nilai (tugas, UH, PTS, PAS) siswa.
     *
     * @authenticated
     */
    public function grades(Request $request)
    {
        $student = $request->user()->student;

        if (!$student) {
            return $this->errorResponse('Data siswa tidak ditemukan', 404);
        }

        $grades = Grade::where('student_id', $student->id)
            ->with('subject')
            ->latest()
            ->get();

        return $this->successResponse(GradeResource::collection($grades), 'Data nilai berhasil diambil');
    }

    /**
     * Get Student Attendance
     *
     * Mengambil riwayat 30 kehadiran harian terakhir milik siswa.
     *
     * @authenticated
     */
    public function attendance(Request $request)
    {
        $student = $request->user()->student;

        if (!$student) {
            return $this->errorResponse('Data siswa tidak ditemukan', 404);
        }

        $attendances = $student->attendances()
            ->latest()
            ->take(30)
            ->get();

        return $this->successResponse(AttendanceResource::collection($attendances), 'Data absensi berhasil diambil');
    }

    /**
     * Get Student Billings
     *
     * Mengambil seluruh rincian tagihan keuangan sekolah siswa.
     *
     * @authenticated
     */
    public function billings(Request $request)
    {
        $student = $request->user()->student;

        if (!$student) {
            return $this->errorResponse('Data siswa tidak ditemukan', 404);
        }

        $billings = $student->billings()
            ->with('category')
            ->latest()
            ->get();

        return $this->successResponse(BillingResource::collection($billings), 'Data tagihan berhasil diambil');
    }

    /**
     * Get Student BK Points
     *
     * Mengambil rekam jejak poin BK (prestasi atau pelanggaran) siswa.
     *
     * @authenticated
     */
    public function points(Request $request)
    {
        $student = $request->user()->student;

        if (!$student) {
            return $this->errorResponse('Data siswa tidak ditemukan', 404);
        }

        $points = $student->pointLogs()
            ->with('category')
            ->latest()
            ->get();

        return $this->successResponse(PointLogResource::collection($points), 'Data poin BK berhasil diambil');
    }

    /**
     * Get Student Notifications
     *
     * Mengambil daftar notifikasi in-app aktif khusus siswa.
     *
     * @authenticated
     */
    public function notifications(Request $request)
    {
        $user = $request->user();
        $notifications = $user->notifications()->latest()->take(50)->get();

        return $this->successResponse(NotificationResource::collection($notifications), 'Notifikasi berhasil diambil');
    }

    /**
     * Mark Notification as Read
     *
     * Menandai notifikasi tertentu sebagai telah dibaca.
     *
     * @authenticated
     */
    public function markNotificationRead(Request $request, $id)
    {
        $user = $request->user();
        $notification = $user->notifications()->findOrFail($id);
        $notification->markAsRead();

        return $this->successResponse(null, 'Notifikasi ditandai telah dibaca');
    }
}
