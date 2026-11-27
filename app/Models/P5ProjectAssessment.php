<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class P5ProjectAssessment extends Model
{
    use LogsActivity;

    /**
     * Official 4-level Kemendikdasmen Kurikulum Merdeka P5 predicates.
     *
     * @var array<string, string>
     */
    public const PREDICATES = [
        'MB' => 'Mulai Berkembang',
        'SB' => 'Sedang Berkembang',
        'BSH' => 'Berkembang Sesuai Harapan',
        'SAB' => 'Sangat Berkembang',
    ];

    protected $fillable = [
        'p5_project_id',
        'p5_project_dimension_id',
        'student_id',
        'classroom_id',
        'assessor_id',
        'predicate',
    ];

    public static function predicateLabel(?string $predicate): ?string
    {
        if ($predicate === null || $predicate === '') {
            return null;
        }

        $key = strtoupper(trim($predicate));

        return self::PREDICATES[$key] ?? null;
    }

    /** @return BelongsTo<P5Project, $this> */
    public function project(): BelongsTo
    {
        return $this->belongsTo(P5Project::class, 'p5_project_id');
    }

    /** @return BelongsTo<P5ProjectDimension, $this> */
    public function projectDimension(): BelongsTo
    {
        return $this->belongsTo(P5ProjectDimension::class, 'p5_project_dimension_id');
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
    public function assessor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assessor_id');
    }
}
