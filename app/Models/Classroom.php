<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Classroom extends Model
{
    use \App\Traits\HasUuid, HasFactory;

    protected $fillable = ['name', 'grade_level', 'major', 'teacher_id', 'academic_year'];

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function students()
    {
        return $this->hasMany(Student::class);
    }

    /**
     * Get all classrooms, cached forever.
     */
    public static function allCached()
    {
        return \Illuminate\Support\Facades\Cache::rememberForever('classrooms_all', function () {
            return self::orderBy('name')->get();
        });
    }

    protected static function booted()
    {
        static::saved(function () {
            \Illuminate\Support\Facades\Cache::forget('classrooms_all');
        });
        static::deleted(function () {
            \Illuminate\Support\Facades\Cache::forget('classrooms_all');
        });
    }
}
