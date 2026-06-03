<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class ElearningClass extends Model
{
    use HasUuid;

    protected $fillable = ['uuid', 'subject_id', 'classroom_id', 'teacher_id', 'name', 'description'];

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function materials()
    {
        return $this->hasMany(ElearningMaterial::class);
    }

    public function assignments()
    {
        return $this->hasMany(ElearningAssignment::class);
    }
}
