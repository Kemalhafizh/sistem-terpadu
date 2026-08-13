<?php
namespace App\Http\Controllers\Foundation;
use App\Http\Controllers\Controller;
use App\Models\FoundationKpi;

class FoundationKpiController extends Controller {
    public function scorecard() { return view('foundation.kpi.scorecard', ['kpis' => FoundationKpi::all()]); }
}
