<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GraduationDecision extends Model
{
    use LogsActivity;

    public const DECISION_GRADUATED = 'lulus';

    public const DECISION_NOT_GRADUATED = 'tidak_lulus';

    protected $fillable = [
        'graduation_batch_id',
        'student_id',
        'classroom_id',
        'decision',
        'basis',
        'note',
        'decided_by',
    ];

    protected function casts(): array
    {
        return [
            'basis' => 'array',
        ];
    }

    /** @return BelongsTo<GraduationBatch, $this> */
    public function batch(): BelongsTo
    {
        return $this->belongsTo(GraduationBatch::class, 'graduation_batch_id');
    }

    /** @return BelongsTo<Student, $this> */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /** @return BelongsTo<Classroom, $this> */
    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    /** @return BelongsTo<User, $this> */
    public function decider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by');
    }
}
