<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EdsPeriod extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'academic_year_id',
        'status',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function assessments()
    {
        return $this->hasMany(EdsAssessment::class, 'period_id');
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['in_progress', 'completed']);
    }
}
