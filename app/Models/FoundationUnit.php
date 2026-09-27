<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FoundationUnit extends Model
{
    use HasFactory;

    protected $fillable = ['unit_code', 'unit_name', 'level', 'address', 'principal_name', 'student_count', 'teacher_count', 'annual_budget', 'annual_revenue', 'is_flagship'];

    protected $casts = ['is_flagship' => 'boolean', 'annual_budget' => 'decimal:2', 'annual_revenue' => 'decimal:2'];
}
