<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExtracurricularAchievement extends Model
{
    protected $fillable = [
        'extracurricular_id',
        'title',
        'level',
        'event_date',
        'organizer',
        'participants',
        'certificate_path',
        'photo_path',
        'notes',
    ];

    protected $casts = [
        'event_date' => 'date',
        'participants' => 'array',
    ];

    public function extracurricular()
    {
        return $this->belongsTo(Extracurricular::class);
    }
}
