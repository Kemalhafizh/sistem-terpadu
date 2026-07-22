<?php

namespace App\Http\Controllers;

use App\Models\ImportLog;
use App\Services\ImportExportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImportExportController extends Controller
{
    protected $service;

    public function __construct(ImportExportService $service)
    {
        $this->service = $service;
    }

    /**
     * Display the Import/Export Hub card grid.
     */
    public function index()
    {
        $modules = $this->service->getAvailableModules();
        return view('import-export.index', compact('modules'));
    }

    /**
     * Handle the uploaded file, store it temporarily, and present the column mapping page.
     */
    public function upload(Request $request)
    {
        $request->validate([
            'module' => 'required|string',
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        $module = $request->module;
        $file = $request->file('file');
        
        // Store temporarily
        $path = $file->store('temp_imports');
        $fileName = $file->getClientOriginalName();

        // Parse file to get headers and preview
        $filePath = Storage::path($path);
        $fileInstance = new \Illuminate\Http\UploadedFile($filePath, $fileName);
        
        $parsed = $this->service->parseUploadedFile($fileInstance);
        $headers = $parsed['headers'];
        $preview = $parsed['preview'];
        $totalRows = count($parsed['all_data']);

        if (empty($headers)) {
            Storage::delete($path);
            return redirect()->route('import-export.index')->with('error', 'Berkas kosong atau tidak dapat diuraikan / The file is empty or cannot be parsed.');
        }

        // Get DB columns for mapping
        $dbColumns = $this->service->getModuleColumns($module);

        return view('import-export.mapping', compact(
            'module',
            'path',
            'fileName',
            'headers',
            'preview',
            'totalRows',
            'dbColumns'
        ));
    }

    /**
     * Execute the import using the selected column mappings.
     */
    public function execute(Request $request)
    {
        $request->validate([
            'module' => 'required|string',
            'file_path' => 'required|string',
            'file_name' => 'required|string',
            'mapping' => 'required|array',
        ]);

        $module = $request->module;
        $path = $request->file_path;
        $fileName = $request->file_name;
        $mapping = $request->mapping; // e.g. ['name' => 0, 'email' => 1]

        // Validasi dan sanitasi jalur berkas untuk mencegah path traversal
        $isSafePath = str_starts_with($path, 'temp_imports/') && !str_contains($path, '..') && !str_contains($path, '\\');

        if (!$isSafePath || !Storage::exists($path)) {
            return redirect()->route('import-export.index')->with('error', 'Berkas tidak valid atau sesi kadaluarsa / Invalid file or session expired.');
        }

        $absolutePath = Storage::path($path);
        $fileInstance = new \Illuminate\Http\UploadedFile($absolutePath, $fileName);

        $parsed = $this->service->parseUploadedFile($fileInstance);
        $data = $parsed['all_data'];

        // Execute import
        $result = $this->service->executeImport($module, $mapping, $data, $fileName, auth()->id());

        // Delete temporary file
        Storage::delete($path);

        if (!$result['success']) {
            return redirect()->route('import-export.index')->with('error', $result['message']);
        }

        $message = "Sukses mengimpor {$result['success_count']} baris data.";
        if ($result['error_count'] > 0) {
            $message .= " Gagal mengimpor {$result['error_count']} baris karena error validasi.";
        }

        return redirect()->route('import-export.history')->with(
            $result['error_count'] > 0 ? 'warning' : 'success',
            $message
        );
    }

    /**
     * Display the import history log timeline.
     */
    public function history()
    {
        $logs = ImportLog::with('importedBy')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('import-export.history', compact('logs'));
    }

    /**
     * Trigger a dynamic transaction rollback for a previously imported batch.
     */
    public function rollback(int $id)
    {
        $result = $this->service->rollbackImport($id);

        if (!$result['success']) {
            return back()->with('error', $result['message']);
        }

        return back()->with('success', $result['message']);
    }

    /**
     * Export data of a selected module into multiple file formats.
     */
    public function exportData(Request $request)
    {
        $request->validate([
            'module' => 'required|string|in:students,employees,subjects,classrooms',
            'format' => 'required|string|in:xlsx,csv,pdf,json',
        ]);

        return $this->service->export($request->module, $request->format);
    }
}
