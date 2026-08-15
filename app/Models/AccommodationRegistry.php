<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccommodationRegistry extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'accommodation_category',
        'specific_need',
        'is_active',
        'applicable_contexts',
        'provision_date',
        'notes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'applicable_contexts' => 'array',
        'provision_date' => 'date',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
