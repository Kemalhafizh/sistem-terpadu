<?php

declare(strict_types=1);

namespace App\Services\Mobile;

use App\Models\AcademicEvent;
use App\Models\Announcement;
use App\Models\Attendance;
use App\Models\Billing;
use App\Models\Grade;
use App\Models\MobileSyncLog;
use App\Models\Student;
use App\Models\TahfidzRecord;
use App\Models\User;
use Carbon\Carbon;

/**
 * Class MobileSyncService
 *
 * Aggregates delta modifications for client-side SQLite/Room offline caching.
 * Compiles updates across school announcements, calendar, student academic grades,
 * daily attendances, tuition fees (SPP), and Qur'an tahfidz logs.
 */
class MobileSyncService
{
    /**
     * Pull delta changes since a given timestamp.
     *
     * @return array<string, mixed>
     */
    public function pullDelta(User $user, ?string $sinceTimestamp = null, string $deviceId = 'default'): array
    {
        $since = $sinceTimestamp ? Carbon::parse($sinceTimestamp) : now()->subDays(30);
        $recordsCount = 0;
        $deltaData = [];

        // 1. General School Announcements Delta
        $announcements = Announcement::where('updated_at', '>=', $since)
            ->orderBy('updated_at', 'desc')
            ->take(50)
            ->get()
            ->map(fn (Announcement $a) => [
                'id' => $a->id,
                'title' => $a->title,
                'content' => strip_tags((string) $a->content),
                'category' => $a->category ?? 'umum',
                'updated_at' => $a->updated_at->toIso8601String(),
            ]);
        $deltaData['announcements'] = $announcements;
        $recordsCount += $announcements->count();

        // 2. Academic Calendar Events Delta
        $calendar = AcademicEvent::where('updated_at', '>=', $since)
            ->orderBy('start_date', 'asc')
            ->take(50)
            ->get()
            ->map(fn (AcademicEvent $c) => [
                'id' => $c->id,
                'title' => $c->title,
                'event_date' => $c->start_date?->toIso8601String(),
                'is_holiday' => $c->event_type === 'holiday',
                'updated_at' => $c->updated_at->toIso8601String(),
            ]);
        $deltaData['calendar'] = $calendar;
        $recordsCount += $calendar->count();

        // 3. Role-Scoped Academic & Student Delta
        if ($user->role === 'student') {
            $student = Student::where('user_id', $user->id)->first();
            if ($student) {
                $studentDelta = $this->getStudentDelta($student, $since);
                $deltaData['student_data'] = $studentDelta;
                $recordsCount += ($studentDelta['total_records'] ?? 0);
            }
        } elseif ($user->role === 'parent') {
            $students = Student::where('parent_user_id', $user->id)->get();
            $childrenDelta = [];
            foreach ($students as $student) {
                $sDelta = $this->getStudentDelta($student, $since);
                $childrenDelta[] = [
                    'student_id' => $student->id,
                    'student_name' => $student->nama_lengkap ?? $student->name,
                    'delta' => $sDelta,
                ];
                $recordsCount += ($sDelta['total_records'] ?? 0);
            }
            $deltaData['children_data'] = $childrenDelta;
        }

        // Log Mobile Sync Activity
        MobileSyncLog::create([
            'user_id' => $user->id,
            'device_id' => $deviceId,
            'sync_type' => 'delta_pull',
            'records_synced' => $recordsCount,
            'synced_at' => now(),
        ]);

        return [
            'success' => true,
            'synced_at' => now()->toIso8601String(),
            'records_synced' => $recordsCount,
            'delta' => $deltaData,
        ];
    }

    /**
     * Get delta changes specific to a student entity.
     *
     * @return array<string, mixed>
     */
    public function getStudentDelta(Student $student, Carbon $since): array
    {
        $total = 0;

        // Academic Grades
        $grades = Grade::where('student_id', $student->id)
            ->where('updated_at', '>=', $since)
            ->get()
            ->map(fn ($g) => [
                'id' => $g->id,
                'subject_name' => $g->subject->name ?? 'Mata Pelajaran',
                'type' => $g->type ?? 'UH',
                'score' => $g->score,
                'updated_at' => $g->updated_at->toIso8601String(),
            ]);
        $total += $grades->count();

        // Attendances
        $attendances = Attendance::where('student_id', $student->id)
            ->where('updated_at', '>=', $since)
            ->get()
            ->map(fn ($att) => [
                'id' => $att->id,
                'date' => $att->date,
                'status' => $att->status,
                'notes' => $att->notes,
                'updated_at' => $att->updated_at->toIso8601String(),
            ]);
        $total += $attendances->count();

        // Financial Tuition Bills (SPP)
        $bills = collect([]);
        if (class_exists(Billing::class)) {
            $bills = Billing::where('student_id', $student->id)
                ->where('updated_at', '>=', $since)
                ->get()
                ->map(fn ($b) => [
                    'id' => $b->id,
                    'bill_code' => $b->bill_code ?? $b->code ?? 'SPP-001',
                    'title' => $b->title ?? 'Tagihan SPP',
                    'amount' => (float) $b->amount,
                    'status' => $b->status,
                    'due_date' => $b->due_date,
                    'updated_at' => $b->updated_at->toIso8601String(),
                ]);
        }
        $total += $bills->count();

        // Qur'an Tahfidz Progress Records
        $tahfidz = TahfidzRecord::where('student_id', $student->id)
            ->where('updated_at', '>=', $since)
            ->get()
            ->map(fn ($t) => [
                'id' => $t->id,
                'surah_name' => $t->surah_name,
                'start_ayah' => $t->start_ayah,
                'end_ayah' => $t->end_ayah,
                'assessment_date' => $t->assessment_date,
                'fluency_score' => $t->fluency_score,
                'updated_at' => $t->updated_at->toIso8601String(),
            ]);
        $total += $tahfidz->count();

        return [
            'grades' => $grades,
            'attendances' => $attendances,
            'bills' => $bills,
            'tahfidz' => $tahfidz,
            'total_records' => $total,
        ];
    }
}
