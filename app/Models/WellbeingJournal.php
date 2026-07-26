<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WellbeingJournal extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'student_id',
        'mood_level',
        'journal_entry',
        'activities',
        'sleep_hours',
        'is_private',
        'created_at',
    ];

    protected $casts = [
        'activities' => 'array',
        'sleep_hours' => 'float',
        'is_private' => 'boolean',
        'created_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
