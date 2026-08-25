<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AiTutorSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'subject_name',
        'topic',
        'difficulty_level',
        'learning_objective',
        'status',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(AiTutorMessage::class, 'session_id');
    }
}
