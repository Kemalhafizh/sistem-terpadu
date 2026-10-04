<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\Achievement;
use App\Models\ExtracurricularMember;
use App\Models\Grade;
use App\Models\MutabaahLog;
use App\Models\ReportCard;
use App\Models\Student;
use App\Models\StudentPointLog;
use App\Models\TahfidzLog;
use App\Models\TahfidzTarget;
use App\Models\UksVisit;
use App\Models\User;
use App\Models\WarningLetter;

/**
 * Every method here used to ignore $studentId entirely and return the same
 * hardcoded numbers ("Ahmad Faiz Abdullah", 88.4, etc.) for every single
 * student in the school — the imports above (Grade, MutabaahLog, TahfidzLog,
 * ...) were already there, just never actually queried. Real aggregation
 * only. A figure with nothing behind it (no grades yet, no tahfidz target,
 * no prayer logged) is null, never 0: the app shows "–" for it. A user
 * with no student record is not profiled at all (the controller answers 404).
 */
class StudentProfile360Service
{
    public function resolveStudent(int $userId): ?Student
    {
        return Student::with(['classroom', 'healthRecord'])->where('user_id', $userId)->first();
    }

    public function getComprehensiveProfile(int $studentId): array
    {
        $user = User::find($studentId);
        $student = $this->resolveStudent($studentId);

        return [
            'biodata' => [
                'id' => $user->id ?? $studentId,
                'name' => $user?->name,
                'nisn' => $student->nisn ?? null,
                'class_name' => $student?->classroom?->name ?? null,
                'avatar_url' => null,
                'email' => $user->email ?? null,
            ],
            'akademik_summary' => $this->getAcademicSummary($studentId),
            'ibadah_summary' => $this->getIbadahSummary($studentId),
            'disiplin_summary' => $this->getDisciplineSummary($studentId),
            'ekskul_list' => $this->getExtracurriculars($studentId),
            'prestasi_list' => $this->getAchievements($studentId),
            'kesehatan_summary' => $this->getHealthSummary($studentId),
        ];
    }

    public function getAcademicSummary(int $studentId): array
    {
        $student = $this->resolveStudent($studentId);
        if (! $student) {
            return [
                'average_score' => null, 'rank_in_class' => null, 'total_class_students' => null,
                'strongest_subject' => null, 'improvement_needed' => null, 'trend' => [],
            ];
        }

        $currentYear = AcademicYear::current();
        $grades = Grade::where('student_id', $student->id)
            ->when($currentYear, fn ($q) => $q->where('academic_year_id', $currentYear->id))
            ->with('subject')
            ->get();

        $averageScore = $grades->isNotEmpty() ? round((float) $grades->avg('score'), 1) : null;

        $classSize = null;
        $rank = null;
        if ($student->classroom) {
            $classmateIds = $student->classroom->students()->pluck('id');
            $classSize = $classmateIds->count();
            if ($averageScore !== null) {
                $classmateAverages = Grade::whereIn('student_id', $classmateIds)
                    ->when($currentYear, fn ($q) => $q->where('academic_year_id', $currentYear->id))
                    ->selectRaw('student_id, AVG(score) as avg_score')
                    ->groupBy('student_id')
                    ->pluck('avg_score', 'student_id');
                $rank = $classmateAverages->filter(fn ($avg) => (float) $avg > $averageScore)->count() + 1;
            }
        }

        $bySubject = $grades->groupBy('subject_id')->filter(fn ($g) => $g->first()->subject !== null)
            ->map(fn ($g) => ['name' => $g->first()->subject->name, 'avg' => $g->avg('score')]);
        $strongest = $bySubject->sortByDesc('avg')->first();
        $weakest = $bySubject->sortBy('avg')->first();

        $trend = ReportCard::where('student_id', $student->id)
            ->orderBy('academic_year_id')
            ->pluck('average_score')
            ->map(fn ($v) => round((float) $v, 1))
            ->values()
            ->toArray();

        return [
            'average_score' => $averageScore,
            'rank_in_class' => $rank,
            'total_class_students' => $classSize,
            'strongest_subject' => $strongest['name'] ?? null,
            'improvement_needed' => $weakest['name'] ?? null,
            'trend' => $trend,
        ];
    }

    public function getIbadahSummary(int $studentId): array
    {
        $student = $this->resolveStudent($studentId);
        if (! $student) {
            return [
                'tahfidz_juz_completed' => null, 'target_juz' => null, 'tahfidz_progress_percent' => null,
                'current_surah' => null, 'mutabaah_weekly_streak' => null, 'sholat_jamaah_percent' => null,
            ];
        }

        $currentYear = AcademicYear::current();
        $target = $currentYear
            ? TahfidzTarget::where('student_id', $student->id)->where('academic_year_id', $currentYear->id)->first()
            : null;
        // No target set for this year: "0 dari 0 juz" would be made up.
        $achievedJuz = $target ? (int) $target->achieved_juz : null;
        $targetJuz = $target && (int) $target->target_juz > 0 ? (int) $target->target_juz : null;
        $progress = $targetJuz !== null ? min(100, (int) round(($achievedJuz / $targetJuz) * 100)) : null;

        $latestLog = TahfidzLog::where('student_id', $student->id)->latest('date')->first();

        // Consecutive real Mutaba'ah log days counting back from today.
        $streak = 0;
        $cursor = now()->startOfDay();
        while (MutabaahLog::where('student_id', $student->id)->whereDate('date', $cursor->toDateString())->exists()) {
            $streak++;
            $cursor = $cursor->copy()->subDay();
        }

        $recentLogs = MutabaahLog::where('student_id', $student->id)->orderByDesc('date')->limit(30)->get();
        $sholatFields = ['sholat_subuh', 'sholat_dzuhur', 'sholat_ashar', 'sholat_maghrib', 'sholat_isya'];
        $totalRecorded = 0;
        $jamaahCount = 0;
        foreach ($recentLogs as $log) {
            foreach ($sholatFields as $field) {
                $value = (int) $log->{$field};
                if ($value > 0) {
                    $totalRecorded++;
                    if ($value === 2) {
                        $jamaahCount++;
                    }
                }
            }
        }
        $jamaahPercent = $totalRecorded > 0 ? round(($jamaahCount / $totalRecorded) * 100, 1) : null;

        return [
            'tahfidz_juz_completed' => $achievedJuz,
            'target_juz' => $targetJuz,
            'tahfidz_progress_percent' => $progress,
            'current_surah' => $latestLog->surah_name ?? null,
            'mutabaah_weekly_streak' => $streak,
            'sholat_jamaah_percent' => $jamaahPercent,
        ];
    }

    /**
     * Mirrors DisciplineService::getSummary()'s exact thresholds/labels so
     * this profile screen and the dedicated Discipline screen never
     * disagree about the same student's status.
     */
    public function getDisciplineSummary(int $studentId): array
    {
        $student = $this->resolveStudent($studentId);
        if (! $student) {
            return [
                'total_positive_points' => null, 'total_violation_points' => null, 'net_points' => null,
                'category' => null, 'active_sanctions' => null,
            ];
        }

        $logs = StudentPointLog::where('student_id', $student->id)->get();
        $positive = (int) $logs->where('points', '>', 0)->sum('points');
        $violation = (int) abs($logs->where('points', '<', 0)->sum('points'));
        $net = $student->total_points;

        $status = 'Aman';
        if ($net >= 100) {
            $status = 'SP3 (Sangat Kritis)';
        } elseif ($net >= 75) {
            $status = 'SP2 (Kritis)';
        } elseif ($net >= 50) {
            $status = 'SP1 (Waspada)';
        } elseif ($net >= 30) {
            $status = 'Perlu Perhatian';
        }

        $activeSanctions = WarningLetter::where('student_id', $student->id)->whereNull('signed_at')->count();

        return [
            'total_positive_points' => $positive,
            'total_violation_points' => $violation,
            'net_points' => $net,
            'category' => $status,
            'active_sanctions' => $activeSanctions,
        ];
    }

    public function getExtracurriculars(int $studentId): array
    {
        $student = $this->resolveStudent($studentId);
        if (! $student) {
            return [];
        }

        return ExtracurricularMember::where('student_id', $student->id)
            ->with('extracurricular')
            ->get()
            ->filter(fn ($m) => $m->extracurricular !== null)
            ->map(fn ($m) => [
                'name' => $m->extracurricular->name,
                'role' => $m->role ?: 'Anggota',
                'joined_year' => $m->joined_at ? $m->joined_at->format('Y') : null,
            ])
            ->values()
            ->toArray();
    }

    public function getAchievements(int $studentId): array
    {
        $student = $this->resolveStudent($studentId);
        if (! $student) {
            return [];
        }

        return Achievement::where('student_id', $student->id)
            ->validated()
            ->get()
            ->map(fn ($a) => [
                'title' => $a->title,
                'level' => $a->level,
                'year' => $a->event_date?->year,
                'category' => $a->field,
            ])
            ->values()
            ->toArray();
    }

    public function getHealthSummary(int $studentId): array
    {
        $student = $this->resolveStudent($studentId);
        if (! $student) {
            return [
                'blood_type' => null, 'height_cm' => null, 'weight_kg' => null,
                'allergies' => [], 'total_uks_visits' => null, 'last_visit_date' => null,
            ];
        }

        $record = $student->healthRecord;
        $lastVisit = UksVisit::where('student_id', $student->id)->latest('visit_time')->first();

        return [
            'blood_type' => $record->blood_type ?? $student->blood_type ?? null,
            'height_cm' => $record?->height_cm !== null ? (float) $record->height_cm : null,
            'weight_kg' => $record?->weight_kg !== null ? (float) $record->weight_kg : null,
            'allergies' => $record->allergies ?? ($student->allergies ? array_map('trim', explode(',', $student->allergies)) : []),
            'total_uks_visits' => UksVisit::where('student_id', $student->id)->count(),
            'last_visit_date' => $lastVisit?->visit_time?->format('Y-m-d'),
        ];
    }
}
