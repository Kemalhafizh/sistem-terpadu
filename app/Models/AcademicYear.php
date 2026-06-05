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
        if (\Illuminate\Support\Facades\Cache::has('academic_year_current')) {
            $year = \Illuminate\Support\Facades\Cache::get('academic_year_current');

            if ($year instanceof self || is_null($year)) {
                return $year;
            }

            // Cache is corrupt (e.g. __PHP_Incomplete_Class)
            \Illuminate\Support\Facades\Cache::forget('academic_year_current');
        }

        $year = self::where('is_active', true)->first();

        \Illuminate\Support\Facades\Cache::forever('academic_year_current', $year);

        return $year;
    }

    /**
     * Helper to activate this period and deactivate all others.
     */
    public function activate(): void
    {
        self::query()->update(['is_active' => false]);
        $this->update(['is_active' => true]);
        
        \Illuminate\Support\Facades\Cache::forget('academic_year_current');
    }

    /**
     * Accessor for name (alias for year) to support existing views.
     */
    public function getNameAttribute(): string
    {
        return $this->year;
    }
}
