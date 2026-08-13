<?php
namespace App\Http\Controllers\Foundation;
use App\Http\Controllers\Controller;
use App\Models\StaffRotation;
use Illuminate\Http\Request;

class StaffRotationController extends Controller {
    public function index() { return view('foundation.rotation.index', ['rotations' => StaffRotation::all()]); }
    public function create() { return view('foundation.rotation.create'); }
    public function store(Request $request) { StaffRotation::create($request->all()); return redirect()->route('foundation.rotation.index'); }
    public function show($id) { return view('foundation.rotation.show', ['rotation' => StaffRotation::findOrFail($id)]); }
}
