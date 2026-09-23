<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class TahsinSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'student_id',
        'teacher_id',
        'surah_name',
        'start_ayah',
        'end_ayah',
        'audio_path',
        'duration_seconds',
        'status',
        'reviewed_at',
    ];

    protected $casts = [
        'start_ayah' => 'integer',
        'end_ayah' => 'integer',
        'duration_seconds' => 'integer',
        'reviewed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (TahsinSubmission $submission) {
            $submission->uuid = $submission->uuid ?: (string) Str::uuid();
        });
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function annotations()
    {
        return $this->hasMany(TahsinAnnotation::class)->orderBy('timestamp_seconds');
    }
}
