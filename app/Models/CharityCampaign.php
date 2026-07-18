<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CharityCampaign extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'type',
        'description',
        'target_amount',
        'collected_amount',
        'end_date',
        'is_active',
    ];

    protected $casts = [
        'target_amount' => 'decimal:2',
        'collected_amount' => 'decimal:2',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function donations()
    {
        return $this->hasMany(CharityDonation::class);
    }

    /**
     * Scope query to active campaigns.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('end_date')
                  ->orWhere('end_date', '>=', now()->toDateString());
            });
    }

    /**
     * Get percentage achieved.
     */
    public function getPercentageAchievedAttribute()
    {
        if (! $this->target_amount || $this->target_amount <= 0) {
            return 0;
        }

        $percentage = ($this->collected_amount / $this->target_amount) * 100;
        return (float) min(100.00, round($percentage, 2));
    }
}
