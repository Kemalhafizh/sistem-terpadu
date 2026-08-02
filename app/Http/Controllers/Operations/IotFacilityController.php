<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
use App\Models\IotDevice;
use Illuminate\Http\Request;

class IotFacilityController extends Controller
{
    public function dashboard()
    {
        $devices = IotDevice::latest()->get();
        return view('facility.iot.dashboard', compact('devices'));
    }
}
