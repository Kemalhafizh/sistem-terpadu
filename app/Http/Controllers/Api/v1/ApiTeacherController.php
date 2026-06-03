<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ScheduleResource;
use App\Http\Resources\TeachingJournalResource;
use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Classroom;
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
}
