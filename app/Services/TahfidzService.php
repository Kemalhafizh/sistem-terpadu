<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\Student;
use App\Models\TahfidzLog;
use App\Models\TahfidzTarget;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TahfidzService
{
    /**
     * Get target, logs, and calculated metrics for a student.
     */
    public function getStudentProgress(Student $student, ?AcademicYear $academicYear = null): array
    {
        $academicYear = $academicYear ?? AcademicYear::current();
        if (! $academicYear) {
            return [
                'target' => null,
                'logs' => collect(),
                'progress_percent' => 0,
                'time_elapsed_percent' => 0,
            ];
        }

        $target = TahfidzTarget::where('student_id', $student->id)
            ->where('academic_year_id', $academicYear->id)
            ->first();

        $logs = $student->tahfidzLogs()
            ->whereBetween('date', [$academicYear->start_date, $academicYear->end_date])
            ->with('teacher')
            ->latest('date')
            ->latest('id')
            ->get();

        // Calculate time elapsed percentage
        $now = Carbon::now();
        $start = Carbon::parse($academicYear->start_date);
        $end = Carbon::parse($academicYear->end_date);

        if ($now->lt($start)) {
            $timeElapsed = 0;
        } elseif ($now->gt($end)) {
            $timeElapsed = 100;
        } else {
            $totalDays = $start->diffInDays($end) ?: 1;
            $elapsedDays = $start->diffInDays($now);
            $timeElapsed = round(($elapsedDays / $totalDays) * 100, 1);
        }

        // Calculate progress percentage
        $progressPercent = 0;
        if ($target) {
            if ($target->target_pages > 0) {
                $progressPercent = round(($target->achieved_pages / $target->target_pages) * 100, 1);
            } elseif ($target->target_juz > 0) {
                $progressPercent = round(($target->achieved_juz / $target->target_juz) * 100, 1);
            }
        }

        return [
            'target' => $target,
            'logs' => $logs,
            'progress_percent' => min($progressPercent, 100),
            'time_elapsed_percent' => $timeElapsed,
        ];
    }

    /**
     * Submit a setoran and recalculate the target achievement.
     */
    public function submitSetoran(Student $student, array $data): TahfidzLog
    {
        return DB::transaction(function () use ($student, $data) {
            $academicYear = AcademicYear::current();

            // Create setoran log
            $log = TahfidzLog::create([
                'student_id' => $student->id,
                'teacher_id' => $data['teacher_id'] ?? auth()->id(),
                'surah_number' => $data['surah_number'],
                'surah_name' => $data['surah_name'],
                'from_ayat' => $data['from_ayat'],
                'to_ayat' => $data['to_ayat'],
                'type' => $data['type'] ?? 'ziyadah',
                'grade' => $data['grade'] ?? 'A',
                'notes' => $data['notes'] ?? null,
                'date' => $data['date'] ?? Carbon::today()->toDateString(),
                'juz' => $data['juz'] ?? null,
                'pages_count' => $data['pages_count'] ?? 0,
            ]);

            // Calculate Grade XP
            // Recalculate target achievements
            if ($academicYear) {
                $this->updateTargetAchievement($student, $academicYear);
            }

            return $log;
        });
    }

    /**
     * Recalculate achievements and status for a student.
     */
    public function updateTargetAchievement(Student $student, ?AcademicYear $academicYear = null): ?TahfidzTarget
    {
        $academicYear = $academicYear ?? AcademicYear::current();
        if (! $academicYear) {
            return null;
        }

        $target = TahfidzTarget::where('student_id', $student->id)
            ->where('academic_year_id', $academicYear->id)
            ->first();

        if (! $target) {
            return null;
        }

        // Sum ziyadah pages and distinct juz memorized in this academic year range
        $achievedPages = $student->tahfidzLogs()
            ->whereBetween('date', [$academicYear->start_date, $academicYear->end_date])
            ->where('type', 'ziyadah')
            ->sum('pages_count');

        $achievedJuz = $student->tahfidzLogs()
            ->whereBetween('date', [$academicYear->start_date, $academicYear->end_date])
            ->where('type', 'ziyadah')
            ->whereNotNull('juz')
            ->distinct('juz')
            ->count('juz');

        // Determine status based on time elapsed
        $now = Carbon::now();
        $start = Carbon::parse($academicYear->start_date);
        $end = Carbon::parse($academicYear->end_date);

        if ($now->lt($start)) {
            $percentageElapsed = 0;
        } elseif ($now->gt($end)) {
            $percentageElapsed = 1.0;
        } else {
            $totalDays = $start->diffInDays($end) ?: 1;
            $elapsedDays = $start->diffInDays($now);
            $percentageElapsed = $elapsedDays / $totalDays;
        }

        $newStatus = 'on_track';

        // Check completion first
        if ($target->target_pages > 0) {
            if ($achievedPages >= $target->target_pages) {
                $newStatus = $achievedPages > $target->target_pages ? 'exceeded' : 'completed';
            } else {
                $progressPercent = $achievedPages / $target->target_pages;
                $newStatus = ($progressPercent >= $percentageElapsed) ? 'on_track' : 'behind';
            }
        } elseif ($target->target_juz > 0) {
            if ($achievedJuz >= $target->target_juz) {
                $newStatus = $achievedJuz > $target->target_juz ? 'exceeded' : 'completed';
            } else {
                $progressPercent = $achievedJuz / $target->target_juz;
                $newStatus = ($progressPercent >= $percentageElapsed) ? 'on_track' : 'behind';
            }
        }

        $target->update([
            'achieved_pages' => $achievedPages,
            'achieved_juz' => $achievedJuz,
            'status' => $newStatus,
        ]);

        return $target;
    }

    /**
     * Get class progress summary metrics.
     */
    public function getClassProgressSummary(?int $classroomId = null): array
    {
        $academicYear = AcademicYear::current();
        if (! $academicYear) {
            return [
                'status_distribution' => ['on_track' => 0, 'behind' => 0, 'exceeded' => 0, 'completed' => 0],
                'average_completion_percent' => 0,
                'total_setoran_count' => 0,
            ];
        }

        $query = TahfidzTarget::where('academic_year_id', $academicYear->id);

        if ($classroomId) {
            $query->whereHas('student', function ($q) use ($classroomId) {
                $q->where('classroom_id', $classroomId);
            });
        }

        $targets = $query->get();

        $statusDistribution = [
            'on_track' => 0,
            'behind' => 0,
            'exceeded' => 0,
            'completed' => 0,
        ];

        $totalCompletionPercent = 0;
        $count = $targets->count();

        foreach ($targets as $target) {
            $statusDistribution[$target->status] = ($statusDistribution[$target->status] ?? 0) + 1;

            $completion = 0;
            if ($target->target_pages > 0) {
                $completion = ($target->achieved_pages / $target->target_pages) * 100;
            } elseif ($target->target_juz > 0) {
                $completion = ($target->achieved_juz / $target->target_juz) * 100;
            }
            $totalCompletionPercent += min($completion, 100);
        }

        $avgCompletion = $count > 0 ? round($totalCompletionPercent / $count, 1) : 0;

        $logsQuery = TahfidzLog::whereBetween('date', [$academicYear->start_date, $academicYear->end_date]);
        if ($classroomId) {
            $logsQuery->whereHas('student', function ($q) use ($classroomId) {
                $q->where('classroom_id', $classroomId);
            });
        }
        $totalSetoran = $logsQuery->count();

        return [
            'status_distribution' => $statusDistribution,
            'average_completion_percent' => $avgCompletion,
            'total_setoran_count' => $totalSetoran,
        ];
    }

    /**
     * Get ranked Leaderboard.
     */
    public function getLeaderboard(?int $classroomId = null, int $limit = 10)
    {
        $academicYear = AcademicYear::current();
        if (! $academicYear) {
            return collect();
        }

        $query = Student::with(['user', 'classroom'])
            ->withCount(['tahfidzLogs' => function ($q) use ($academicYear) {
                $q->whereBetween('date', [$academicYear->start_date, $academicYear->end_date]);
            }])
            ->withSum(['tahfidzLogs as total_pages_memorized' => function ($q) use ($academicYear) {
                $q->whereBetween('date', [$academicYear->start_date, $academicYear->end_date])
                    ->where('type', 'ziyadah');
            }], 'pages_count')
            ->whereHas('tahfidzTargets', function ($q) use ($academicYear) {
                $q->where('academic_year_id', $academicYear->id);
            });

        if ($classroomId) {
            $query->where('classroom_id', $classroomId);
        }

        return $query->get()
            ->map(function ($student) use ($academicYear) {
                $target = TahfidzTarget::where('student_id', $student->id)
                    ->where('academic_year_id', $academicYear->id)
                    ->first();
                $student->tahfidz_target = $target;

                return $student;
            })
            ->sortByDesc(function ($student) {
                return $student->total_pages_memorized ?? 0;
            })
            ->take($limit)
            ->values();
    }
}
