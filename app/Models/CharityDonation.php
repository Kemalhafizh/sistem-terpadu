<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class CharityDonation extends Model
{
    use HasFactory;

    protected $fillable = [
        'charity_campaign_id',
        'user_id',
        'donor_name',
        'amount',
        'payment_method',
        'status',
        'evidence_path',
        'is_anonymous',
        'receipt_number',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_anonymous' => 'boolean',
    ];

    public function campaign()
    {
        return $this->belongsTo(CharityCampaign::class, 'charity_campaign_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get evidence image URL.
     */
    public function getEvidenceUrlAttribute()
    {
        if ($this->evidence_path) {
            return Storage::url($this->evidence_path);
        }
        return null;
    }
}
