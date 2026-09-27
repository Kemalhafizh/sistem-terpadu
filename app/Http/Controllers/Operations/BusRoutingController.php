<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
use App\Models\BusRoute;

class BusRoutingController extends Controller
{
    public function routes()
    {
        $routes = BusRoute::with(['bus', 'passengers'])->latest()->get();

        return view('facility.transport.routes', compact('routes'));
    }
}
