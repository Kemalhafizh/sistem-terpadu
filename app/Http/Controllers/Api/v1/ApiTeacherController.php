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
use App\Models\Exam;
use App\Models\ParentMessage;
use App\Models\QuestionBank;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\TeachingJournal;
use App\Services\CbtAccessControlService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;

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

        // This year's lessons only: earlier years' rows used to count towards
        // "jam mengajar minggu ini" and today's list on the app's home.
        $year = AcademicYear::current();
        $schedules = $year
            ? Schedule::where('teacher_id', $user->id)
                ->where('academic_year_id', $year->id)
                ->with(['subject', 'classroom', 'teacher'])
                ->get()
            : collect();

        return $this->successResponse(ScheduleResource::collection($schedules), __('Jadwal mengajar berhasil diambil'));
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

        // Classes taught or homeroomed this academic year; earlier years' classes
        // used to be counted as "kelas diampu" too.
        $classrooms = Classroom::whereIn('id', Schedule::classroomIdsFor($user, AcademicYear::current()))
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

        return $this->successResponse($data, __('Daftar kelas berhasil diambil'));
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
        // Any teacher could list any class's roster before.
        if (! Schedule::coversClassroom($request->user(), (int) $classroomId, AcademicYear::current())) {
            return $this->errorResponse(__('Anda tidak mengajar di kelas ini pada tahun ajaran aktif.'), 403);
        }

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

        return $this->successResponse($students, __('Daftar siswa rombel kelas berhasil diambil'));
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
        if (! $activeYear) {
            return $this->errorResponse(__('Tahun akademik aktif tidak ditemukan'), 400);
        }

        // Any teacher could record attendance for any class before.
        if (! Schedule::coversClassroom($request->user(), (int) $request->classroom_id, $activeYear)) {
            return $this->errorResponse(__('Anda tidak mengajar di kelas ini pada tahun ajaran aktif.'), 403);
        }
        if ($request->schedule_id
            && ! Schedule::whereKey($request->schedule_id)->where('classroom_id', $request->classroom_id)->exists()) {
            return $this->errorResponse(__('Jadwal ini bukan jadwal kelas tersebut.'), 422);
        }

        $recordedCount = 0;

        foreach ($request->students as $s) {
            // Validasi relasi siswa ada di kelas tersebut
            $student = Student::find($s['student_id']);
            if ($student->classroom_id !== (int) $request->classroom_id) {
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
            'recorded_count' => $recordedCount,
        ], __('Absensi berhasil disimpan'));
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

        return $this->successResponse(TeachingJournalResource::collection($journals), __('Jurnal mengajar berhasil diambil'));
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
        if (! $activeYear) {
            return $this->errorResponse(__('Tahun akademik aktif tidak ditemukan'), 400);
        }

        if (! Schedule::teaches($request->user(), (int) $request->classroom_id, (int) $request->subject_id, $activeYear->id)) {
            return $this->errorResponse(__('Anda tidak mengajar mata pelajaran ini di kelas tersebut pada tahun ajaran aktif.'), 403);
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

        return $this->successResponse(new TeachingJournalResource($journal), __('Jurnal mengajar berhasil disimpan'), 201);
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
        if (! $student) {
            return $this->errorResponse('Siswa tidak ditemukan', 404);
        }

        $isHomeroomTeacher = (int) $student->classroom?->teacher_id === (int) $user->id;
        $hasExistingThread = ParentMessage::where('student_id', $student->id)
            ->where(function ($q) use ($user) {
                $q->where('sender_id', $user->id)->orWhere('recipient_id', $user->id);
            })
            ->exists();

        if (! $isHomeroomTeacher && ! $hasExistingThread) {
            return $this->errorResponse('Anda tidak memiliki akses untuk mengirim pesan mengenai siswa ini.', 403);
        }

        if (! $student->parent_id) {
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

        return $this->successResponse(new ParentMessageResource($message), 'Pesan berhasil dikirim', 201);
    }

    /**
     * Store CBT Exam with Manual Questions (supporting LaTeX & Images)
     */
    public function storeExamWithQuestions(Request $request)
    {
        Gate::authorize('create', Exam::class);

        // No client-supplied token: the entry token is issued only by
        // CbtAccessControlService (5-minute lifetime) via GET cbt/exams/{id}/token.
        // No subject/grade/academic-year fallbacks either — every value is real.
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'subject_id' => 'required|integer|exists:subjects,id',
            'classroom_id' => 'required|integer|exists:classrooms,id',
            'duration_minutes' => 'required|integer|min:5|max:300',
            'passing_score' => 'required|numeric|min:0|max:100',
            'max_violations' => 'sometimes|integer|min:1|max:10',
            'shuffle_questions' => 'sometimes|boolean',
            'shuffle_options' => 'sometimes|boolean',
            'mobile_only' => 'sometimes|boolean',
            'questions' => 'required|array|min:1|max:200',
            'questions.*.question_text' => 'required|string|max:10000',
            'questions.*.image_url' => 'nullable|string|max:2048|url:http,https',
            'questions.*.options' => 'required|array|min:2|max:5',
            'questions.*.options.*.key' => 'required|string|in:A,B,C,D,E',
            'questions.*.options.*.text' => 'nullable|string|max:5000',
            'questions.*.options.*.image_url' => 'nullable|string|max:2048|url:http,https',
            'questions.*.correct_answer' => 'required|string|in:A,B,C,D,E',
            'questions.*.weight' => 'nullable|numeric|min:0.01|max:999.99',
        ]);

        $validator->after(function ($v) use ($request) {
            if ($v->errors()->isNotEmpty()) {
                return;
            }
            foreach ($request->input('questions', []) as $i => $q) {
                $keys = array_column($q['options'], 'key');
                if (count($keys) !== count(array_unique($keys))) {
                    $v->errors()->add("questions.$i.options", 'Soal #'.($i + 1).': kunci pilihan jawaban tidak boleh ganda.');
                }
                if (! in_array($q['correct_answer'], $keys, true)) {
                    $v->errors()->add("questions.$i.correct_answer", 'Soal #'.($i + 1).': kunci jawaban harus salah satu pilihan yang ada.');
                }
                foreach ($q['options'] as $opt) {
                    if (blank($opt['text'] ?? null) && blank($opt['image_url'] ?? null)) {
                        $v->errors()->add("questions.$i.options", 'Soal #'.($i + 1).": pilihan {$opt['key']} harus berisi teks atau gambar.");
                    }
                }
            }
        });

        $validator->validate();

        $activeYear = AcademicYear::where('is_active', true)->first();
        if (! $activeYear) {
            return $this->errorResponse('Tahun ajaran aktif belum diatur. Hubungi admin sekolah.', 422);
        }

        $teacher = $request->user();
        $classroom = Classroom::findOrFail($request->integer('classroom_id'));
        $questions = $request->input('questions');

        $exam = DB::transaction(function () use ($request, $teacher, $classroom, $activeYear, $questions) {
            $exam = Exam::create([
                'title' => $request->input('title'),
                'subject_id' => $request->integer('subject_id'),
                'classroom_id' => $classroom->id,
                'academic_year_id' => $activeYear->id,
                'created_by' => $teacher->id,
                'type' => 'ulangan_harian',
                'start_time' => now(),
                'end_time' => now()->addDays(7),
                'duration_minutes' => $request->integer('duration_minutes'),
                'total_questions' => count($questions),
                'passing_score' => (float) $request->input('passing_score'),
                'max_violations' => $request->integer('max_violations', 3),
                'status' => 'published',
                'shuffle_questions' => $request->boolean('shuffle_questions', true),
                'shuffle_options' => $request->boolean('shuffle_options', true),
                'mobile_only' => $request->boolean('mobile_only', true),
            ]);

            foreach ($questions as $index => $qData) {
                $qBank = QuestionBank::create([
                    'subject_id' => $exam->subject_id,
                    'created_by' => $teacher->id,
                    'question_text' => $qData['question_text'],
                    'question_image' => $qData['image_url'] ?? null,
                    'type' => 'multiple_choice',
                    'options' => array_map(fn ($o) => [
                        'key' => $o['key'],
                        'text' => $o['text'] ?? '',
                        'image_url' => $o['image_url'] ?? null,
                    ], $qData['options']),
                    'correct_answer' => $qData['correct_answer'],
                    'grade_level' => $classroom->grade_level,
                ]);

                $exam->questions()->attach($qBank->id, [
                    'order' => $index + 1,
                    'weight' => (float) ($qData['weight'] ?? 10.0),
                ]);
            }

            return $exam->refresh()->load(['subject', 'classroom']);
        });

        return $this->successResponse([
            'exam_id' => $exam->id,
            'uuid' => $exam->uuid,
            'title' => $exam->title,
            'type' => $exam->type,
            'status' => $exam->status,
            'subject' => $exam->subject?->name,
            'classroom' => $exam->classroom?->name,
            'start_time' => $exam->start_time?->toIso8601String(),
            'end_time' => $exam->end_time?->toIso8601String(),
            'duration_minutes' => $exam->duration_minutes,
            'total_questions' => $exam->total_questions,
            'created_by' => $exam->created_by,
            'operator_id' => $exam->operator_id,
            // Same rule as GET cbt/exams/{id}/token — tells the app whether it
            // may open the proctor screen (which fetches the real token).
            'can_view_token' => app(CbtAccessControlService::class)->canViewToken($teacher, $exam),
        ], 'Ujian dan bank soal berhasil diterbitkan', 201);
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
        $fullUrl = asset('storage/'.$path);

        return $this->successResponse([
            'path' => $path,
            'image_url' => $fullUrl,
        ], 'Gambar berhasil diunggah');
    }
}
