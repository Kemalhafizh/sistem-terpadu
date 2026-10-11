<?php

namespace App\Http\Controllers\HRD;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\FingerprintImport;
use App\Services\HRD\FingerprintAttendanceImporter;
use App\Services\HRD\FingerprintImportException;
use Illuminate\Http\Request;

/** Upload the fingerprint machine's log; staff attendance is built from it (FASE 119.6.1). */
class FingerprintImportController extends Controller
{
    public function index()
    {
        $lastImport = session('fingerprint_import') ? FingerprintImport::find(session('fingerprint_import')) : null;

        return view('hrd.fingerprint-import', [
            'lastImport' => $lastImport,
            'imports' => FingerprintImport::with('importer')->latest()->take(10)->get(),
            'unmapped' => Employee::with('user')->whereNull('fingerprint_id')->orderBy('id')->get(),
        ]);
    }

    public function store(Request $request, FingerprintAttendanceImporter $importer)
    {
        $request->validate(['file' => 'required|file|max:10240|extensions:csv,txt,dat,xlsx,xls']);
        $file = $request->file('file');

        try {
            $import = $importer->import($file->getRealPath(), $file->getClientOriginalName(), $request->user());
        } catch (FingerprintImportException $e) {
            return back()->withErrors(['file' => $e->getMessage()]);
        }

        return redirect()->route('hrd.fingerprint-import.index')
            ->with('fingerprint_import', $import->id)
            ->with('success', __('Log mesin sidik jari berhasil diimpor.'));
    }
}
