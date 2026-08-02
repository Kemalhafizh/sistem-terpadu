<?php

namespace App\Http\Controllers\Compliance;

use App\Http\Controllers\Controller;
use App\Models\RetentionPolicy;
use Illuminate\Http\Request;

class DataRetentionController extends Controller
{
    public function index()
    {
        $policies = RetentionPolicy::with('purgeLogs')->latest()->get();
        return view('compliance.retention.index', compact('policies'));
    }
}
