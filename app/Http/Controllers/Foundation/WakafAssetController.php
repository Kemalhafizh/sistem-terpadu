<?php
namespace App\Http\Controllers\Foundation;
use App\Http\Controllers\Controller;
use App\Models\WakafAsset;
use Illuminate\Http\Request;

class WakafAssetController extends Controller {
    public function index() { return view('foundation.wakaf.index', ['assets' => WakafAsset::all()]); }
    public function create() { return view('foundation.wakaf.create'); }
    public function store(Request $request) { WakafAsset::create($request->all()); return redirect()->route('foundation.wakaf.index'); }
    public function show($id) { return view('foundation.wakaf.show', ['asset' => WakafAsset::findOrFail($id)]); }
    public function utilization($id) { return view('foundation.wakaf.utilization', ['asset' => WakafAsset::findOrFail($id)]); }
}
