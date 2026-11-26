<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\Achievement;
use App\Models\Attendance;
use App\Models\ClassEnrollment;
use App\Models\Employee;
use App\Models\ExtracurricularGrade;
use App\Models\Grade;
use App\Models\PositionAssignment;
use App\Models\ReportCard;
use App\Models\Student;
use App\Models\StudentMutation;

class StudentLedgerService
{
    /**
     * Susun Buku Induk Siswa lengkap sesuai format administrasi sekolah:
     * identitas siswa & wali, riwayat rombel per TA, mutasi, rekap presensi,
     * nilai rapor & mata pelajaran per semester, ekstrakurikuler, prestasi
     * tervalidasi, dan kelulusan/alumni.
     *
     * @return array<string, mixed>
     */
    public function build(Student $student): array
    {
        $student->loadMissing([
            'user',
            'parent',
            'classroom.teacher',
            'alumniData.tracerResponse',
        ]);

        $enrollments = ClassEnrollment::with(['schoolYear', 'classroom.teacher'])
            ->where('student_id', $student->id)
            ->orderBy('started_on')
            ->orderBy('id')
            ->get();

        $mutations = StudentMutation::where('student_id', $student->id)
            ->where('status', StudentMutation::STATUS_APPROVED)
            ->orderBy('mutation_date')
            ->orderBy('id')
            ->get();

        $reportCards = ReportCard::with(['academicYear', 'classroom'])
            ->where('student_id', $student->id)
            ->get()
            ->sortBy(fn (ReportCard $rc) => sprintf(
                '%s-%s',
                $rc->academicYear?->year ?? '0000',
                strtolower((string) ($rc->academicYear?->semester ?? '')) === 'genap' ? '2' : '1'
            ))
            ->values();

        $gradesByYear = Grade::with(['subject', 'academicYear'])
            ->where('student_id', $student->id)
            ->get()
            ->groupBy('academic_year_id');

        $academicYearIds = $reportCards->pluck('academic_year_id')
            ->merge($gradesByYear->keys())
            ->filter()
            ->unique()
            ->values();

        $academicYears = AcademicYear::whereIn('id', $academicYearIds)
            ->get()
            ->keyBy('id');

        $semesterSummaries = $academicYearIds
            ->map(function ($yearId) use ($academicYears, $reportCards, $gradesByYear) {
                $year = $academicYears->get($yearId);
                $reportCard = $reportCards->firstWhere('academic_year_id', $yearId);
                $grades = $gradesByYear->get($yearId, collect());

                $subjectRows = $grades->groupBy('subject_id')
                    ->map(function ($subjectGrades) {
                        $subject = $subjectGrades->first()?->subject;
                        $category = match (strtolower(trim((string) $subject?->category))) {
                            'umum', 'nasional' => 'Kelompok A (Umum)',
                            'muatan_lokal', 'lokal' => 'Kelompok A (Muatan Lokal)',
                            'pilihan', 'kejuruan' => 'Kelompok B (Pilihan)',
                            'ciri_khas', 'ciri khas' => 'Kelompok C (Ciri Khas)',
                            default => $subject?->category ? ucfirst((string) $subject->category) : '-',
                        };

                        return [
                            'subject_name' => $subject?->name ?? 'Mata Pelajaran',
                            'subject_code' => $subject?->code ?? '-',
                            'group' => $category,
                            'kkm' => $subject?->kkm ?? 75,
                            'average_score' => round((float) $subjectGrades->avg('score'), 2),
                            'components' => $subjectGrades->map(fn (Grade $g) => [
                                'type' => $g->type,
                                'score' => (float) $g->score,
                            ])->values(),
                        ];
                    })
                    ->sortBy(fn (array $row) => $row['group'].'-'.$row['subject_name'])
                    ->values();

                return [
                    'academic_year' => $year,
                    'report_card' => $reportCard,
                    'subjects' => $subjectRows,
                ];
            })
            ->sortBy(fn (array $entry) => sprintf(
                '%s-%s',
                $entry['academic_year']?->year ?? '0000',
                strtolower((string) ($entry['academic_year']?->semester ?? '')) === 'genap' ? '2' : '1'
            ))
            ->values();

        $extracurriculars = ExtracurricularGrade::with(['member.extracurricular', 'academicYear'])
            ->whereHas('member', fn ($q) => $q->where('student_id', $student->id))
            ->get()
            ->sortBy('academic_year_id')
            ->values();

        $achievements = Achievement::query()
            ->where('student_id', $student->id)
            ->validated()
            ->orderBy('event_date')
            ->get();

        $attendanceRaw = Attendance::query()
            ->where('student_id', $student->id)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $attendanceSummary = [
            'H' => (int) ($attendanceRaw['H'] ?? 0),
            'S' => (int) ($attendanceRaw['S'] ?? 0),
            'I' => (int) ($attendanceRaw['I'] ?? 0),
            'A' => (int) ($attendanceRaw['A'] ?? 0),
        ];

        $principalHolder = PositionAssignment::holderOn('kepala_sekolah');
        $principalNip = $principalHolder
            ? (Employee::where('user_id', $principalHolder->id)->value('nip') ?: school_principal_nip())
            : school_principal_nip();
        $principalName = $principalHolder?->name ?: school_principal_name();

        return [
            'student' => $student,
            'enrollments' => $enrollments,
            'mutations' => $mutations,
            'reportCards' => $reportCards,
            'semesterSummaries' => $semesterSummaries,
            'extracurriculars' => $extracurriculars,
            'achievements' => $achievements,
            'attendanceSummary' => $attendanceSummary,
            'alumni' => $student->alumniData,
            'principal' => [
                'name' => signature_name($principalName),
                'raw_name' => $principalName,
                'nip' => filled($principalNip) ? $principalNip : null,
            ],
            'printedAt' => now(),
        ];
    }
}
