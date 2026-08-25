<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiTutorMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'sender',
        'message',
        'socratic_scaffold_type',
        'comprehension_score',
    ];

    protected $casts = [
        'comprehension_score' => 'float',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(AiTutorSession::class, 'session_id');
    }
}
