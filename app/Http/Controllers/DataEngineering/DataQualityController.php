<?php
namespace App\Http\Controllers\DataEngineering;
use App\Http\Controllers\Controller;
use App\Services\DataEngineering\DataQualityService;
use Illuminate\Http\Request;
class DataQualityController extends Controller {
    protected $service;
    public function __construct(DataQualityService $service) { $this->service = $service; }
    public function healthDashboard() { return view('data-quality.health-dashboard'); }
    public function resolutionCenter() { return view('data-quality.resolution-center'); }
    public function resolveAnomaly(Request $request, $id) {
        $this->service->resolveAnomaly($id, auth()->id());
        return redirect()->back();
    }
}
