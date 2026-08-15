<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ZoneBreach extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id', 'zone_name', 'breach_type', 'breach_time',
        'detected_by', 'is_acknowledged', 'security_action_taken'
    ];

    protected $casts = [
        'breach_time' => 'datetime',
        'is_acknowledged' => 'boolean',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
