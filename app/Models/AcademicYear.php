<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

class AcademicYear extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'school_year_id',
        'year',
        'semester',
        'is_active',
        'start_date',
        'end_date',
    ];

    /** @return BelongsTo<SchoolYear, $this> */
    public function schoolYear(): BelongsTo
    {
        return $this->belongsTo(SchoolYear::class);
    }

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
        if (Cache::has('academic_year_current')) {
            $year = Cache::get('academic_year_current');

            if ($year instanceof self || is_null($year)) {
                return $year;
            }

            // Cache is corrupt (e.g. __PHP_Incomplete_Class)
            Cache::forget('academic_year_current');
        }

        $year = self::where('is_active', true)->first();

        Cache::forever('academic_year_current', $year);

        return $year;
    }

    /**
     * Helper to activate this period and deactivate all others.
     */
    public function activate(): void
    {
        self::query()->update(['is_active' => false]);
        $this->update(['is_active' => true]);

        Cache::forget('academic_year_current');
    }

    /**
     * Accessor for name (alias for year) to support existing views.
     */
    public function getNameAttribute(): string
    {
        return $this->year;
    }
}
