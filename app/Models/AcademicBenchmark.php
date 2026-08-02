<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AcademicBenchmark extends Model
{
    use HasFactory;

    protected $table = 'academic_benchmarks';

    protected $fillable = [
        'subject_id',
        'exam_type',
        'school_average',
        'national_average',
        'percentile_rank',
        'year',
    ];

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }
}
