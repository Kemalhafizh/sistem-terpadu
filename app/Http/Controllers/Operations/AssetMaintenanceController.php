<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
use App\Models\FacilityAsset;
use App\Models\MaintenanceLog;
use Illuminate\Http\Request;

class AssetMaintenanceController extends Controller
{
    public function schedule()
    {
        $assets = FacilityAsset::with('maintenanceLogs')->latest()->get();
        return view('facility.maintenance.schedule', compact('assets'));
    }
}
