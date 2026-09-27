<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentAspiration extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'title',
        'body',
        'category',
        'status',
        'is_anonymous',
        'student_id',
        'resolution_notes',
        'resolved_by',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function resolver()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }
}
