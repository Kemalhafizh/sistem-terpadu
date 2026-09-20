<?php

namespace App\Models;

use App\Notifications\StudentPointNotification;
use App\Services\CounselingService;
use Illuminate\Database\Eloquent\Model;
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

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function category()
    {
        return $this->belongsTo(PointCategory::class, 'point_category_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    protected static function booted()
    {
        static::created(function ($pointLog) {
            $pointLog->loadMissing('student.parent');
            $student = $pointLog->student;
            if ($student && $student->parent) {
                $student->parent->notify(new StudentPointNotification($pointLog));
            }

            // Pemicu Sesi Konseling Wajib Otomatis jika akumulasi poin >= 50
            if ($student && $student->total_points >= 50) {
                try {
                    app(CounselingService::class)->createMandatorySession($student);
                } catch (\Exception $e) {
                    Log::error('Failed to trigger automatic counseling session: '.$e->getMessage());
                }
            }

            // Pemicu Auto-Generate SP
            if ($student) {
                $totalPoints = $student->total_points;
                
                $thresholds = [
                    100 => 'SP3',
                    75 => 'SP2',
                    50 => 'SP1',
                ];

                foreach ($thresholds as $threshold => $level) {
                    if ($totalPoints >= $threshold) {
                        // Check if SP already exists for this level
                        \App\Models\WarningLetter::firstOrCreate(
                            ['student_id' => $student->id, 'level' => $level],
                            [
                                'point_threshold' => $threshold,
                                'issued_at' => now(),
                                'notes' => "Penerbitan otomatis $level karena poin mencapai/melewati $threshold.",
                            ]
                        );
                        // Only trigger the highest applicable SP during this log event
                        break;
                    }
                }
            }
        });
    }
}
