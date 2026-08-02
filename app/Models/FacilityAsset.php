<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function maintenanceLogs(): HasMany
    {
        return $this->hasMany(MaintenanceLog::class, 'asset_id');
    }
}
