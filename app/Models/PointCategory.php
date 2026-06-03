<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PointCategory extends Model
{
    protected $fillable = [
        'name',
        'type',
        'default_points',
        'is_active',
    ];

    public function logs()
    {
        return $this->hasMany(StudentPointLog::class);
    }

    /**
     * Scope: only active categories.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
