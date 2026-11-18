<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassEnrollment extends Model
{
    use LogsActivity;

    public const STATUS_ACTIVE = 'aktif';

    public const STATUS_PROMOTED = 'naik';

    public const STATUS_RETAINED = 'tinggal';

    public const STATUS_GRADUATED = 'lulus';

    public const STATUS_MUTATED_OUT = 'mutasi_keluar';

    public const STATUS_LEFT = 'keluar';

    public const STATUS_TRANSFERRED_CLASS = 'pindah_rombel';

    protected $fillable = [
        'student_id',
        'classroom_id',
        'school_year_id',
        'status',
        'started_on',
        'ended_on',
        'ended_reason',
        'decided_by',
        'promotion_decision_id',
    ];

    protected function casts(): array
    {
        return [
            'started_on' => 'date',
            'ended_on' => 'date',
        ];
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

    /** @return BelongsTo<SchoolYear, $this> */
    public function schoolYear(): BelongsTo
    {
        return $this->belongsTo(SchoolYear::class);
    }

    /** @return BelongsTo<User, $this> */
    public function decider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by');
    }
}
