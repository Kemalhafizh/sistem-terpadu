<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
use App\Models\CarbonEmission;
use App\Models\UtilityBill;

class UtilityTrackerController extends Controller
{
    public function dashboard()
    {
        $bills = UtilityBill::latest()->get();
        $emissions = CarbonEmission::latest()->get();

        return view('facility.utility.dashboard', compact('bills', 'emissions'));
    }
}
