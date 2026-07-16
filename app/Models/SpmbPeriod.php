<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpmbPeriod extends Model
{
    use \App\Traits\HasUuid, HasFactory;

    protected $fillable = [
        'academic_year_id',
        'name',
        'total_quota',
        'status',
    ];

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function waves()
    {
        return $this->hasMany(SpmbWave::class);
    }

    public function tracks()
    {
        return $this->hasMany(SpmbTrack::class);
    }
}
