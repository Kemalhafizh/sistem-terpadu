<?php

namespace App\Http\Controllers\Api\v1;

use App\Events\ChatMessageSent;
use App\Http\Controllers\Controller;
use App\Http\Resources\ParentMessageResource;
use App\Http\Resources\ScheduleResource;
use App\Http\Resources\TeachingJournalResource;
use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Classroom;
use App\Models\ParentMessage;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\TeachingJournal;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * @group Teacher Scoped Data v1
 *
 * API endpoint khusus untuk peran Guru guna melihat jadwal mengajar, menginput absensi kelas, dan membuat jurnal mengajar.
 */
class ApiTeacherController extends Controller
{
    use ApiResponseTrait;

    /**
     * Get Teacher Schedule
     *
     * Mengambil jadwal mengajar guru pada tahun akademik saat ini.
     *
     * @authenticated
     */
    public function schedule(Request $request)
    {
        $user = $request->user();

        $schedules = Schedule::where('teacher_id', $user->id)
            ->with(['subject', 'classroom', 'teacher'])
            ->get();

        return $this->successResponse(ScheduleResource::collection($schedules), 'Jadwal mengajar berhasil diambil');
    }

    /**
     * Get Taught Classrooms
     *
     * Mengambil daftar seluruh kelas yang diajar oleh guru tersebut.
     *
     * @authenticated
     */
    public function classes(Request $request)
    {
        $user = $request->user();

        // Cari kelas di mana guru adalah wali kelas
        // ATAU kelas di mana guru memiliki jadwal mengajar
        $classroomIdsFromSchedules = Schedule::where('teacher_id', $user->id)
            ->pluck('classroom_id')
            ->toArray();

        $classrooms = Classroom::where('teacher_id', $user->id)
            ->orWhereIn('id', $classroomIdsFromSchedules)
            ->withCount('students')
            ->get();

        $data = $classrooms->map(function ($c) use ($user) {
            return [
                'id' => $c->id,
                'name' => $c->name,
                'grade_level' => $c->grade_level,
                'major' => $c->major,
                'is_homeroom' => $c->teacher_id === $user->id,
                'students_count' => $c->students_count,
            ];
        });

        return $this->successResponse($data, 'Daftar kelas berhasil diambil');
    }

    /**
     * Get Students in Classroom
     *
     * Mengambil daftar seluruh siswa aktif di suatu kelas untuk absensi atau penilaian.
     *
     * @authenticated
     */
    public function classStudents(Request $request, $classroomId)
    {
        // name is a getNameAttribute() accessor proxying the related User,
        // not a real column — selecting/ordering by it directly against the
        // students table silently returned the literal string "name" for
        // every row. user_id must be selected (for the accessor to resolve)
        // and the relation eager-loaded, then sorted in PHP.
        $students = Student::where('classroom_id', $classroomId)
            ->select('id', 'user_id', 'nis', 'nisn', 'gender')
            ->with('user:id,name')
            ->get()
            ->sortBy('name')
            ->values()
            ->map(fn ($s) => [
                'id' => $s->id,
                'name' => $s->name,
                'nis' => $s->nis,
                'nisn' => $s->nisn,
                'gender' => $s->gender,
            ]);

        return $this->successResponse($students, 'Daftar siswa rombel kelas berhasil diambil');
    }

    /**
     * Store Bulk Attendance
     *
     * Menginput absensi kehadiran siswa secara massal untuk kelas tertentu.
     *
     * @authenticated
     *
     * @bodyParam classroom_id integer required ID Kelas. Example: 2
     * @bodyParam date string required Tanggal absensi (YYYY-MM-DD). Example: 2026-05-27
     * @bodyParam schedule_id integer optional ID Jadwal terkait. Example: 4
     * @bodyParam students array required List data absensi per siswa.
     * @bodyParam students.*.student_id integer required ID Siswa. Example: 3
     * @bodyParam students.*.status string required Status absensi (H, S, I, A). Example: H
     * @bodyParam students.*.notes string optional Catatan tambahan absensi. Example: Sakit flu ringan
     */
    public function storeAttendance(Request $request)
    {
        $request->validate([
            'classroom_id' => 'required|exists:classrooms,id',
            'date' => 'required|date_format:Y-m-d',
            'schedule_id' => 'nullable|exists:schedules,id',
            'students' => 'required|array|min:1',
            'students.*.student_id' => 'required|exists:students,id',
            'students.*.status' => 'required|in:H,S,I,A',
            'students.*.notes' => 'nullable|string|max:255',
        ]);

        $activeYear = AcademicYear::current();
        if (!$activeYear) {
            return $this->errorResponse('Tahun akademik aktif tidak ditemukan', 400);
        }

        $recordedCount = 0;

        foreach ($request->students as $s) {
            // Validasi relasi siswa ada di kelas tersebut
            $student = Student::find($s['student_id']);
            if ($student->classroom_id !== (int)$request->classroom_id) {
                continue;
            }

            Attendance::updateOrCreate(
                [
                    'student_id' => $s['student_id'],
                    'classroom_id' => $request->classroom_id,
                    'date' => $request->date,
                ],
                [
                    'academic_year_id' => $activeYear->id,
                    'schedule_id' => $request->schedule_id,
                    'status' => $s['status'],
                    'notes' => $s['notes'] ?? null,
                    'recorded_by' => $request->user()->id,
                ]
            );

            $recordedCount++;
        }

        return $this->successResponse([
            'recorded_count' => $recordedCount
        ], 'Absensi berhasil disimpan');
    }

    /**
     * Get Teaching Journals
     *
     * Mengambil riwayat pengisian jurnal mengajar guru.
     *
     * @authenticated
     */
    public function journals(Request $request)
    {
        $journals = TeachingJournal::where('user_id', $request->user()->id)
            ->with(['subject', 'classroom'])
            ->latest()
            ->get();

        return $this->successResponse(TeachingJournalResource::collection($journals), 'Jurnal mengajar berhasil diambil');
    }

    /**
     * Store Teaching Journal
     *
     * Membuat entri baru jurnal mengajar digital beserta lampiran pendukung.
     *
     * @authenticated
     *
     * @bodyParam subject_id integer required ID Mata Pelajaran. Example: 3
     * @bodyParam classroom_id integer required ID Kelas. Example: 2
     * @bodyParam teaching_date string required Tanggal mengajar (YYYY-MM-DD). Example: 2026-05-27
     * @bodyParam jam_ke integer required Jam keberapa pengajaran. Example: 1
     * @bodyParam topic string required Topik atau materi pelajaran. Example: Aljabar Linier
     * @bodyParam learning_activity string required Aktivitas KBM berlangsung. Example: Ceramah dan tanya jawab
     * @bodyParam learning_method string required Metode KBM. Example: Diskusi Kelompok
     * @bodyParam obstacles string optional Hambatan KBM jika ada. Example: Beberapa proyektor mati
     * @bodyParam notes string optional Catatan guru. Example: Perlu review materi aljabar
     * @bodyParam students_present integer required Jumlah siswa hadir. Example: 32
     * @bodyParam students_absent integer required Jumlah siswa tidak hadir. Example: 2
     * @bodyParam attachment file optional File PDF/Gambar lampiran jurnal (max 10MB).
     */
    public function storeJournal(Request $request)
    {
        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'classroom_id' => 'required|exists:classrooms,id',
            'teaching_date' => 'required|date_format:Y-m-d',
            'jam_ke' => 'required|integer|min:1|max:12',
            'topic' => 'required|string|max:255',
            'learning_activity' => 'required|string|max:5000',
            'learning_method' => 'required|string|max:255',
            'obstacles' => 'nullable|string|max:2000',
            'notes' => 'nullable|string|max:2000',
            'students_present' => 'required|integer|min:0',
            'students_absent' => 'required|integer|min:0',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        $activeYear = AcademicYear::current();
        if (!$activeYear) {
            return $this->errorResponse('Tahun akademik aktif tidak ditemukan', 400);
        }

        $path = null;
        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('uploads/curriculum/journals', 'public');
        }

        $journal = TeachingJournal::create([
            'user_id' => $request->user()->id,
            'subject_id' => $request->subject_id,
            'classroom_id' => $request->classroom_id,
            'academic_year_id' => $activeYear->id,
            'teaching_date' => $request->teaching_date,
            'jam_ke' => $request->jam_ke,
            'topic' => $request->topic,
            'learning_activity' => $request->learning_activity,
            'learning_method' => $request->learning_method,
            'obstacles' => $request->obstacles,
            'notes' => $request->notes,
            'students_present' => $request->students_present,
            'students_absent' => $request->students_absent,
            'attachment_path' => $path,
            'status' => 'draft', // Draf default sebelum di-review
        ]);

        return $this->successResponse(new TeachingJournalResource($journal), 'Jurnal mengajar berhasil disimpan', 201);
    }

    /**
     * Get Messages (Parent Consultations)
     *
     * Mengambil seluruh riwayat pesan konsultasi guru dengan wali murid,
     * lintas seluruh siswa. Mirror dari ApiParentController::messages() —
     * baris log yang sama (ParentMessage), dilihat dari sisi guru.
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

        return $this->successResponse(ParentMessageResource::collection($messages), 'Riwayat pesan berhasil diambil');
    }

    /**
     * Reply to Parent
     *
     * Membalas/memulai pesan konsultasi ke wali murid dari siswa tertentu.
     * Penerima (parent_id siswa) ditentukan otomatis di server — guru cukup
     * menyebut siswa yang dimaksud, bukan id akun wali muridnya. Diizinkan
     * untuk wali kelas siswa tersebut, atau siapa pun yang sudah punya
     * percakapan berjalan dengan siswa ini (mis. guru BK yang dihubungi
     * lebih dulu oleh orang tua).
     *
     * @authenticated
     *
     * @bodyParam student_uuid string required UUID Siswa. Example: 9c1b3e2a-...
     * @bodyParam message string Isi balasan (boleh kosong jika ada lampiran). Example: Baik, akan saya pantau ananda.
     * @bodyParam attachment file Lampiran gambar/PDF (maks 5MB).
     */
    public function sendMessage(Request $request)
    {
        $request->validate([
            'student_uuid' => 'required|string|exists:students,uuid',
            'message' => 'required_without:attachment|nullable|string|max:5000',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $user = $request->user();
        $student = Student::where('uuid', $request->student_uuid)->first();
        if (!$student) {
            return $this->errorResponse('Siswa tidak ditemukan', 404);
        }

        $isHomeroomTeacher = (int) $student->classroom?->teacher_id === (int) $user->id;
        $hasExistingThread = ParentMessage::where('student_id', $student->id)
            ->where(function ($q) use ($user) {
                $q->where('sender_id', $user->id)->orWhere('recipient_id', $user->id);
            })
            ->exists();

        if (!$isHomeroomTeacher && !$hasExistingThread) {
            return $this->errorResponse('Anda tidak memiliki akses untuk mengirim pesan mengenai siswa ini.', 403);
        }

        if (!$student->parent_id) {
            return $this->errorResponse('Siswa ini belum memiliki akun wali murid terdaftar.', 400);
        }

        $attachmentPath = null;
        $attachmentType = null;
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $attachmentPath = $file->store('chat_attachments', 'public');
            $attachmentType = $file->getClientOriginalExtension() === 'pdf' ? 'pdf' : 'image';
        }

        $message = ParentMessage::create([
            'sender_id' => $user->id,
            'recipient_id' => $student->parent_id,
            'student_id' => $student->id,
            'message' => $request->message ?? '',
            'attachment_path' => $attachmentPath,
            'attachment_type' => $attachmentType,
            'is_read' => false,
        ]);

        // Sender di-attach langsung dari user yang sudah diautentikasi agar
        // payload broadcast punya sender_name yang nyata.
        $message->setRelation('sender', $user);
        event(new ChatMessageSent($message));

        return $this->successResponse(new ParentMessageResource($message), 'Pesan berhasil dikirim', 210);
    }

    /**
     * Store CBT Exam with Manual Questions (supporting LaTeX & Images)
     */
    public function storeExamWithQuestions(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'subject_id' => 'nullable|exists:subjects,id',
            'subject_name' => 'nullable|string',
            'duration_minutes' => 'required|integer|min:1',
            'passing_score' => 'nullable|numeric',
            'token' => 'nullable|string',
            'questions' => 'required|array|min:1',
            'questions.*.question_text' => 'required|string',
            'questions.*.options' => 'required|array',
            'questions.*.correct_answer' => 'required|string',
        ]);

        $teacher = $request->user();
        $activeYear = AcademicYear::where('is_active', true)->first() ?: AcademicYear::first();
        $subjectId = $request->subject_id;
        if (!$subjectId && $request->subject_name) {
            $subject = \App\Models\Subject::where('name', 'like', '%' . $request->subject_name . '%')->first();
            $subjectId = $subject ? $subject->id : 1;
        }

        $token = $request->token ?: 'SA1-' . rand(1000, 9999);

        $exam = \App\Models\Exam::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'title' => $request->title,
            'subject_id' => $subjectId ?: 1,
            'classroom_id' => $request->classroom_id ?: null,
            'academic_year_id' => $activeYear ? $activeYear->id : 1,
            'created_by' => $teacher->id,
            'type' => 'ulangan_harian',
            'start_time' => now(),
            'end_time' => now()->addDays(7),
            'duration_minutes' => (int) $request->duration_minutes,
            'total_questions' => count($request->questions),
            'token' => $token,
            'passing_score' => (float) ($request->passing_score ?: 75.0),
            'status' => 'published',
            'shuffle_questions' => (bool) $request->input('shuffle_questions', true),
            'shuffle_options' => (bool) $request->input('shuffle_options', true),
            'is_secure_mode' => (bool) $request->input('require_kiosk_mode', true),
        ]);

        foreach ($request->questions as $index => $qData) {
            $qBank = \App\Models\QuestionBank::create([
                'subject_id' => $subjectId ?: 1,
                'created_by' => $teacher->id,
                'question_text' => $qData['question_text'],
                'question_image' => $qData['image_url'] ?? $qData['question_image'] ?? null,
                'type' => 'multiple_choice',
                'options' => $qData['options'],
                'correct_answer' => $qData['correct_answer'] ?? 'A',
                'grade_level' => 'XII',
            ]);

            \DB::table('exam_questions')->insert([
                'exam_id' => $exam->id,
                'question_bank_id' => $qBank->id,
                'order' => $index + 1,
                'weight' => (float) ($qData['weight'] ?? 10.0),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return $this->successResponse([
            'exam_id' => $exam->id,
            'uuid' => $exam->uuid,
            'title' => $exam->title,
            'token' => $token,
            'total_questions' => count($request->questions),
        ], 'Jadwal ujian dan bank soal berhasil diterbitkan', 201);
    }

    /**
     * Upload Image for Question or Option
     */
    public function uploadExamImage(Request $request)
    {
        $request->validate([
            'image' => 'required|file|mimes:jpeg,jpg,png,webp|max:10240',
        ]);

        $path = $request->file('image')->store('uploads/cbt/questions', 'public');
        $fullUrl = asset('storage/' . $path);

        return $this->successResponse([
            'path' => $path,
            'image_url' => $fullUrl,
        ], 'Gambar berhasil diunggah');
    }
}
