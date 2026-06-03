<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcademicYear extends Model
{
    use \App\Traits\HasUuid, HasFactory;

    protected $fillable = [
        'year',
        'semester',
        'is_active',
        'start_date',
        'end_date',
    ];

    /**
     * Scope for active academic year.
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * Static helper to get the currently active academic year.
     */
    public static function current(): ?self
    {
        return self::where('is_active', true)->first();
    }

    /**
     * Helper to activate this period and deactivate all others.
     */
    public function activate(): void
    {
        self::query()->update(['is_active' => false]);
        $this->update(['is_active' => true]);
    }

    /**
     * Accessor for name (alias for year) to support existing views.
     */
    public function getNameAttribute(): string
    {
        return $this->year;
    }
}
