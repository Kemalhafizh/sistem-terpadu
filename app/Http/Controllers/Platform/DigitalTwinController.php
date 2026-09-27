<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\DigitalTwinSimulation;

class DigitalTwinController extends Controller
{
    public function simulation()
    {
        $simulations = DigitalTwinSimulation::with('creator')->latest()->get();

        return view('developer.twin.simulation', compact('simulations'));
    }
}
