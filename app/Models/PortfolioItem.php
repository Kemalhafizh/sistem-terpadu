<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PortfolioItem extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'student_id',
        'title',
        'description',
        'category',
        'date',
        'file_path',
        'external_link',
        'is_verified',
        'verified_by',
        'verified_at',
        'is_public',
    ];

    protected $casts = [
        'date' => 'date',
        'is_verified' => 'boolean',
        'is_public' => 'boolean',
        'verified_at' => 'datetime',
    ];

    /**
     * Get the student who owns this portfolio item.
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the user who verified this portfolio item.
     */
    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Scope query to only include verified items.
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    /**
     * Scope query to only include public items.
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }
}
