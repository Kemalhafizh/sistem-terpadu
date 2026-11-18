<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PromotionDecision extends Model
{
    use LogsActivity;

    protected $fillable = [
        'promotion_batch_id',
        'student_id',
        'from_classroom_id',
        'decision',
        'to_classroom_id',
        'basis',
        'homeroom_note',
        'decided_by',
    ];

    protected function casts(): array
    {
        return [
            'basis' => 'array',
        ];
    }

    /** @return BelongsTo<PromotionBatch, $this> */
    public function batch(): BelongsTo
    {
        return $this->belongsTo(PromotionBatch::class, 'promotion_batch_id');
    }

    /** @return BelongsTo<Student, $this> */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /** @return BelongsTo<Classroom, $this> */
    public function fromClassroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class, 'from_classroom_id');
    }

    /** @return BelongsTo<Classroom, $this> */
    public function toClassroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class, 'to_classroom_id');
    }
}
