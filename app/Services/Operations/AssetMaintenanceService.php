<?php

namespace App\Services\Operations;

use App\Models\FacilityAsset;
use App\Models\MaintenanceLog;

class AssetMaintenanceService
{
    public function registerAsset(string $tag, string $name, string $category, string $location, float $cost = 0): FacilityAsset
    {
        return FacilityAsset::create([
            'asset_tag' => $tag,
            'name' => $name,
            'category' => $category,
            'location' => $location,
            'condition' => 'good',
            'cost' => $cost,
            'purchase_date' => now(),
        ]);
    }

    public function logMaintenance(int $assetId, int $technicianId, string $type, float $cost = 0, ?string $findings = null): MaintenanceLog
    {
        return MaintenanceLog::create([
            'asset_id' => $assetId,
            'technician_id' => $technicianId,
            'service_type' => $type,
            'findings' => $findings,
            'cost' => $cost,
            'service_date' => now(),
            'next_service_due' => now()->addMonths(6),
        ]);
    }
}
