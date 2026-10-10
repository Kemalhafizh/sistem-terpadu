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
use App\Models\TahfidzLog;
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
    private const STUDENT_ROLES = ['student', 'siswa'];

    private const PARENT_ROLES = ['parent', 'orang_tua'];

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

        // 1. General School Announcements Delta — same audience rule as
        // ApiGeneralController::announcements(), via Announcement::visibleTo().
        // (This and the calendar block used to sit behind class_exists() on
        // unimported names, which never resolved, so both were silently
        // missing from every delta.)
        $announcements = Announcement::visibleTo($user)
            ->where('updated_at', '>=', $since)
            ->reorder('updated_at', 'desc')
            ->take(50)
            ->get()
            ->map(fn ($a) => [
                'id' => $a->id,
                'title' => $a->title,
                'content' => strip_tags((string) $a->content),
                'category' => $a->category ?? 'general',
                'updated_at' => $a->updated_at->toIso8601String(),
            ]);
        $deltaData['announcements'] = $announcements;
        $recordsCount += $announcements->count();

        // 2. Academic Calendar Events Delta
        $calendar = AcademicEvent::visibleTo($user)
            ->where('updated_at', '>=', $since)
            ->orderBy('start_date', 'asc')
            ->take(50)
            ->get()
            ->map(fn ($c) => [
                'id' => $c->id,
                'title' => $c->title,
                'event_date' => $c->start_date?->toDateString(),
                'event_type' => $c->event_type,
                'is_holiday' => $c->event_type === 'holiday',
                'updated_at' => $c->updated_at->toIso8601String(),
            ]);
        $deltaData['calendar'] = $calendar;
        $recordsCount += $calendar->count();

        // 3. Role-Scoped Academic & Student Delta
        if (in_array($user->role, self::STUDENT_ROLES, true)) {
            $student = Student::where('user_id', $user->id)->first();
            if ($student) {
                $studentDelta = $this->getStudentDelta($student, $since);
                $deltaData['student_data'] = $studentDelta;
                $recordsCount += ($studentDelta['total_records'] ?? 0);
            }
        } elseif (in_array($user->role, self::PARENT_ROLES, true)) {
            $students = Student::with('user')->where('parent_id', $user->id)->get();
            $childrenDelta = [];
            foreach ($students as $student) {
                $sDelta = $this->getStudentDelta($student, $since);
                $childrenDelta[] = [
                    'student_id' => $student->id,
                    'student_name' => $student->user?->name,
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
        // Keys follow tahfidz_logs (same names as the app's other tahfidz models).
        $tahfidz = TahfidzLog::where('student_id', $student->id)
            ->where('updated_at', '>=', $since)
            ->get()
            ->map(fn ($t) => [
                'id' => $t->id,
                'surah_number' => $t->surah_number,
                'surah_name' => $t->surah_name,
                'from_ayat' => $t->from_ayat,
                'to_ayat' => $t->to_ayat,
                'type' => $t->type,
                'grade' => $t->grade,
                'date' => $t->date?->toDateString(),
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
