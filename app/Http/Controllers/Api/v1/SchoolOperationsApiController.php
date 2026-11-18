<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\AcademicEvent;
use App\Models\AcademicYear;
use App\Models\HealthScreeningResult;
use App\Models\Registration;
use App\Models\SpmbDocument;
use App\Models\SpmbTrack;
use App\Models\SpmbWave;
use App\Models\Student;
use App\Models\UksMedicine;
use App\Models\UksVisit;
use App\Notifications\UksVisitNotification;
use App\Support\Access\StudentDataAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SchoolOperationsApiController extends Controller
{
    // ==========================================
    // KALENDER AKADEMIK & EVENT SEKOLAH
    // ==========================================

    /**
     * The shape the Android app's AcademicCalendarEventItem reads
     * (category/start_time/end_time/location/is_holiday). : the app
     * now reads the same calendar as the web (AcademicEvent) instead of the
     * removed second "enterprise" calendar. Events have no location column,
     * so `location` is null rather than made up.
     */
    private function calendarEventToArray(AcademicEvent $event): array
    {
        return [
            'id' => $event->id,
            'title' => $event->title,
            'description' => $event->description,
            'category' => $event->event_type,
            'start_time' => $event->start_date?->toDateTimeString(),
            'end_time' => $event->end_date?->toDateTimeString(),
            'location' => null,
            'is_holiday' => $event->event_type === 'holiday',
        ];
    }

    public function calendarEvents(Request $request): JsonResponse
    {
        $request->validate(['month' => 'nullable|integer|between:1,12', 'year' => 'nullable|integer|min:2000']);

        $events = AcademicEvent::visibleTo($request->user())
            ->when($request->query('month'), fn ($query, $month) => $query->whereMonth('start_date', $month))
            ->when($request->query('year'), fn ($query, $year) => $query->whereYear('start_date', $year))
            ->when($request->query('category'), fn ($query, $category) => $query->where('event_type', $category))
            ->orderBy('start_date')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $events->map(fn ($e) => $this->calendarEventToArray($e))->values(),
        ]);
    }

    public function exportIcal(Request $request)
    {
        $events = AcademicEvent::visibleTo($request->user())
            ->whereYear('start_date', $request->query('year', now()->year))
            ->orderBy('start_date')
            ->get();

        $school = school_short_name();
        $ics = "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-//{$school}//Sula-One//ID\r\nCALSCALE:GREGORIAN\r\nMETHOD:PUBLISH\r\nX-WR-CALNAME:Kalender Akademik {$school}\r\n";

        foreach ($events as $event) {
            $start = $event->start_date->copy()->utc()->format('Ymd\THis\Z');
            // An event without an end is a point in time, not a made-up two-hour block.
            $end = ($event->end_date ?? $event->start_date)->copy()->utc()->format('Ymd\THis\Z');
            $title = $event->title;
            $desc = $event->description ?? '';
            $uid = $event->id.'@sultanagung1.sch.id';

            $ics .= "BEGIN:VEVENT\r\nUID:{$uid}\r\nDTSTART:{$start}\r\nDTEND:{$end}\r\nSUMMARY:{$title}\r\nDESCRIPTION:{$desc}\r\nEND:VEVENT\r\n";
        }

        $ics .= "END:VCALENDAR\r\n";

        return response($ics, 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="kalender-akademik-sulaone.ics"',
        ]);
    }

    // ==========================================
    // SPMB / PPDB MOBILE
    // ==========================================

    public function spmbWaves(): JsonResponse
    {
        // Real columns: SpmbWave.status (enum), not the "is_active" boolean
        // this used to filter on — that column never existed, so this
        // always matched zero rows and fell through to fabricated data.
        $waves = SpmbWave::with('period.tracks')->where('status', 'open')->get();

        $data = $waves->map(function ($wave) {
            return [
                'id' => $wave->id,
                'name' => $wave->name,
                'academic_year' => $wave->period?->academicYear?->name ?? '-',
                'start_date' => $wave->registration_start?->toDateString(),
                'end_date' => $wave->registration_end?->toDateString(),
                'is_open' => $wave->status === 'open',
                'fee' => (int) $wave->registration_fee,
                // Tracks belong to the wave's period, not the wave itself.
                'tracks' => $wave->period?->tracks->map(fn ($t) => [
                    'id' => $t->id,
                    'name' => $t->name,
                    'quota' => $t->quota,
                ])->values() ?? [],
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function spmbRegister(Request $request): JsonResponse
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'nisn' => 'required|string|max:20',
            'school_origin' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20',
            'track_name' => 'nullable|string',
        ]);

        $openWave = SpmbWave::where('status', 'open')->with('period')->first();
        if (! $openWave) {
            return response()->json([
                'success' => false,
                'message' => 'Pendaftaran SPMB sedang ditutup. Tidak ada gelombang yang aktif saat ini.',
            ], 422);
        }

        $track = $request->filled('track_name')
            ? SpmbTrack::where('spmb_period_id', $openWave->spmb_period_id)
                ->where('name', 'like', '%'.$request->input('track_name').'%')
                ->first()
            : null;

        $academicYearId = $openWave->period?->academic_year_id
            ?? AcademicYear::where('is_active', true)->first()?->id;

        if (! $academicYearId) {
            return response()->json([
                'success' => false,
                'message' => 'Tahun ajaran aktif tidak ditemukan. Hubungi admin sekolah.',
            ], 422);
        }

        $registration = Registration::create([
            'registration_number' => Registration::generateRegistrationNumber(),
            'full_name' => $request->input('full_name'),
            'nisn' => $request->input('nisn'),
            'previous_school' => $request->input('school_origin'),
            'parent_phone' => $request->input('phone_number'),
            'status' => 'pending',
            'document_status' => 'incomplete',
            'academic_year_id' => $academicYearId,
            'spmb_wave_id' => $openWave->id,
            'spmb_track_id' => $track?->id,
            'user_id' => $request->user()?->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pendaftaran SPMB berhasil dikirim. Simpan Nomor Pendaftaran Anda!',
            'data' => [
                'registration_number' => $registration->registration_number,
                'full_name' => $registration->full_name,
                'nisn' => $registration->nisn,
                'school_origin' => $registration->previous_school,
                'track' => $track?->name ?? 'Belum ditentukan',
                'status' => 'menunggu_verifikasi_berkas',
                'submission_date' => $registration->created_at->toIso8601String(),
                'test_schedule' => 'Akan diumumkan melalui SuperApp setelah berkas diverifikasi.',
            ],
        ], 201);
    }

    public function spmbCheckStatus(Request $request, string $registrationNo): JsonResponse
    {
        $registration = Registration::where('registration_number', $registrationNo)
            ->with('spmbTrack')
            ->first();

        if (! $registration) {
            return response()->json([
                'success' => false,
                'message' => 'Nomor pendaftaran tidak ditemukan.',
            ], 404);
        }

        $verificationStatusMap = [
            'pending' => 'menunggu',
            'reviewing' => 'menunggu',
            'accepted' => 'terverifikasi',
            'rejected' => 'ditolak',
        ];
        $finalStatusMap = [
            'pending' => null,
            'reviewing' => null,
            'accepted' => 'diterima',
            'rejected' => 'tidak_lulus',
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'registration_number' => $registration->registration_number,
                'full_name' => $registration->full_name,
                'track' => $registration->spmbTrack?->name ?? 'Belum ditentukan',
                'verification_status' => $verificationStatusMap[$registration->status] ?? 'menunggu',
                // No CBT/interview scheduling columns exist on this table —
                // left honestly null rather than a fabricated fixed date.
                'cbt_test_date' => null,
                'interview_date' => null,
                'final_status' => $finalStatusMap[$registration->status] ?? null,
                'notes' => $registration->admin_notes,
            ],
        ]);
    }

    /**
     * Real document upload against an already-created registration — the
     * mobile registration flow collects documents in Step 3 before the
     * record exists (Step 4 submits), so the Android side stores real
     * picked files locally and uploads them here right after
     * spmbRegister() succeeds, rather than needing the flow reordered.
     */
    public function spmbUploadDocument(Request $request, string $registrationNo): JsonResponse
    {
        $request->validate([
            'document_type' => 'required|string|max:50',
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $registration = Registration::where('registration_number', $registrationNo)->first();
        if (! $registration) {
            return response()->json(['success' => false, 'message' => 'Nomor pendaftaran tidak ditemukan.'], 404);
        }

        $path = $request->file('file')->store('spmb/mobile_documents', 'public');

        $document = SpmbDocument::create([
            'registration_id' => $registration->id,
            'document_type' => $request->input('document_type'),
            'file_path' => $path,
            'verification_status' => 'pending',
        ]);

        // Once every one of the 4 documents the mobile form collects has
        // arrived, the registration is no longer "incomplete".
        $requiredTypes = ['photo', 'kk', 'rapor', 'sertifikat'];
        $uploadedTypes = SpmbDocument::where('registration_id', $registration->id)
            ->pluck('document_type')
            ->unique();
        if (collect($requiredTypes)->every(fn ($t) => $uploadedTypes->contains($t))) {
            $registration->update(['document_status' => 'submitted']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Dokumen berhasil diunggah.',
            'data' => [
                'id' => $document->id,
                'document_type' => $document->document_type,
                'file_url' => Storage::url($path),
            ],
        ], 201);
    }

    // ==========================================
    // REKAM MEDIS UKS & SKRINING
    // ==========================================

    /**
     * Resolves the target Student model for UKS/health-screening endpoints,
     * enforcing that a student only ever sees their own records and a parent
     * only their own children's — comparing against Student.id (not
     * User.id, which the previous version of this method conflated: a
     * student's users.id and students.id are different auto-increment
     * sequences on different tables).
     */
    private function resolveUksTargetStudent(Request $request, ?int $requestedStudentId): Student
    {
        $user = $request->user();

        if (in_array($user->role, ['student', 'siswa'], true)) {
            $own = $user->student;
            if (! $own) {
                abort(404, 'Data siswa untuk akun ini tidak ditemukan.');
            }
            if ($requestedStudentId && $requestedStudentId !== $own->id) {
                abort(403, 'Akses ditolak. Anda hanya berhak melihat data UKS Anda sendiri.');
            }

            return $own;
        }

        if (in_array($user->role, StudentDataAccess::PARENT_ROLES, true)) {
            if (! $requestedStudentId) {
                abort(422, 'Parameter student_id wajib diisi untuk melihat data UKS anak.');
            }
            $child = Student::where('id', $requestedStudentId)->where('parent_id', $user->id)->first();
            if (! $child) {
                abort(403, 'Akses ditolak. Anda hanya berhak melihat data UKS anak Anda.');
            }

            return $child;
        }

        // Staff/nurse/admin: must specify which student.
        if (! $requestedStudentId) {
            abort(422, 'Parameter student_id wajib diisi.');
        }
        $student = Student::find($requestedStudentId);
        if (! $student) {
            abort(404, 'Data siswa tidak ditemukan.');
        }

        return $student;
    }

    public function uksVisits(Request $request): JsonResponse
    {
        $requestedId = $request->filled('student_id') ? (int) $request->query('student_id') : null;
        $student = $this->resolveUksTargetStudent($request, $requestedId);

        $visits = UksVisit::with(['medicines', 'handler'])
            ->where('student_id', $student->id)
            ->latest('visit_time')
            ->get()
            ->map(function (UksVisit $v) {
                return [
                    'id' => $v->id,
                    'visit_time' => $v->visit_time->toDateTimeString(),
                    'complaints' => $v->complaint,
                    'diagnosis' => $v->diagnosis,
                    'treatment' => $v->treatment,
                    'temperature' => $v->temperature !== null ? (string) $v->temperature : null,
                    'blood_pressure' => $v->blood_pressure,
                    'medicines' => $v->medicines->map(fn ($m) => [
                        'name' => $m->name,
                        'dose' => $m->pivot->quantity.'x',
                    ])->values(),
                    'action' => $v->disposition,
                    'handler_name' => $v->handler?->name,
                    'parent_notified' => (bool) $v->parent_notified,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $visits,
        ]);
    }

    public function recordUksVisit(Request $request): JsonResponse
    {
        $request->validate([
            'student_id' => 'required|integer|exists:students,id',
            'complaint' => 'required|string',
            'diagnosis' => 'nullable|string',
            'temperature' => 'nullable|numeric|min:30|max:45',
            'blood_pressure' => 'nullable|string|max:20',
            'actions' => 'nullable|array',
            'actions.*' => 'string',
            'medicines' => 'nullable|array',
            'medicines.*.id' => 'required_with:medicines|integer|exists:uks_medicines,id',
            'medicines.*.quantity' => 'nullable|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        $visit = DB::transaction(function () use ($request) {
            $visit = UksVisit::create([
                'student_id' => $request->input('student_id'),
                'handled_by' => $request->user()->id,
                'visit_time' => now(),
                'complaint' => $request->input('complaint'),
                'diagnosis' => $request->input('diagnosis'),
                'treatment' => collect($request->input('actions', []))->implode(', ') ?: null,
                'temperature' => $request->input('temperature'),
                'blood_pressure' => $request->input('blood_pressure'),
                'severity' => 'ringan',
                'disposition' => 'istirahat_uks',
                'parent_notified' => false,
                'notes' => $request->input('notes'),
            ]);

            foreach ($request->input('medicines', []) as $med) {
                $medicine = UksMedicine::find($med['id']);
                if (! $medicine) {
                    continue;
                }
                $qty = (int) ($med['quantity'] ?? 1);
                $visit->medicines()->attach($medicine->id, ['quantity' => $qty]);
                $medicine->decrement('stock', min($qty, $medicine->stock));
            }

            $student = $visit->load('student.parent')->student;
            if ($student?->parent) {
                $student->parent->notify(new UksVisitNotification($visit));
                $visit->update(['parent_notified' => true]);
            }

            return $visit->load('medicines', 'handler');
        });

        return response()->json([
            'success' => true,
            'message' => $visit->parent_notified
                ? 'Kunjungan UKS berhasil dicatat & notifikasi terkirim ke Orang Tua.'
                : 'Kunjungan UKS berhasil dicatat. Siswa tidak memiliki kontak orang tua terdaftar untuk notifikasi.',
            'data' => [
                'id' => $visit->id,
                'visit_time' => $visit->visit_time->toDateTimeString(),
                'complaints' => $visit->complaint,
                'diagnosis' => $visit->diagnosis,
                'treatment' => $visit->treatment,
                'temperature' => $visit->temperature !== null ? (string) $visit->temperature : null,
                'blood_pressure' => $visit->blood_pressure,
                'medicines' => $visit->medicines->map(fn ($m) => [
                    'name' => $m->name,
                    'dose' => $m->pivot->quantity.'x',
                ])->values(),
                'action' => $visit->disposition,
                'handler_name' => $visit->handler?->name,
                'parent_notified' => (bool) $visit->parent_notified,
            ],
        ], 201);
    }

    /**
     * Real student-name/NISN search — the mobile "Catat Pasien UKS" form
     * previously took a free-text student name with no way to resolve a
     * real student_id, which is exactly why recordUksVisit() never actually
     * had one to save against.
     */
    public function searchUksStudents(Request $request): JsonResponse
    {
        $query = trim((string) $request->query('q', ''));
        if (mb_strlen($query) < 2) {
            return response()->json(['success' => true, 'data' => []]);
        }

        // Student.name is a computed accessor proxying User.name (no real
        // "name" column on students), so matching by name needs a relation
        // constraint rather than a direct where().
        $students = Student::with(['classroom', 'user'])
            ->where(function ($q) use ($query) {
                $q->whereHas('user', function ($uq) use ($query) {
                    $uq->where('name', 'like', "%{$query}%");
                })
                    ->orWhere('nisn', 'like', "%{$query}%")
                    ->orWhere('nis', 'like', "%{$query}%");
            })
            ->limit(10)
            ->get()
            ->map(fn ($s) => [
                'id' => $s->id,
                'name' => $s->name,
                'nisn' => $s->nisn,
                'classroom_name' => $s->classroom?->name,
            ]);

        return response()->json(['success' => true, 'data' => $students]);
    }

    /**
     * Real medicine inventory — the mobile form previously showed a
     * hardcoded stock list that never actually deducted anything server-side.
     */
    public function uksMedicines(Request $request): JsonResponse
    {
        $medicines = UksMedicine::orderBy('name')->get()->map(fn ($m) => [
            'id' => (string) $m->id,
            'name' => $m->name,
            'quantity' => $m->stock,
        ]);

        return response()->json(['success' => true, 'data' => $medicines]);
    }

    public function healthScreening(Request $request, ?int $studentId = null): JsonResponse
    {
        $student = $this->resolveUksTargetStudent($request, $studentId);

        $latestResult = HealthScreeningResult::where('student_id', $student->id)
            ->with('screening')
            ->latest('id')
            ->first();

        $heightCm = $latestResult?->height_cm;
        $weightKg = $latestResult?->weight_kg;
        $bmi = ($heightCm && $weightKg && (float) $heightCm > 0)
            ? round((float) $weightKg / (((float) $heightCm / 100) ** 2), 1)
            : null;
        $bmiCategory = match (true) {
            $bmi === null => null,
            $bmi < 18.5 => 'Kurus / Underweight',
            $bmi < 25 => 'Normal / Ideal',
            $bmi < 30 => 'Gemuk / Overweight',
            default => 'Obesitas',
        };

        $allergies = $student->allergies
            ? array_values(array_filter(array_map('trim', explode(',', $student->allergies))))
            : [];

        return response()->json([
            'success' => true,
            'data' => [
                'student_id' => $student->id,
                'student_name' => $student->name,
                'nisn' => $student->nisn,
                'blood_type' => $student->blood_type,
                'allergies' => $allergies,
                'height_cm' => $heightCm !== null ? (int) round((float) $heightCm) : null,
                'weight_kg' => $weightKg !== null ? (int) round((float) $weightKg) : null,
                'bmi' => $bmi,
                'bmi_category' => $bmiCategory,
                'vision_right' => $latestResult?->vision_right,
                'vision_left' => $latestResult?->vision_left,
                'dental_health' => $latestResult?->dental_status,
                // No hearing-test column exists anywhere in the schema —
                // left honestly null rather than fabricated.
                'hearing' => null,
                'last_screened_at' => $latestResult?->screening?->screening_date?->toDateString(),
                'screener' => $latestResult ? ($latestResult->screening?->title ?? 'Tim UKS Sekolah') : null,
            ],
        ]);
    }
}
