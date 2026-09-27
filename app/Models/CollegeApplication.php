<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CollegeApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id', 'university_catalog_id', 'admission_type', 'status', 'counselor_id',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function catalog()
    {
        return $this->belongsTo(UniversityCatalog::class, 'university_catalog_id');
    }

    public function counselor()
    {
        return $this->belongsTo(User::class, 'counselor_id');
    }
}
