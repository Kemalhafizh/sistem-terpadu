<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PtnProgram extends Model
{
    use HasFactory;

    protected $fillable = [
        'university_name',
        'program_name',
        'cluster',
        'passing_grade',
        'capacity',
    ];

    protected $casts = [
        'passing_grade' => 'float',
    ];
}
