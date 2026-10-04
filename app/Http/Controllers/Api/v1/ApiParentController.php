<?php

namespace App\Http\Controllers\Api\v1;

use App\Events\ChatMessageSent;
use App\Http\Controllers\Controller;
use App\Http\Resources\AttendanceResource;
use App\Http\Resources\BillingResource;
use App\Http\Resources\GradeResource;
use App\Http\Resources\ParentMessageResource;
use App\Http\Resources\PointLogResource;
use App\Http\Resources\StudentResource;
use App\Models\AcademicYear;
use App\Models\Grade;
use App\Models\ParentMessage;
use App\Models\Student;
use App\Models\User;
use App\Support\Access\StudentDataAccess;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Parent Scoped Data v1
 *
 * API endpoint khusus untuk peran Orang Tua guna memantau data anak-anak mereka dan berkomunikasi dengan guru.
 */
class ApiParentController extends Controller
{
    use ApiResponseTrait;

    /**
     * Get Children List
     *
     * Mengambil daftar seluruh anak yang terdaftar untuk orang tua yang aktif.
     *
     * @authenticated
     */
    public function children(Request $request)
    {
        $user = $request->user();

        if (! in_array($user->role, StudentDataAccess::PARENT_ROLES, true)) {
            return $this->errorResponse(__('Akses ditolak.'), 403);
        }

        $children = $user->children()->with(['classroom', 'user'])->get();

        return $this->successResponse(StudentResource::collection($children), __('Daftar anak berhasil diambil'));
    }

    /**
     * Get Child Summary
     *
     * Mengambil rangkuman dasbor indikator akademik, absensi, keuangan, dan poin BK untuk anak tertentu.
     *
     * @authenticated
     *
     * @urlParam student string required UUID siswa (anak). Example: 123e4567-e89b-12d3-a456-426614174000
     */
    public function childSummary(Request $request, $studentUuid)
    {
        $student = $this->verifyChildRelation($request->user(), $studentUuid);
        if ($student instanceof JsonResponse) {
            return $student;
        }

        // This academic year, as the app labels it. No records means "no data" (null),
        // not 100% attendance or an average of 0.
        $year = AcademicYear::current();
        $inYear = fn ($q) => $year ? $q->where('academic_year_id', $year->id) : $q;

        $gradesAvg = $inYear(Grade::where('student_id', $student->id))->avg('score');

        $attendance = $inYear($student->attendances())->toBase()
            ->selectRaw("COUNT(*) as total, SUM(CASE WHEN status = 'H' THEN 1 ELSE 0 END) as present")->first();
        $totalDays = (int) ($attendance->total ?? 0);
        $attendanceRate = $totalDays > 0 ? round(((int) $attendance->present / $totalDays) * 100, 2) : null;

        $unpaidBillings = $student->billings()->where('status', '!=', 'paid')->count();

        // Violation points only: reward points are achievements, not violations.
        $totalPoints = (int) $student->pointLogs()->where('type', '!=', 'reward')->sum('points');

        return $this->successResponse([
            'student' => [
                'uuid' => $student->uuid,
                'name' => $student->user->name ?? 'N/A',
                'classroom' => $student->classroom->name ?? 'N/A',
                'grade_level' => $student->classroom->grade_level ?? 'N/A',
                'homeroom_teacher' => $student->classroom->teacher->name ?? null,
            ],
            'statistics' => [
                'average_grade' => $gradesAvg === null ? null : round((float) $gradesAvg, 2),
                'attendance_rate' => $attendanceRate,
                'unpaid_billings_count' => $unpaidBillings,
                'total_bk_points' => $totalPoints,
            ],
        ], __('Rangkuman data anak berhasil dihitung'));
    }

    /**
     * Get Child Grades
     *
     * Mengambil riwayat nilai akademik anak.
     *
     * @authenticated
     */
    public function childGrades(Request $request, $studentUuid)
    {
        $student = $this->verifyChildRelation($request->user(), $studentUuid);
        if ($student instanceof JsonResponse) {
            return $student;
        }

        $grades = Grade::where('student_id', $student->id)
            ->with('subject')
            ->latest()
            ->get();

        return $this->successResponse(GradeResource::collection($grades), __('Data nilai anak berhasil diambil'));
    }

    /**
     * Get Child Attendance
     *
     * Mengambil 30 absensi harian terakhir anak.
     *
     * @authenticated
     */
    public function childAttendance(Request $request, $studentUuid)
    {
        $student = $this->verifyChildRelation($request->user(), $studentUuid);
        if ($student instanceof JsonResponse) {
            return $student;
        }

        $attendances = $student->attendances()
            ->latest()
            ->take(30)
            ->get();

        return $this->successResponse(AttendanceResource::collection($attendances), __('Data absensi anak berhasil diambil'));
    }

    /**
     * Get Child Billings
     *
     * Mengambil daftar tagihan keuangan anak.
     *
     * @authenticated
     */
    public function childBillings(Request $request, $studentUuid)
    {
        $student = $this->verifyChildRelation($request->user(), $studentUuid);
        if ($student instanceof JsonResponse) {
            return $student;
        }

        $billings = $student->billings()
            ->with('category')
            ->latest()
            ->get();

        return $this->successResponse(BillingResource::collection($billings), __('Data tagihan keuangan anak berhasil diambil'));
    }

    /**
     * Get Child BK Points
     *
     * Mengambil catatan log poin BK anak.
     *
     * @authenticated
     */
    public function childPoints(Request $request, $studentUuid)
    {
        $student = $this->verifyChildRelation($request->user(), $studentUuid);
        if ($student instanceof JsonResponse) {
            return $student;
        }

        $points = $student->pointLogs()
            ->with('category')
            ->latest()
            ->get();

        return $this->successResponse(PointLogResource::collection($points), __('Log poin BK anak berhasil diambil'));
    }

    /**
     * Get Messages Chat History
     *
     * Mengambil daftar obrolan pesan antara Orang Tua dan Guru terkait anak-anak mereka.
     *
     * @authenticated
     */
    public function messages(Request $request)
    {
        $user = $request->user();

        $messages = ParentMessage::where('sender_id', $user->id)
            ->orWhere('recipient_id', $user->id)
            ->with(['sender', 'recipient', 'student.user'])
            ->latest()
            ->take(100)
            ->get();

        return $this->successResponse(ParentMessageResource::collection($messages), __('Riwayat pesan berhasil diambil'));
    }

    /**
     * Send Message to Teacher
     *
     * Mengirim pesan konsultasi baru ke guru tertentu.
     * Memicu pengiriman WebSocket real-time `ChatMessageSent` secara internal.
     *
     * @authenticated
     *
     * @bodyParam recipient_id integer required ID Guru (User ID). Example: 5
     * @bodyParam student_uuid string required UUID Siswa (Anak). Example: 9c1b3e2a-...
     * @bodyParam message string Isi teks pesan obrolan (boleh kosong jika ada lampiran). Example: Assalamu'alaikum wr. wb.
     * @bodyParam attachment file Lampiran gambar/PDF (maks 5MB).
     */
    public function sendMessage(Request $request)
    {
        $request->validate([
            'recipient_id' => 'required|exists:users,id',
            // uuid, not the numeric PK — the mobile app only ever learns a
            // child's uuid (parent/children), never the raw id, so this
            // matches every other child-scoped endpoint on this controller.
            'student_uuid' => 'required|string|exists:students,uuid',
            'message' => 'required_without:attachment|nullable|string|max:5000',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $user = $request->user();

        // 1. Verifikasi guru ada
        $teacher = User::findOrFail($request->recipient_id);
        if (! in_array($teacher->role, ['guru', 'bk'])) {
            return $this->errorResponse(__('Tujuan pengiriman bukan akun guru/BK'), 400);
        }

        // 2. Verifikasi relasi anak
        $student = $this->verifyChildRelation($user, $request->student_uuid);
        if ($student instanceof JsonResponse) {
            return $student;
        }

        // 2b. Simpan lampiran jika ada
        $attachmentPath = null;
        $attachmentType = null;
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $attachmentPath = $file->store('chat_attachments', 'public');
            $attachmentType = $file->getClientOriginalExtension() === 'pdf' ? 'pdf' : 'image';
        }

        // 3. Buat dan simpan pesan
        $message = ParentMessage::create([
            'sender_id' => $user->id,
            'recipient_id' => $teacher->id,
            'student_id' => $student->id,
            'message' => $request->message ?? '',
            'attachment_path' => $attachmentPath,
            'attachment_type' => $attachmentType,
            'is_read' => false,
        ]);

        // 4. Memicu WebSocket Broadcast Real-time (sender di-attach langsung dari
        // user yang sudah diautentikasi agar payload broadcast punya sender_name
        // yang nyata, bukan ditebak di sisi Android).
        $message->setRelation('sender', $user);
        event(new ChatMessageSent($message));

        return $this->successResponse(new ParentMessageResource($message), __('Pesan berhasil dikirim'), 201);
    }

    /**
     * Relational child check helper.
     */
    private function verifyChildRelation($user, $studentUuid)
    {
        $student = Student::where('uuid', $studentUuid)->first();
        if (! $student) {
            return $this->errorResponse(__('Siswa tidak ditemukan'), 404);
        }

        if ($student->parent_id !== $user->id) {
            return $this->errorResponse(__('Otorisasi ditolak. Siswa bukan anak terdaftar Anda.'), 403);
        }

        return $student;
    }
}
