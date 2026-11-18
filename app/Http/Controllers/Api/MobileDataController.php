<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AnnouncementResource;
use App\Http\Resources\AttendanceResource;
use App\Http\Resources\BillingResource;
use App\Http\Resources\GradeResource;
use App\Http\Resources\MutabaahLogResource;
use App\Http\Resources\PointLogResource;
use App\Http\Resources\StudentResource;
use App\Http\Resources\TahfidzLogResource;
use App\Models\Announcement;
use App\Models\Student;
use App\Support\Access\Roles;
use App\Support\Access\StudentDataAccess;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

/**
 * @group Mobile Application Data
 *
 * API endpoint untuk mengambil data akademik, keuangan, dan islami
 * untuk ditampilkan di aplikasi mobile siswa atau orang tua.
 */
class MobileDataController extends Controller
{
    use ApiResponseTrait;

    /**
     * Get Students List
     *
     * Mengambil daftar siswa yang berelasi dengan akun yang sedang login.
     * Jika login sebagai `parent`, akan mengembalikan daftar anak-anaknya.
     * Jika login sebagai `student`, mengembalikan data dirinya sendiri.
     *
     * @authenticated
     */
    public function getStudents(Request $request)
    {
        $user = $request->user();
        $roles = $user->effectiveRoles();

        if (array_intersect($roles, StudentDataAccess::PARENT_ROLES) !== []) {
            $students = $user->children;
        } elseif (array_intersect($roles, StudentDataAccess::STUDENT_ROLES) !== []) {
            $students = Student::where('user_id', $user->id)->get();
        } else {
            return $this->errorResponse('Unauthorized access', 403);
        }

        return $this->successResponse(StudentResource::collection($students));
    }

    /**
     * Get Attendances
     *
     * Mendapatkan riwayat 30 absensi harian terakhir dari siswa tertentu.
     *
     * @authenticated
     *
     * @urlParam uuid string required UUID dari siswa. Example: 123e4567-e89b-12d3-a456-426614174000
     */
    public function getAttendances(Request $request, $uuid)
    {
        $student = $this->getStudentByUuid($request->user(), $uuid);
        if (! $student) {
            return $this->errorResponse('Student not found or unauthorized', 404);
        }

        $attendances = $student->attendances()->latest()->take(30)->get();

        return $this->successResponse(AttendanceResource::collection($attendances));
    }

    /**
     * Get Grades
     *
     * Mendapatkan seluruh rekam jejak nilai (Tugas, UH, PTS, PAS) dari siswa tertentu.
     *
     * @authenticated
     *
     * @urlParam uuid string required UUID dari siswa.
     */
    public function getGrades(Request $request, $uuid)
    {
        $student = $this->getStudentByUuid($request->user(), $uuid);
        if (! $student) {
            return $this->errorResponse('Student not found or unauthorized', 404);
        }

        $grades = $student->grades()->with('subject')->latest()->get();

        return $this->successResponse(GradeResource::collection($grades));
    }

    /**
     * Get Billings
     *
     * Mendapatkan daftar tagihan keuangan beserta status pembayarannya.
     *
     * @authenticated
     *
     * @urlParam uuid string required UUID dari siswa.
     */
    public function getBillings(Request $request, $uuid)
    {
        $student = $this->getStudentByUuid($request->user(), $uuid);
        if (! $student) {
            return $this->errorResponse('Student not found or unauthorized', 404);
        }

        $billings = $student->billings()->with('category')->latest()->get();

        return $this->successResponse(BillingResource::collection($billings));
    }

    public function getPointLogs(Request $request, $uuid)
    {
        $student = $this->getStudentByUuid($request->user(), $uuid);
        if (! $student) {
            return $this->errorResponse('Student not found or unauthorized', 404);
        }

        $logs = $student->pointLogs()->with('category')->latest()->get();

        return $this->successResponse(PointLogResource::collection($logs));
    }

    public function getTahfidzLogs(Request $request, $uuid)
    {
        $student = $this->getStudentByUuid($request->user(), $uuid);
        if (! $student) {
            return $this->errorResponse('Student not found or unauthorized', 404);
        }

        $logs = $student->tahfidzLogs()->latest()->get();

        return $this->successResponse(TahfidzLogResource::collection($logs));
    }

    public function getMutabaahLogs(Request $request, $uuid)
    {
        $student = $this->getStudentByUuid($request->user(), $uuid);
        if (! $student) {
            return $this->errorResponse('Student not found or unauthorized', 404);
        }

        $logs = $student->mutabaahLogs()->with('item')->latest()->take(50)->get();

        return $this->successResponse(MutabaahLogResource::collection($logs));
    }

    /**
     * Get Announcements
     *
     * Mendapatkan 10 pengumuman aktif terbaru yang ditujukan untuk role pengguna.
     *
     * @authenticated
     */
    public function getAnnouncements(Request $request)
    {
        $user = $request->user();
        $announcements = Announcement::visibleTo($user)->with('creator')->take(10)->get();

        return $this->successResponse(AnnouncementResource::collection($announcements));
    }

    /**
     * Helper to get student and check authorization.
     */
    private function getStudentByUuid($user, $uuid)
    {
        if (! $user) {
            return null;
        }

        $student = Student::where('uuid', $uuid)->first();
        if (! $student) {
            return null;
        }

        $roles = $user->effectiveRoles();

        if ($user->role === 'superadmin'
            || array_intersect($roles, [...StudentDataAccess::STAFF_ROLES, ...Roles::LEADERSHIP]) !== []) {
            return $student;
        }

        if (array_intersect($roles, StudentDataAccess::PARENT_ROLES) !== [] && $student->parent_id === $user->id) {
            return $student;
        }

        if (array_intersect($roles, StudentDataAccess::STUDENT_ROLES) !== [] && $student->user_id === $user->id) {
            return $student;
        }

        return null;
    }
}
