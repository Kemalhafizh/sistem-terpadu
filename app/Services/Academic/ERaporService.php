<?php

namespace App\Services\Academic;

use App\Models\AcademicEvent;
use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Classroom;
use App\Models\ClassSession;
use App\Models\DailyAssessmentScore;
use App\Models\Grade;
use App\Models\PositionAssignment;
use App\Models\ReportCard;
use App\Models\ReportCardEntry;
use App\Models\ReportCardPdf;
use App\Models\ReportCardTemplate;
use App\Models\SessionAttendance;
use App\Models\Setting;
use App\Models\Student;
use App\Models\StudentLeaveRequest;
use App\Models\Subject;
use App\Models\User;
use App\Support\School;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use ZipArchive;

/**
 * Integrasi e-Rapor Kemendikdasmen Kurikulum Merdeka (D5):
 * pengelompokan mapel per fase/tingkat, draf capaian kompetensi berbasis TP,
 * rekap absensi harian C2/C3, pemantauan kelengkapan wali kelas,
 * ekspor CSV e-Rapor, unggah massal PDF resmi berdasar NISN,
 * penerbitan massal per angkatan, serta resolusi pejabat historis per tanggal rapor.
 */
class ERaporService
{
    private const IPA_CODES = ['FSK', 'FIS', 'KMA', 'KIM', 'BIO'];

    private const IPS_CODES = ['SOS', 'SJH', 'SEJ', 'GEO', 'EKO'];

    private const CIRI_KHAS_CODES = ['PAI', 'BTAQ', 'THFDZ', 'THF', 'ARB', 'FQH', 'AQD', 'AQH', 'SKI', 'BK', 'PSB'];

    private const PHASE_X_CIRI_KHAS_NAMES = [
        'fiqih',
        'aqidah akhlak',
        'bahasa arab',
        "al-qur'an hadits",
        'al-quran hadits',
        'ski',
        'pai',
        'btaq',
        'program tahfidz',
        'tahfidz',
    ];

    private const PHASE_F_CIRI_KHAS_NAMES = [
        'bahasa arab',
        'pembiasaan sikap baik',
        'pai',
        'btaq',
        'program tahfidz',
        'tahfidz',
    ];

    /**
     * Resolve historical principal and homeroom teacher at a given report card date.
     *
     * @return array{principal: ?User, principal_name: ?string, principal_nip: ?string, wali_kelas: ?User}
     */
    public function resolveOfficials(Classroom $classroom, ?CarbonInterface $issuedDate = null): array
    {
        $anchor = $issuedDate ?? CarbonImmutable::today();
        $principalUser = Schema::hasTable('position_assignments')
            ? PositionAssignment::holderOn('kepala_sekolah', $anchor)
            : User::query()->whereIn('role', ['kepala_sekolah', 'kepsek'])->first();

        $configuredPrincipalName = School::principalName();
        $principalName = $principalUser?->name ?? $configuredPrincipalName;

        $principalNip = null;
        if ($configuredPrincipalName === null || $principalName === $configuredPrincipalName) {
            $principalNip = School::principalNip();
        }

        $classroom->loadMissing('teacher');

        return [
            'principal' => $principalUser,
            'principal_name' => $principalName,
            'principal_nip' => $principalNip,
            'wali_kelas' => $classroom->teacher,
        ];
    }

    /**
     * Determine Kemendikdasmen Kurikulum Merdeka phase ('E' for X, 'F' for XI-XII)
     * and continuous semester number (1..6).
     */
    public function inferPhase(?string $gradeLevel): string
    {
        return strtoupper(trim((string) $gradeLevel)) === 'X' ? 'E' : 'F';
    }

    public function inferSemesterNumber(?string $gradeLevel, string $semester = 'ganjil'): int
    {
        $isEven = strtolower(trim($semester)) === 'genap';

        return match (strtoupper(trim((string) $gradeLevel))) {
            'X' => $isEven ? 2 : 1,
            'XI' => $isEven ? 4 : 3,
            'XII' => $isEven ? 6 : 5,
            default => $isEven ? 2 : 1,
        };
    }

    /**
     * Group subjects into Kemendikdasmen Kurikulum Merdeka sections:
     * - kelompok_a (umum + muatan lokal; grade X separates IPA & IPS sub-groups)
     * - kelompok_b (mapel pilihan, grade XI-XII only)
     * - kelompok_c (ciri khas sekolah)
     *
     * @param  Collection<int, Subject>  $subjects
     * @return array{
     *     phase: string,
     *     kelompok_a: array{
     *         umum: Collection<int, Subject>,
     *         ipa: Collection<int, Subject>,
     *         ips: Collection<int, Subject>,
     *         muatan_lokal: Collection<int, Subject>,
     *     },
     *     kelompok_b: Collection<int, Subject>,
     *     kelompok_c: Collection<int, Subject>,
     * }
     */
    public function groupSubjects(Collection $subjects, string $gradeLevel, ?Classroom $classroom = null): array
    {
        $grade = strtoupper(trim($gradeLevel));
        $phase = $this->inferPhase($grade);

        $electiveIds = collect();
        if ($classroom && in_array($grade, ['XI', 'XII'], true) && Schema::hasTable('classroom_subjects')) {
            $electiveIds = $classroom->electiveSubjects()->pluck('subjects.id');
        }

        $kelompokAUmum = collect();
        $kelompokAIpa = collect();
        $kelompokAIps = collect();
        $kelompokALokal = collect();
        $kelompokB = collect();
        $kelompokC = collect();

        foreach ($subjects as $subject) {
            $category = strtolower(trim((string) $subject->category));
            $code = strtoupper(trim((string) $subject->code));
            $nameLower = strtolower(trim((string) $subject->name));
            $displayGroup = strtolower(trim((string) ($subject->display_group ?? '')));

            if ($category === Subject::CATEGORY_CIRI_KHAS || $category === 'ciri khas' || in_array($code, self::CIRI_KHAS_CODES, true) || $this->matchesCiriKhasName($nameLower, $grade)) {
                $kelompokC->push($subject);

                continue;
            }

            if (in_array($grade, ['XI', 'XII'], true) && ($category === Subject::CATEGORY_PILIHAN || $electiveIds->contains($subject->id))) {
                $kelompokB->push($subject);

                continue;
            }

            if ($category === Subject::CATEGORY_MUATAN_LOKAL || $category === 'lokal') {
                $kelompokALokal->push($subject);

                continue;
            }

            if ($grade === 'X') {
                if ($displayGroup === 'ipa' || in_array($code, self::IPA_CODES, true) || str_contains($nameLower, 'fisika') || str_contains($nameLower, 'kimia') || str_contains($nameLower, 'biologi')) {
                    $kelompokAIpa->push($subject);

                    continue;
                }
                if ($displayGroup === 'ips' || in_array($code, self::IPS_CODES, true) || str_contains($nameLower, 'sosiologi') || str_contains($nameLower, 'sejarah') || str_contains($nameLower, 'geografi') || str_contains($nameLower, 'ekonomi')) {
                    $kelompokAIps->push($subject);

                    continue;
                }
            }

            $kelompokAUmum->push($subject);
        }

        return [
            'phase' => $phase,
            'kelompok_a' => [
                'umum' => $kelompokAUmum->values(),
                'ipa' => $kelompokAIpa->values(),
                'ips' => $kelompokAIps->values(),
                'muatan_lokal' => $kelompokALokal->values(),
            ],
            'kelompok_b' => $kelompokB->values(),
            'kelompok_c' => $kelompokC->values(),
        ];
    }

    /**
     * Generate draft capaian kompetensi from DailyAssessment learning objectives (TP).
     * Never outputs "FALSE" or fabricated text when TPs are missing; never duplicates
     * a single TP into both strongest and weakest clauses.
     *
     * @return array{
     *     strongest_tp: ?string,
     *     strongest_score: ?float,
     *     weakest_tp: ?string,
     *     weakest_score: ?float,
     *     draft: ?string,
     *     tp_count: int,
     * }
     */
    public function generateTpCapaian(Student $student, Subject $subject, AcademicYear $year): array
    {
        $scores = DailyAssessmentScore::query()
            ->with('dailyAssessment')
            ->where('student_id', $student->id)
            ->whereHas('dailyAssessment', function ($q) use ($subject, $year) {
                $q->where('subject_id', $subject->id)
                    ->where('academic_year_id', $year->id)
                    ->whereNotNull('learning_objective')
                    ->where('learning_objective', '!=', '');
            })
            ->get();

        $tpBuckets = [];
        foreach ($scores as $scoreRow) {
            $tp = trim((string) ($scoreRow->dailyAssessment?->learning_objective ?? ''));
            $final = $scoreRow->final_score;
            if ($tp === '' || $final === null) {
                continue;
            }
            $tpBuckets[$tp][] = (float) $final;
        }

        if ($tpBuckets === []) {
            return [
                'strongest_tp' => null,
                'strongest_score' => null,
                'weakest_tp' => null,
                'weakest_score' => null,
                'draft' => null,
                'tp_count' => 0,
            ];
        }

        $averages = [];
        foreach ($tpBuckets as $tp => $vals) {
            $averages[$tp] = round(array_sum($vals) / count($vals), 2);
        }

        arsort($averages);
        $tpNames = array_keys($averages);
        $strongestTp = $tpNames[0];
        $strongestScore = $averages[$strongestTp];
        $weakestTp = end($tpNames);
        $weakestScore = $averages[$weakestTp];

        $aMin = (float) Setting::get('rapor_predikat_a_min', 90);
        $bMin = (float) Setting::get('rapor_predikat_b_min', 80);
        $cMin = (float) Setting::get('rapor_predikat_c_min', $subject->kkm ?? 70);

        $opening = $this->capaianOpeningPhrase($strongestScore, $aMin, $bMin, $cMin);

        if (count($averages) === 1 || mb_strtolower($strongestTp) === mb_strtolower($weakestTp)) {
            $draft = $strongestScore < $cMin
                ? "Perlu bimbingan dalam {$strongestTp}."
                : "{$opening} dalam {$strongestTp}.";
        } else {
            $firstClause = $strongestScore < $cMin
                ? "Perlu bimbingan dalam {$strongestTp}"
                : "{$opening} dalam {$strongestTp}";

            $secondClause = $weakestScore < $aMin
                ? "dan perlu bimbingan dalam {$weakestTp}"
                : "serta menguasai {$weakestTp} dengan baik";

            $draft = "{$firstClause} {$secondClause}.";
        }

        return [
            'strongest_tp' => $strongestTp,
            'strongest_score' => $strongestScore,
            'weakest_tp' => $weakestTp,
            'weakest_score' => $weakestScore,
            'draft' => $draft,
            'tp_count' => count($averages),
        ];
    }

    /**
     * Aggregate day-level attendance (hadir/sakit/izin/alpa) for rapor period:
     * - Approved leave requests (C3) mark the whole covered date(s) as sakit/izin.
     * - A day counts as Alpha ONLY if at least 1 class session/attendance exists on that date
     *   AND the student has zero present (hadir/telat/H) records across the entire day.
     * - Partial-day absences are surfaced separately without inflating rapor Alpha.
     *
     * @return array{
     *     hadir: int,
     *     sakit: int,
     *     izin: int,
     *     alpa: int,
     *     partial_absence_days: int,
     *     partial_absence_dates: array<int, string>,
     * }
     */
    public function calculateDayLevelAttendance(
        Student $student,
        AcademicYear $year,
        ?CarbonInterface $from = null,
        ?CarbonInterface $to = null,
    ): array {
        $start = $from?->toDateString() ?? ($year->start_date ? CarbonImmutable::parse($year->start_date)->toDateString() : null);
        $end = $to?->toDateString() ?? ($year->end_date ? CarbonImmutable::parse($year->end_date)->toDateString() : null);

        $leaveDates = [];
        if (Schema::hasTable('student_leave_requests')) {
            $leaves = StudentLeaveRequest::query()
                ->where('student_id', $student->id)
                ->whereIn('status', StudentLeaveRequest::APPROVED_STATUSES)
                ->where(function ($q) use ($year) {
                    $q->where('academic_year_id', $year->id)
                        ->orWhereNull('academic_year_id');
                })
                ->when($start !== null, fn ($q) => $q->whereDate('end_date', '>=', $start))
                ->when($end !== null, fn ($q) => $q->whereDate('start_date', '<=', $end))
                ->get(['type', 'start_date', 'end_date']);

            foreach ($leaves as $leave) {
                $cursor = CarbonImmutable::parse($leave->start_date)->startOfDay();
                $leaveEnd = CarbonImmutable::parse($leave->end_date)->startOfDay();
                if ($start !== null && $cursor->lt(CarbonImmutable::parse($start))) {
                    $cursor = CarbonImmutable::parse($start);
                }
                if ($end !== null && $leaveEnd->gt(CarbonImmutable::parse($end))) {
                    $leaveEnd = CarbonImmutable::parse($end);
                }
                while ($cursor->lte($leaveEnd)) {
                    $dayKey = $cursor->toDateString();
                    $leaveDates[$dayKey] = $leave->type === StudentLeaveRequest::TYPE_SAKIT ? 'sakit' : 'izin';
                    $cursor = $cursor->addDay();
                }
            }
        }

        $sessionByDate = [];
        if (Schema::hasTable('session_attendances') && Schema::hasTable('class_sessions')) {
            $sessionRows = SessionAttendance::query()
                ->with('classSession:id,session_date,academic_year_id')
                ->where('student_id', $student->id)
                ->whereHas('classSession', function ($q) use ($year, $start, $end) {
                    $q->where('academic_year_id', $year->id)
                        ->when($start !== null, fn ($sq) => $sq->where('session_date', '>=', $start))
                        ->when($end !== null, fn ($sq) => $sq->where('session_date', '<=', $end));
                })
                ->get();

            foreach ($sessionRows as $row) {
                $day = (string) ($row->classSession?->session_date ?? '');
                if ($day !== '') {
                    $sessionByDate[$day][] = strtolower((string) $row->status);
                }
            }
        }

        $dailyByDate = [];
        $dailyRows = Attendance::query()
            ->where('student_id', $student->id)
            ->where('academic_year_id', $year->id)
            ->when($start !== null, fn ($q) => $q->whereDate('date', '>=', $start))
            ->when($end !== null, fn ($q) => $q->whereDate('date', '<=', $end))
            ->get(['date', 'status']);

        foreach ($dailyRows as $row) {
            $day = CarbonImmutable::parse($row->date)->toDateString();
            $dailyByDate[$day] = match (strtoupper((string) $row->status)) {
                'H' => 'hadir',
                'S' => 'sakit',
                'I' => 'izin',
                default => 'alpa',
            };
        }

        $allDates = array_values(array_unique([
            ...array_keys($leaveDates),
            ...array_keys($sessionByDate),
            ...array_keys($dailyByDate),
        ]));
        sort($allDates);

        $counts = ['hadir' => 0, 'sakit' => 0, 'izin' => 0, 'alpa' => 0];
        $partialAbsenceDates = [];

        foreach ($allDates as $day) {
            if (isset($leaveDates[$day])) {
                $counts[$leaveDates[$day]]++;

                continue;
            }

            if (isset($sessionByDate[$day])) {
                $statuses = $sessionByDate[$day];
                $hasPresent = false;
                $hasMissing = false;
                $onlySakit = true;
                $onlyIzin = true;

                foreach ($statuses as $st) {
                    if (in_array($st, SessionAttendance::PRESENT_STATUSES, true)) {
                        $hasPresent = true;
                        $onlySakit = false;
                        $onlyIzin = false;
                    } else {
                        $hasMissing = true;
                        if ($st !== SessionAttendance::STATUS_SAKIT) {
                            $onlySakit = false;
                        }
                        if ($st !== SessionAttendance::STATUS_IZIN) {
                            $onlyIzin = false;
                        }
                    }
                }

                if ($hasPresent) {
                    $counts['hadir']++;
                    if ($hasMissing) {
                        $partialAbsenceDates[] = $day;
                    }
                } elseif ($onlySakit) {
                    $counts['sakit']++;
                } elseif ($onlyIzin) {
                    $counts['izin']++;
                } else {
                    $counts['alpa']++;
                }

                continue;
            }

            if (isset($dailyByDate[$day])) {
                $counts[$dailyByDate[$day]]++;
            }
        }

        return [
            'hadir' => $counts['hadir'],
            'sakit' => $counts['sakit'],
            'izin' => $counts['izin'],
            'alpa' => $counts['alpa'],
            'partial_absence_days' => count($partialAbsenceDates),
            'partial_absence_dates' => $partialAbsenceDates,
        ];
    }

    /**
     * Resolve subject score for a student in a given year and report type ('pts' or 'pas').
     * Returns null when no grade exists (so e-Rapor CSV exports blank, never 0).
     */
    public function resolveSubjectScore(Student $student, Subject $subject, AcademicYear $year, string $reportType = 'pas', ?ReportCardTemplate $template = null): ?float
    {
        if ($template && $student->user_id) {
            $entry = ReportCardEntry::query()
                ->where('report_card_id', $template->id)
                ->where('student_id', $student->user_id)
                ->where('subject_id', $subject->id)
                ->first();

            if ($entry !== null) {
                return (float) $entry->score;
            }
        }

        $grades = Grade::query()
            ->where('student_id', $student->id)
            ->where('subject_id', $subject->id)
            ->where('academic_year_id', $year->id)
            ->get()
            ->keyBy('type');

        if ($grades->isEmpty()) {
            return null;
        }

        if ($reportType === ReportCardTemplate::REPORT_TYPE_PTS) {
            return $grades->has('pts') ? (float) $grades->get('pts')->score : null;
        }

        if ($grades->has('pas')) {
            return (float) $grades->get('pas')->score;
        }

        return round((float) $grades->avg('score'), 2);
    }

    /**
     * Homeroom completeness check per classroom: subject scores, TP capaian readiness,
     * homeroom notes, and nearest 'batas_input_nilai' deadline from A0 calendar.
     *
     * @return array<string, mixed>
     */
    public function completeness(Classroom $classroom, ?AcademicYear $year = null, string $reportType = 'pas'): array
    {
        $year ??= AcademicYear::current();
        if (! $year) {
            return [
                'classroom' => $classroom,
                'academic_year' => null,
                'total_students' => 0,
                'subjects' => [],
                'notes_filled' => 0,
                'deadline' => null,
            ];
        }

        $students = Student::with('user')
            ->where('classroom_id', $classroom->id)
            ->get();
        $totalStudents = $students->count();

        $subjectIds = collect()
            ->merge($classroom->electiveSubjects()->pluck('subjects.id'))
            ->merge(Grade::whereIn('student_id', $students->pluck('id'))->where('academic_year_id', $year->id)->pluck('subject_id'))
            ->merge(ClassSession::where('classroom_id', $classroom->id)->where('academic_year_id', $year->id)->pluck('subject_id'))
            ->unique()
            ->values();

        $subjects = $subjectIds->isNotEmpty()
            ? Subject::whereIn('id', $subjectIds)->orderBy('name')->get()
            : collect();

        $subjectStatus = $subjects->map(function (Subject $subject) use ($students, $year, $reportType, $totalStudents) {
            $scoredCount = 0;
            $tpReadyCount = 0;

            foreach ($students as $student) {
                if ($this->resolveSubjectScore($student, $subject, $year, $reportType) !== null) {
                    $scoredCount++;
                }
                if ($this->generateTpCapaian($student, $subject, $year)['draft'] !== null) {
                    $tpReadyCount++;
                }
            }

            return [
                'subject_id' => $subject->id,
                'code' => $subject->code,
                'name' => $subject->name,
                'scored_count' => $scoredCount,
                'tp_ready_count' => $tpReadyCount,
                'total_students' => $totalStudents,
                'is_complete' => $totalStudents > 0 && $scoredCount >= $totalStudents,
            ];
        })->values()->all();

        $notesFilled = ReportCard::query()
            ->where('classroom_id', $classroom->id)
            ->where('academic_year_id', $year->id)
            ->whereNotNull('homeroom_notes')
            ->where('homeroom_notes', '!=', '')
            ->count();

        $deadline = null;
        if (Schema::hasTable('academic_events')) {
            $deadlineEvent = AcademicEvent::query()
                ->where('academic_year_id', $year->id)
                ->where('milestone', 'batas_input_nilai')
                ->whereDate('start_date', '>=', now()->toDateString())
                ->orderBy('start_date')
                ->first();
            if ($deadlineEvent) {
                $deadline = [
                    'title' => $deadlineEvent->title,
                    'date' => CarbonImmutable::parse($deadlineEvent->start_date)->toDateString(),
                ];
            }
        }

        return [
            'classroom' => $classroom,
            'academic_year' => $year,
            'total_students' => $totalStudents,
            'subjects' => $subjectStatus,
            'notes_filled' => $notesFilled,
            'deadline' => $deadline,
        ];
    }

    /**
     * Export classroom scores in Kemendikdasmen e-Rapor import CSV structure.
     * Missing scores remain blank ("") instead of 0.
     *
     * @return array{filename: string, headers: array<int, string>, rows: array<int, array<int, string>>}
     */
    public function exportERaporCsv(
        Classroom $classroom,
        AcademicYear $year,
        ?int $subjectId = null,
        string $reportType = 'pas',
        ?ReportCardTemplate $template = null,
    ): array {
        $students = Student::with('user')
            ->where('classroom_id', $classroom->id)
            ->join('users', 'students.user_id', '=', 'users.id')
            ->orderBy('users.name')
            ->select('students.*')
            ->get();

        $subjects = $subjectId
            ? Subject::where('id', $subjectId)->get()
            : Subject::orderBy('name')->whereIn('id', function ($q) use ($classroom, $students, $year) {
                $q->select('subject_id')->from('grades')
                    ->whereIn('student_id', $students->pluck('id'))
                    ->where('academic_year_id', $year->id)
                    ->union(
                        DB::table('classroom_subjects')->select('subject_id')->where('classroom_id', $classroom->id)
                    );
            })->get();

        if ($subjects->isEmpty() && $subjectId === null) {
            $subjects = Subject::orderBy('name')->get();
        }

        $gradeLevel = (string) ($classroom->grade_level ?? 'X');
        $phase = $this->inferPhase($gradeLevel);
        $semesterNumber = $template?->semester_number ?? $this->inferSemesterNumber($gradeLevel, (string) ($year->semester ?? 'ganjil'));
        $officials = $this->resolveOfficials($classroom, $template?->issued_date);

        $headers = [
            'NIS',
            'NISN',
            'Nama Siswa',
            'Rombel',
            'Fase',
            'Semester',
            'Jenis Rapor',
            'Kode Mapel',
            'Mata Pelajaran',
            'Nilai Akhir',
            'TP Terkuat',
            'TP Terlemah',
            'Draf Capaian Kompetensi',
            'Wali Kelas',
            'Kepala Sekolah',
        ];

        $rows = [];
        foreach ($subjects as $subject) {
            foreach ($students as $student) {
                $score = $this->resolveSubjectScore($student, $subject, $year, $reportType, $template);
                $tpData = $this->generateTpCapaian($student, $subject, $year);

                $rows[] = [
                    (string) ($student->nis ?? ''),
                    (string) ($student->nisn ?? ''),
                    (string) ($student->user?->name ?? ''),
                    (string) $classroom->name,
                    $phase,
                    (string) $semesterNumber,
                    strtoupper($reportType),
                    (string) ($subject->code ?? ''),
                    (string) $subject->name,
                    $score !== null ? (string) round($score, 2) : '',
                    (string) ($tpData['strongest_tp'] ?? ''),
                    (string) ($tpData['weakest_tp'] ?? ''),
                    (string) ($tpData['draft'] ?? ''),
                    (string) ($officials['wali_kelas']?->name ?? ''),
                    (string) ($officials['principal_name'] ?? ''),
                ];
            }
        }

        if ($template && in_array($template->status, [ReportCardTemplate::STATUS_NILAI_DISUSUN, ReportCardTemplate::STATUS_NILAI_LENGKAP], true)) {
            $template->update(['status' => ReportCardTemplate::STATUS_DIEKSPOR]);
        }

        $safeClass = preg_replace('/[^A-Za-z0-9_\-]/', '_', (string) $classroom->name);
        $filename = "ekspor_erapor_{$safeClass}_{$reportType}_smt{$semesterNumber}.csv";

        return [
            'filename' => $filename,
            'headers' => $headers,
            'rows' => $rows,
        ];
    }

    /**
     * Bulk upload official e-Rapor PDFs (direct multiple PDFs or a ZIP bundle).
     * Files are matched strictly by student NISN on the classroom roster and saved on
     * private 'local' storage so students only receive temporary signed URLs.
     *
     * @param  array<int, UploadedFile>  $uploadedFiles
     * @return array{
     *     matched: array<int, array{filename: string, nisn: string, student_id: int, pdf_id: int}>,
     *     unmatched: array<int, string>,
     *     skipped: array<int, string>,
     * }
     */
    public function uploadOfficialPdfs(
        ReportCardTemplate $template,
        Classroom $classroom,
        array $uploadedFiles,
        User $uploader,
    ): array {
        $students = Student::where('classroom_id', $classroom->id)
            ->whereNotNull('nisn')
            ->where('nisn', '!=', '')
            ->get(['id', 'user_id', 'nisn']);

        $nisnMap = [];
        foreach ($students as $student) {
            $cleanNisn = preg_replace('/\D+/', '', (string) $student->nisn);
            if ($cleanNisn !== '') {
                $nisnMap[$cleanNisn] = $student;
            }
        }

        $candidates = [];
        $skipped = [];

        foreach ($uploadedFiles as $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }

            $ext = strtolower($file->getClientOriginalExtension());
            $clientName = basename((string) $file->getClientOriginalName());

            if ($ext === 'zip') {
                $zip = new ZipArchive;
                $opened = $zip->open($file->getRealPath());
                if ($opened !== true) {
                    $skipped[] = $clientName;

                    continue;
                }

                try {
                    for ($i = 0; $i < $zip->numFiles; $i++) {
                        $entryName = (string) $zip->getNameIndex($i);
                        if ($entryName === '' || str_ends_with($entryName, '/') || str_contains($entryName, '..') || str_starts_with($entryName, '__MACOSX/')) {
                            continue;
                        }
                        if (strtolower(pathinfo($entryName, PATHINFO_EXTENSION)) !== 'pdf') {
                            $skipped[] = basename($entryName);

                            continue;
                        }
                        $contents = $zip->getFromIndex($i);
                        if (! is_string($contents) || $contents === '') {
                            $skipped[] = basename($entryName);

                            continue;
                        }
                        $candidates[] = [
                            'filename' => basename($entryName),
                            'bytes' => $contents,
                        ];
                    }
                } finally {
                    $zip->close();
                }

                continue;
            }

            if ($ext !== 'pdf') {
                $skipped[] = $clientName;

                continue;
            }

            $bytes = file_get_contents($file->getRealPath());
            if (! is_string($bytes) || $bytes === '') {
                $skipped[] = $clientName;

                continue;
            }

            $candidates[] = [
                'filename' => $clientName,
                'bytes' => $bytes,
            ];
        }

        $matched = [];
        $unmatched = [];

        foreach ($candidates as $candidate) {
            $student = $this->matchStudentByFilename($candidate['filename'], $nisnMap);
            if (! $student || ! $student->user_id) {
                $unmatched[] = $candidate['filename'];

                continue;
            }

            $relativePath = "erapor-pdfs/{$template->id}/{$student->user_id}.pdf";
            Storage::disk('local')->put($relativePath, $candidate['bytes']);

            $sha256 = hash('sha256', $candidate['bytes']);

            $pdf = ReportCardPdf::updateOrCreate(
                [
                    'report_card_id' => $template->id,
                    'student_id' => $student->user_id,
                ],
                [
                    'file_path' => $relativePath,
                    'sha256_hash' => $sha256,
                    'qr_verification_url' => url("/api/v1/rapor/verify/{$sha256}"),
                    'is_official' => true,
                    'original_filename' => $candidate['filename'],
                    'generated_at' => now(),
                    'generated_by' => $uploader->id,
                ]
            );

            $matched[] = [
                'filename' => $candidate['filename'],
                'nisn' => (string) $student->nisn,
                'student_id' => $student->id,
                'pdf_id' => $pdf->id,
            ];
        }

        if ($matched !== [] && $template->status !== ReportCardTemplate::STATUS_TERBIT) {
            $template->update(['status' => ReportCardTemplate::STATUS_RAPOR_RESMI_DIUNGGAH]);
        }

        return [
            'matched' => $matched,
            'unmatched' => $unmatched,
            'skipped' => $skipped,
        ];
    }

    /**
     * Bulk publish report card templates for a grade level / academic year batch.
     *
     * @return Collection<int, ReportCardTemplate>
     */
    public function publishBatch(
        AcademicYear $year,
        string $gradeLevel,
        string $reportType,
        int $semesterNumber,
        User $publisher,
        ?CarbonInterface $issuedDate = null,
    ): Collection {
        $grade = strtoupper(trim($gradeLevel));
        $phase = $this->inferPhase($grade);

        $templates = ReportCardTemplate::query()
            ->where('academic_year_id', $year->id)
            ->where('report_type', $reportType)
            ->where(function ($q) use ($grade, $phase) {
                $q->where('grade_level', $grade)
                    ->orWhere(fn ($sq) => $sq->whereNull('grade_level')->where('phase', $phase));
            })
            ->where(function ($q) use ($semesterNumber) {
                $q->where('semester_number', $semesterNumber)
                    ->orWhereNull('semester_number');
            })
            ->get();

        if ($templates->isEmpty()) {
            throw ValidationException::withMessages([
                'grade_level' => __('Tidak ada template rapor yang sesuai untuk angkatan :grade pada periode ini.', [
                    'grade' => $grade,
                ]),
            ]);
        }

        $now = now();
        $anchorDate = $issuedDate?->toDateString() ?? $now->toDateString();

        DB::transaction(function () use ($templates, $publisher, $now, $anchorDate, $grade, $semesterNumber) {
            foreach ($templates as $template) {
                $template->update([
                    'is_published' => true,
                    'status' => ReportCardTemplate::STATUS_TERBIT,
                    'grade_level' => $template->grade_level ?? $grade,
                    'semester_number' => $template->semester_number ?? $semesterNumber,
                    'issued_date' => $template->issued_date ?? $anchorDate,
                    'published_at' => $now,
                    'published_by' => $publisher->id,
                ]);
            }
        });

        return $templates->fresh();
    }

    private function capaianOpeningPhrase(float $score, float $aMin, float $bMin, float $cMin): string
    {
        if ($score >= $aMin) {
            return 'Menunjukkan penguasaan yang sangat baik';
        }
        if ($score >= $bMin) {
            return 'Menunjukkan penguasaan yang baik';
        }
        if ($score >= $cMin) {
            return 'Menunjukkan penguasaan yang cukup';
        }

        return 'Perlu bimbingan';
    }

    private function matchesCiriKhasName(string $nameLower, string $grade): bool
    {
        $list = $grade === 'X' ? self::PHASE_X_CIRI_KHAS_NAMES : self::PHASE_F_CIRI_KHAS_NAMES;
        foreach ($list as $needle) {
            if (str_contains($nameLower, $needle)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<string, Student>  $nisnMap
     */
    private function matchStudentByFilename(string $filename, array $nisnMap): ?Student
    {
        $base = pathinfo($filename, PATHINFO_FILENAME);

        preg_match_all('/\d+/', $base, $matches);
        $matchedStudents = [];
        foreach ($matches[0] ?? [] as $token) {
            if (isset($nisnMap[$token])) {
                $matchedStudents[$nisnMap[$token]->id] = $nisnMap[$token];
            }
        }

        if (count($matchedStudents) === 1) {
            return reset($matchedStudents);
        }

        $digits = preg_replace('/\D+/', '', $base);
        if ($digits !== '' && isset($nisnMap[$digits])) {
            return $nisnMap[$digits];
        }

        return null;
    }
}
