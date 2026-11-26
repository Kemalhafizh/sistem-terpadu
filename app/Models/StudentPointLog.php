<?php

namespace App\Models;

use App\Notifications\StudentPointNotification;
use App\Services\CounselingService;
use App\Services\DisciplineService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;

class StudentPointLog extends Model
{
    protected $fillable = [
        'student_id',
        'point_category_id',
        'created_by',
        'points',
        'type',
        'description',
    ];

    /** @return BelongsTo<Student, $this> */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /** @return BelongsTo<PointCategory, $this> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(PointCategory::class, 'point_category_id');
    }

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    protected static function booted()
    {
        static::created(function (self $pointLog) {
            $pointLog->loadMissing('student.parent');
            $student = $pointLog->student;
            if (! $student) {
                return;
            }

            // Flush any cached pointLogs relation so total_points reflects this new entry.
            $student->unsetRelation('pointLogs');

            if ($student->parent) {
                $student->parent->notify(new StudentPointNotification($pointLog));
            }

            $totalPoints = $student->total_points;
            $mandatorySession = null;

            // Pemicu Sesi Konseling Wajib Otomatis (E2) berdasarkan ambang di Pengaturan
            if ($totalPoints >= WarningLetter::mandatoryCounselingThreshold()) {
                try {
                    $mandatorySession = app(CounselingService::class)->createMandatorySession(
                        $student,
                        $pointLog->created_by ? (int) $pointLog->created_by : null
                    );
                } catch (\Exception $e) {
                    Log::error('Failed to trigger automatic counseling session: '.$e->getMessage());
                }
            }

            // Pemicu Auto-Generate SP berdasarkan ambang & tahapan di Pengaturan (E1)
            $descendingThresholds = WarningLetter::thresholds();
            arsort($descendingThresholds);

            foreach ($descendingThresholds as $level => $threshold) {
                if ($totalPoints >= $threshold) {
                    $letter = WarningLetter::firstOrCreate(
                        ['student_id' => $student->id, 'level' => $level],
                        [
                            'point_threshold' => $threshold,
                            'issued_at' => now(),
                            'issued_by' => $pointLog->created_by,
                            'notes' => "Penerbitan otomatis {$level} karena poin mencapai/melewati {$threshold}.",
                            'counseling_session_id' => $mandatorySession?->id,
                        ]
                    );

                    if ($mandatorySession && $letter->counseling_session_id === null) {
                        $letter->update(['counseling_session_id' => $mandatorySession->id]);
                    }

                    app(DisciplineService::class)->dispatchWarningLetterNotifications($letter, $totalPoints);

                    // Only trigger the highest applicable SP during this log event
                    break;
                }
            }
        });
    }
}
