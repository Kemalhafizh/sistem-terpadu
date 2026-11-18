<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacilityAsset extends Model
{
    use HasFactory;

    protected $table = 'facility_assets';

    protected $fillable = [
        'asset_tag',
        'name',
        'category',
        'location',
        'condition',
        'purchase_date',
        'cost',
    ];

    protected $casts = [
        'purchase_date' => 'date',
    ];
}
