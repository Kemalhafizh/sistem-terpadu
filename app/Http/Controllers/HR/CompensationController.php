<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\BenefitClaim;
use App\Models\CompensationStructure;

class CompensationController extends Controller
{
    public function dashboard()
    {
        return view('compensation.dashboard');
    }

    public function structures()
    {
        $structures = CompensationStructure::all();

        return view('compensation.employee-structures', compact('structures'));
    }

    public function claims()
    {
        $claims = BenefitClaim::all();

        return view('compensation.claims-approval', compact('claims'));
    }
}
