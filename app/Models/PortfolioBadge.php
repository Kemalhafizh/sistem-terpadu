<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PortfolioBadge extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'icon', 'criteria', 'color'];

    /**
     * Get students who earned this badge.
     */
    public function students()
    {
        return $this->belongsToMany(Student::class, 'student_badges', 'badge_id', 'student_id')
            ->withPivot('awarded_by', 'earned_at', 'notes')
            ->withTimestamps();
    }
}
