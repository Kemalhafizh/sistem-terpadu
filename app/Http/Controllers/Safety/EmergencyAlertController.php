<?php

namespace App\Http\Controllers\Safety;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EmergencyAlertController extends Controller
{
    public function index()
    {
        return view('fase60.emergency.alert-center');
    }

    public function dispatchTeam(Request $request, $id)
    {
        return redirect()->back();
    }

    public function reportForm($id)
    {
        return view('fase60.emergency.report-form');
    }
}
