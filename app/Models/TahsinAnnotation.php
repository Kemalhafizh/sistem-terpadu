<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TahsinAnnotation extends Model
{
    protected $fillable = [
        'tahsin_submission_id',
        'teacher_id',
        'timestamp_seconds',
        'note',
        'tajwid_category',
    ];

    protected $casts = [
        'timestamp_seconds' => 'integer',
    ];

    public function submission()
    {
        return $this->belongsTo(TahsinSubmission::class, 'tahsin_submission_id');
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }
}
