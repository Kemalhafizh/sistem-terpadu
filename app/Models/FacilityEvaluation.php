<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FacilityEvaluation extends Model
{
    protected $fillable = [
        'facility_asset_id',
        'user_id',
        'rating',
        'comments',
    ];

    public function facilityAsset()
    {
        return $this->belongsTo(FacilityAsset::class);
    }
}
