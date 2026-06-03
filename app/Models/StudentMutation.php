<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentMutation extends Model
{
    protected $fillable = ['student_id', 'type', 'mutation_date', 'reason', 'target_school', 'document_path'];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
