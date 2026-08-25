<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduleBuilderDraft extends Model
{
    use HasFactory;

    protected $table = 'schedule_builder_drafts';

    protected $fillable = [
        'academic_year_id',
        'name',
        'schedule_data',
        'conflict_reports',
        'status',
        'created_by',
    ];

    protected $casts = [
        'schedule_data' => 'array',
        'conflict_reports' => 'array',
    ];

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
