<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentXpLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'xp_amount',
        'source',
        'description',
    ];

    protected $casts = [
        'xp_amount' => 'integer',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
