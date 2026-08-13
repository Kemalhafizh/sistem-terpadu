<?php
namespace App\Http\Controllers\Foundation;
use App\Http\Controllers\Controller;
use App\Models\FoundationUnit;
use Illuminate\Http\Request;

class FoundationUnitController extends Controller {
    public function index() { return view('foundation.units.index', ['units' => FoundationUnit::all()]); }
    public function show($id) { return view('foundation.units.show', ['unit' => FoundationUnit::findOrFail($id)]); }
    public function create() { return view('foundation.units.create'); }
    public function store(Request $request) { FoundationUnit::create($request->all()); return redirect()->route('foundation.units.index'); }
}
