<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentBadge extends Model
{
    use HasFactory;

    protected $table = 'micro_student_badges';

    protected $fillable = ['student_id', 'micro_badge_id', 'awarded_at', 'awarded_by', 'evidence_url'];

    protected $casts = [
        'awarded_at' => 'date',
    ];

    public function badge()
    {
        return $this->belongsTo(MicroBadge::class, 'micro_badge_id');
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
