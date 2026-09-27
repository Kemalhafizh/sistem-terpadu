<?php

namespace App\Http\Controllers\Compliance;

use App\Http\Controllers\Controller;
use App\Models\LegalContract;

class LegalContractController extends Controller
{
    public function index()
    {
        $contracts = LegalContract::with('renewals')->latest()->get();

        return view('compliance.contracts.index', compact('contracts'));
    }
}
