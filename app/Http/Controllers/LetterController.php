<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\Employee;
use App\Models\Letter;
use App\Models\LetterTemplate;
use App\Models\Student;
use App\Services\LetterService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LetterController extends Controller
{
    use AuthorizesRequests;

    protected $letterService;

    public function __construct(LetterService $letterService)
    {
        $this->letterService = $letterService;
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', Letter::class);

        $query = Letter::with(['template', 'student.classroom', 'employee', 'creator']);

        if ($request->filled('template_id')) {
            $query->where('template_id', $request->template_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('letter_number', 'like', "%{$search}%")
                    ->orWhereHas('student', fn ($sq) => $sq->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('employee', fn ($eq) => $eq->where('name', 'like', "%{$search}%"));
            });
        }

        $letters = $query->orderBy('created_at', 'desc')->paginate(15);
        $templates = LetterTemplate::where('is_active', true)->get();

        return view('letters.index', compact('letters', 'templates'));
    }

    public function create(Request $request)
    {
        $this->authorize('create', Letter::class);

        $template = LetterTemplate::findOrFail($request->template_id);
        $students = Student::with('classroom')->orderBy('name')->get();
        $employees = Employee::orderBy('name')->get();

        return view('letters.create', compact('template', 'students', 'employees'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Letter::class);

        $template = LetterTemplate::findOrFail($request->template_id);

        $rules = [
            'issue_date' => 'required|date',
            'student_id' => 'nullable|exists:students,id',
            'employee_id' => 'nullable|exists:employees,id',
            'notes' => 'nullable|string',
            'field_values' => 'nullable|array',
        ];

        $request->validate($rules);

        $letter = Letter::create([
            'template_id' => $template->id,
            'field_values' => $request->field_values ?? [],
            'student_id' => $request->student_id,
            'employee_id' => $request->employee_id,
            'created_by' => auth()->id(),
            'issue_date' => $request->issue_date,
            'status' => 'draft',
            'notes' => $request->notes,
        ]);

        return redirect()->route('letters.index')->with('success', 'Draf surat resmi berhasil disimpan!');
    }

    public function show(Letter $letter)
    {
        $this->authorize('view', $letter);

        $letter->load(['template', 'student.classroom', 'employee', 'creator']);

        $renderedBody = $this->letterService->renderLetterBody($letter);

        return view('letters.show', compact('letter', 'renderedBody'));
    }

    public function issue(Letter $letter)
    {
        $this->authorize('issue', Letter::class);

        if ($letter->status !== 'draft') {
            return redirect()->back()->with('error', 'Hanya surat berstatus draf yang dapat diterbitkan!');
        }

        $number = $this->letterService->generateLetterNumber($letter->template, $letter->issue_date->format('Y-m-d'));

        $letter->update([
            'letter_number' => $number,
            'status' => 'issued',
        ]);

        return redirect()->route('letters.show', $letter->id)->with('success', 'Surat resmi berhasil diterbitkan dengan nomor: '.$number);
    }

    public function destroy(Letter $letter)
    {
        $this->authorize('delete', $letter);

        $letter->delete();

        return redirect()->route('letters.index')->with('success', 'Surat resmi berhasil dihapus!');
    }

    public function downloadPdf(Letter $letter)
    {
        $this->authorize('view', $letter);

        $pdf = $this->letterService->generatePdf($letter);
        $filename = str_replace('/', '_', $letter->letter_number ?? 'draf_surat').'.pdf';

        return $pdf->download($filename);
    }

    // =========================================================
    // TEMPLATE MANAGEMENT & BULK GENERATION
    // =========================================================
    public function manageTemplates()
    {
        // Admin or superadmin or staf_tu
        if (! in_array(auth()->user()->role, ['superadmin', 'admin', 'staf_tu'])) {
            abort(403);
        }

        $templates = LetterTemplate::all();

        return view('letters.templates.index', compact('templates'));
    }

    public function storeTemplate(Request $request)
    {
        if (! in_array(auth()->user()->role, ['superadmin', 'admin', 'staf_tu'])) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:letter_templates,code',
            'body_template' => 'required|string',
            'required_fields' => 'required|array',
            'signature' => 'nullable|image|max:1024',
            'signer_name' => 'required|string',
            'signer_title' => 'required|string',
        ]);

        $signaturePath = null;
        if ($request->hasFile('signature')) {
            // Simpan secara aman di private storage
            $signaturePath = $request->file('signature')->store('signatures', 'local');
        }

        LetterTemplate::create([
            'name' => $request->name,
            'code' => strtoupper($request->code),
            'body_template' => $request->body_template,
            'required_fields' => $request->required_fields,
            'signer_name' => $request->signer_name,
            'signer_title' => $request->signer_title,
            'signature_path' => $signaturePath,
            'is_active' => true,
        ]);

        return redirect()->route('letters.templates.index')->with('success', 'Template surat baru berhasil ditambahkan!');
    }

    public function showBulkForm()
    {
        $this->authorize('create', Letter::class);

        $templates = LetterTemplate::where('is_active', true)->get();
        $classrooms = Classroom::orderBy('name')->get();

        return view('letters.bulk', compact('templates', 'classrooms'));
    }

    public function bulkStore(Request $request)
    {
        $this->authorize('create', Letter::class);

        $request->validate([
            'template_id' => 'required|exists:letter_templates,id',
            'classroom_id' => 'required|exists:classrooms,id',
            'field_values' => 'nullable|array',
        ]);

        $template = LetterTemplate::findOrFail($request->template_id);
        $students = Student::where('classroom_id', $request->classroom_id)->pluck('id')->toArray();

        if (empty($students)) {
            return redirect()->back()->with('error', 'Tidak ada siswa aktif di kelas yang dipilih!');
        }

        $letters = $this->letterService->bulkGenerate(
            $template,
            $students,
            $request->field_values ?? [],
            auth()->id()
        );

        return redirect()->route('letters.index')->with('success', count($letters).' Draf surat massal berhasil dibuat!');
    }
}
