<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentHealthRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'blood_type',
        'allergies',
        'chronic_conditions',
        'medications',
        'medical_notes',
        'emergency_contact_name',
        'emergency_contact_phone',
        'insurance_number',
        'height_cm',
        'weight_kg',
        'vision_status',
    ];

    protected $casts = [
        'allergies' => 'array',
        'chronic_conditions' => 'array',
        'medications' => 'array',
        'height_cm' => 'decimal:1',
        'weight_kg' => 'decimal:1',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
