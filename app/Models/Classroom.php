<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class Classroom extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = ['name', 'grade_level', 'major', 'teacher_id', 'academic_year'];

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /** @return HasMany<Student, $this> */
    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    /**
     * Get all classrooms, cached forever.
     */
    public static function allCached()
    {
        return Cache::rememberForever('classrooms_all', function () {
            return self::orderBy('name')->get();
        });
    }

    protected static function booted()
    {
        static::saved(function () {
            Cache::forget('classrooms_all');
        });
        static::deleted(function () {
            Cache::forget('classrooms_all');
        });
    }
}
