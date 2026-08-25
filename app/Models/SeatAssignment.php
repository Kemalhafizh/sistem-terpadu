<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeatAssignment extends Model
{
    use HasFactory;

    protected $table = 'seat_assignments';

    protected $fillable = [
        'classroom_layout_id',
        'student_id',
        'grid_row',
        'grid_column',
        'behavior_note',
    ];

    protected $casts = [
        'grid_row' => 'integer',
        'grid_column' => 'integer',
    ];

    public function classroomLayout(): BelongsTo
    {
        return $this->belongsTo(ClassroomLayout::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
