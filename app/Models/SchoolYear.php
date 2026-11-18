<?php

namespace App\Models;

use App\Traits\HasUuid;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SchoolYear extends Model
{
    use HasUuid, LogsActivity;

    public const STATUS_DRAFT = 'draf';

    public const STATUS_ACTIVE = 'aktif';

    public const STATUS_CLOSED = 'ditutup';

    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    /** @return HasMany<AcademicYear, $this> */
    public function semesters(): HasMany
    {
        return $this->hasMany(AcademicYear::class, 'school_year_id');
    }

    /** @return HasOne<AcademicCalendar, $this> */
    public function calendar(): HasOne
    {
        return $this->hasOne(AcademicCalendar::class, 'school_year_id');
    }

    /** @return HasMany<Classroom, $this> */
    public function classrooms(): HasMany
    {
        return $this->hasMany(Classroom::class, 'school_year_id');
    }

    /** @return HasMany<ClassEnrollment, $this> */
    public function enrollments(): HasMany
    {
        return $this->hasMany(ClassEnrollment::class, 'school_year_id');
    }

    public static function active(): ?self
    {
        return self::query()->where('status', self::STATUS_ACTIVE)->first();
    }
}
