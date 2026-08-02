<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MentorProfile extends Model
{
    use HasFactory;

    protected $table = 'mentor_profiles';

    protected $fillable = [
        'alumni_id',
        'expertise_areas',
        'max_mentees',
        'is_available',
        'bio',
    ];

    protected $casts = [
        'expertise_areas' => 'array',
        'is_available' => 'boolean',
    ];

    public function alumni(): BelongsTo
    {
        return $this->belongsTo(User::class, 'alumni_id');
    }

    public function pairs(): HasMany
    {
        return $this->hasMany(MentorshipPair::class, 'mentor_profile_id');
    }
}
