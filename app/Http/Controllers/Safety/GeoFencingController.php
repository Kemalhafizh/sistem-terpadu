<?php

namespace App\Http\Controllers\Safety;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class GeoFencingController extends Controller
{
    public function index()
    {
        return view('fase60.geo-fencing.zone-manager');
    }

    public function defineZone(Request $request)
    {
        return redirect()->back();
    }

    public function logBreach(Request $request)
    {
        return redirect()->back();
    }

    public function acknowledgeBreach($id)
    {
        return redirect()->back();
    }

    public function history()
    {
        return view('fase60.geo-fencing.breach-log');
    }
}
