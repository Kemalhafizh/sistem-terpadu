<?php

namespace App\Services;

use App\Models\PointCategory;
use App\Models\Setting;
use App\Models\Student;
use App\Models\StudentPointLog;
use App\Models\User;
use App\Models\WarningLetter;
use App\Notifications\WarningLetterIssuedNotification;
use App\Support\Access\StudentDataAccess;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DisciplineService
{
    /**
     * Get discipline summary using school-configured SP thresholds & stages (`ALUR_KERJA.md` E1).
     */
    public function getSummary(Student $student)
    {
        $logs = $student->pointLogs()->get(['points', 'type', 'created_at']);

        $totalPoints = (int) $logs->sum(fn (StudentPointLog $log) => match ($log->type) {
            'reward' => -abs((int) $log->points),
            'penalty' => abs((int) $log->points),
            default => (int) $log->points,
        });

        $thresholds = WarningLetter::thresholds();
        $status = WarningLetter::statusForPoints($totalPoints);

        $penalties = $logs->where('type', 'penalty');
        $nextLevel = collect($thresholds)
            ->filter(fn (int $threshold) => $totalPoints < $threshold)
            ->sort()
            ->keys()
            ->first();

        return [
            'total_points' => $totalPoints,
            'status' => $status,
            'total_violation_points' => (int) $penalties->sum(fn (StudentPointLog $log) => abs((int) $log->points)),
            'total_reward_points' => (int) $logs->where('type', 'reward')->sum(fn (StudentPointLog $log) => abs((int) $log->points)),
            'total_cases' => $penalties->count(),
            // Null once every level has been reached.
            'next_warning_level' => $nextLevel,
            'next_warning_threshold' => $nextLevel !== null ? $thresholds[$nextLevel] : null,
            'thresholds' => $thresholds,
            'attention_threshold' => WarningLetter::attentionThreshold(),
            'mandatory_counseling_threshold' => WarningLetter::mandatoryCounselingThreshold(),
            'last_updated' => $logs->max('created_at')?->format('Y-m-d H:i:s'),
            // The school's real rule book.
            'rules' => PointCategory::active()
                ->orderBy('type')
                ->orderBy('name')
                ->get(['name', 'type', 'default_points'])
                ->map(fn ($category) => [
                    'name' => $category->name,
                    'type' => $category->type,
                    'points' => abs((int) $category->default_points),
                ])
                ->values(),
        ];
    }

    /**
     * Update school-configurable SP thresholds & mandatory counseling trigger in `settings`.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function updateThresholds(array $data): array
    {
        return DB::transaction(function () use ($data) {
            Setting::set('discipline_attention_threshold', (string) (int) $data['attention_threshold'], 'discipline', 'number');
            Setting::set('discipline_sp1_threshold', (string) (int) $data['sp1_threshold'], 'discipline', 'number');
            Setting::set('discipline_sp2_threshold', (string) (int) $data['sp2_threshold'], 'discipline', 'number');
            Setting::set('discipline_sp3_threshold', (string) (int) $data['sp3_threshold'], 'discipline', 'number');
            Setting::set(
                'discipline_mandatory_counseling_threshold',
                (string) (int) ($data['mandatory_counseling_threshold'] ?? $data['sp1_threshold']),
                'discipline',
                'number'
            );

            if (isset($data['stages']) && is_array($data['stages']) && $data['stages'] !== []) {
                Setting::set('discipline_warning_stages', (string) json_encode(array_values($data['stages'])), 'discipline', 'json');
            } else {
                // Clear custom JSON stages when standard SP1/SP2/SP3 fields are updated directly.
                Setting::where('key', 'discipline_warning_stages')->delete();
                Cache::forget('setting_discipline_warning_stages');
            }

            return [
                'attention_threshold' => WarningLetter::attentionThreshold(),
                'thresholds' => WarningLetter::thresholds(),
                'mandatory_counseling_threshold' => WarningLetter::mandatoryCounselingThreshold(),
            ];
        });
    }

    /**
     * Manually issue or update a warning letter by Waka Kesiswaan / BK, scheduling
     * mandatory counseling (E2) and notifying parents & homeroom staff.
     */
    public function issueWarningLetter(
        Student $student,
        User $issuer,
        string $level,
        ?string $notes = null,
    ): WarningLetter {
        return DB::transaction(function () use ($student, $issuer, $level, $notes) {
            $thresholds = WarningLetter::thresholds();
            $normalizedLevel = strtoupper(trim($level));
            $threshold = $thresholds[$normalizedLevel] ?? $student->total_points;

            $counselingSession = app(CounselingService::class)->createMandatorySession($student, $issuer->id);

            $letter = WarningLetter::updateOrCreate(
                [
                    'student_id' => $student->id,
                    'level' => $normalizedLevel,
                ],
                [
                    'point_threshold' => $threshold,
                    'issued_at' => now(),
                    'issued_by' => $issuer->id,
                    'notes' => $notes ?? "Surat Peringatan {$normalizedLevel} diterbitkan oleh {$issuer->name}.",
                    'counseling_session_id' => $counselingSession?->id,
                ]
            );

            $this->dispatchWarningLetterNotifications($letter, $student->total_points);

            return $letter->refresh();
        });
    }

    /**
     * Notify parent (in-app + WA) and internal stakeholders (Waka Kesiswaan & Wali Kelas)
     * when a WarningLetter has not yet notified parents.
     */
    public function dispatchWarningLetterNotifications(WarningLetter $letter, int $currentPoints): void
    {
        if ($letter->parent_notified_at !== null) {
            return;
        }

        $letter->loadMissing(['student.user', 'student.parent', 'student.classroom.teacher']);
        $student = $letter->student;
        if (! $student) {
            return;
        }

        $notification = new WarningLetterIssuedNotification($letter, $currentPoints);

        if ($student->parent) {
            $student->parent->notify($notification);
        }

        $notifiedStaffIds = [];
        $homeroomTeacher = $student->classroom?->teacher;
        if ($homeroomTeacher) {
            $homeroomTeacher->notify($notification);
            $notifiedStaffIds[] = $homeroomTeacher->id;
        }

        $kesiswaanUsers = User::query()
            ->where('role', 'waka_kesiswaan')
            ->orWhereHas('positionAssignments', function ($q) {
                $q->activeOn()->whereHas('position', fn ($pq) => $pq->where('code', 'waka_kesiswaan'));
            })
            ->get();

        foreach ($kesiswaanUsers as $staff) {
            if (! in_array($staff->id, $notifiedStaffIds, true)) {
                $staff->notify($notification);
                $notifiedStaffIds[] = $staff->id;
            }
        }

        $letter->update(['parent_notified_at' => now()]);
    }

    /**
     * Get discipline history logs.
     */
    public function getHistory(Student $student, $paginate = true)
    {
        $query = StudentPointLog::with('category', 'creator:id,name')
            ->where('student_id', $student->id)
            ->orderBy('created_at', 'desc');

        $transform = fn ($log) => [
            'id' => $log->id,
            'points' => $log->points,
            'type' => $log->type, // penalty, reward, adjustment
            'category_name' => $log->category?->name,
            'description' => $log->description,
            'date' => $log->created_at->format('Y-m-d H:i:s'),
            'recorded_by' => $log->creator?->name ?? 'Sistem',
        ];

        if ($paginate) {
            $logs = $query->paginate(15);
            $logs->getCollection()->transform($transform);

            return $logs;
        }

        return $query->get()->map($transform)->values();
    }

    /**
     * Get all warning letters for a student.
     */
    public function getWarningLetters(Student $student, $viewer = null)
    {
        $viewerIsThisParent = $viewer instanceof User
            && array_intersect($viewer->effectiveRoles(), StudentDataAccess::PARENT_ROLES) !== []
            && (int) $student->parent_id === (int) $viewer->id;

        return WarningLetter::where('student_id', $student->id)
            ->orderBy('level', 'desc')
            ->get()
            ->map(function (WarningLetter $letter) use ($viewerIsThisParent) {
                return [
                    'id' => $letter->id,
                    'level' => $letter->level,
                    'point_threshold' => $letter->point_threshold,
                    'issued_at' => $letter->issued_at->format('Y-m-d'),
                    'is_signed' => ! is_null($letter->signed_at),
                    'signed_at' => $letter->signed_at ? $letter->signed_at->format('Y-m-d H:i:s') : null,
                    'notes' => $letter->notes,
                    'counseling_session_id' => $letter->counseling_session_id,
                    'can_sign' => $viewerIsThisParent && is_null($letter->signed_at),
                ];
            })
            ->values();
    }

    /**
     * Sign a warning letter (Digital Signature by Parent).
     */
    public function signWarningLetter($letterId, $user, $signatureString)
    {
        $letter = WarningLetter::findOrFail($letterId);

        if (! ($user instanceof User) || array_intersect($user->effectiveRoles(), StudentDataAccess::PARENT_ROLES) === []) {
            throw new Exception('Akses ditolak. Hanya orang tua/wali yang dapat menandatangani surat peringatan.');
        }

        $student = Student::find($letter->student_id);
        if (! $student || (int) $student->parent_id !== (int) $user->id) {
            throw new Exception('Otorisasi gagal. Anda bukan orang tua yang terdaftar untuk siswa ini.');
        }

        if ($letter->signed_at) {
            throw new Exception('Surat peringatan ini sudah ditandatangani sebelumnya.');
        }

        if (empty($signatureString)) {
            throw new Exception('Tanda tangan digital tidak boleh kosong.');
        }

        $letter->update([
            'signed_at' => now(),
            'signed_by_parent_id' => $user->id,
            'digital_signature' => $signatureString,
        ]);

        return clone $letter;
    }
}
