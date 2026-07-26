<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ScholarshipProgram extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'source',
        'academic_year',
        'total_quota',
        'amount_per_student',
        'eligibility_criteria',
        'application_start_date',
        'application_end_date',
        'status',
        'created_by',
    ];

    protected $casts = [
        'eligibility_criteria' => 'array',
        'amount_per_student' => 'decimal:2',
        'application_start_date' => 'date',
        'application_end_date' => 'date',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(ScholarshipApplication::class);
    }
}
