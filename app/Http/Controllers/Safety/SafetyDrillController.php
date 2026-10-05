<?php

namespace App\Http\Controllers\Safety;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SafetyDrillController extends Controller
{
    public function index()
    {
        return view('fase60.safety-drill.calendar');
    }

    public function schedule(Request $request)
    {
        return redirect()->back();
    }

    public function concludeDrill(Request $request, $id)
    {
        return redirect()->back();
    }

    public function evaluation($id)
    {
        return view('fase60.safety-drill.evaluation');
    }
}
