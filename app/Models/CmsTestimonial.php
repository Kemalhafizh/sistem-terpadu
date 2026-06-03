<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CmsTestimonial extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'role',
        'content',
        'avatar_path',
        'is_active',
        'order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    /**
     * Scope active testimonials ordered.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('order', 'asc');
    }
}
