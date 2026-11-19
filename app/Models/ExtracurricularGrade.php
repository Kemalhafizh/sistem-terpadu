<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExtracurricularGrade extends Model
{
    protected $fillable = [
        'extracurricular_member_id',
        'academic_year_id',
        'grade',
        'description',
    ];

    /** @return BelongsTo<ExtracurricularMember, $this> */
    public function member(): BelongsTo
    {
        return $this->belongsTo(ExtracurricularMember::class, 'extracurricular_member_id');
    }

    /** @return BelongsTo<AcademicYear, $this> */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }
}
