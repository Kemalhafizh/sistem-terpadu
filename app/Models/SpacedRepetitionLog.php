<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpacedRepetitionLog extends Model
{
    use HasFactory;

    protected $fillable = ['student_id', 'memory_card_id', 'repetition_count', 'easiness_factor', 'interval_days', 'next_review_date'];

    protected $casts = [
        'next_review_date' => 'date',
        'easiness_factor' => 'decimal:2',
    ];

    public function card()
    {
        return $this->belongsTo(MemoryCard::class, 'memory_card_id');
    }
}
