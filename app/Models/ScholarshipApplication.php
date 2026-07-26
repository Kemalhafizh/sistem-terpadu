<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ScholarshipApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'scholarship_program_id',
        'student_id',
        'application_data',
        'supporting_documents',
        'gpa_score',
        'family_income',
        'recommendation_letter_path',
        'status',
        'reviewed_by',
        'review_notes',
        'disbursed_at',
    ];

    protected $casts = [
        'application_data' => 'array',
        'supporting_documents' => 'array',
        'gpa_score' => 'decimal:2',
        'family_income' => 'decimal:2',
        'disbursed_at' => 'datetime',
    ];

    public function program(): BelongsTo
    {
        return $this->belongsTo(ScholarshipProgram::class, 'scholarship_program_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function disbursement(): HasOne
    {
        return $this->hasOne(ScholarshipDisbursement::class);
    }
}
