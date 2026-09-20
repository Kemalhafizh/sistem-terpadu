<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WarningLetter extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'level',
        'point_threshold',
        'issued_at',
        'signed_at',
        'signed_by_parent_id',
        'digital_signature',
        'notes',
    ];

    protected $casts = [
        'issued_at' => 'date',
        'signed_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'signed_by_parent_id');
    }
}
