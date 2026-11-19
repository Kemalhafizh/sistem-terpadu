<?php

namespace App\Http\Controllers;

use App\Http\Middleware\RoleMiddleware;
use App\Models\Classroom;
use App\Models\Employee;
use App\Models\Letter;
use App\Models\LetterTemplate;
use App\Models\Student;
use App\Models\WorkflowDefinition;
use App\Services\LetterService;
use App\Services\WorkflowService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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

        $query = Letter::with(['template', 'student.user', 'student.classroom', 'employee.user', 'creator', 'approver']);

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
                    ->orWhereHas('student.user', fn ($sq) => $sq->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('employee.user', fn ($eq) => $eq->where('name', 'like', "%{$search}%"));
            });
        }

        $letters = $query->orderBy('created_at', 'desc')->paginate(15);
        $templates = LetterTemplate::where('is_active', true)->get();

        $statusCounts = Letter::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $stats = [
            'total' => (int) $statusCounts->sum(),
            'draft' => (int) ($statusCounts[Letter::STATUS_DRAFT] ?? 0),
            'pending' => (int) ($statusCounts[Letter::STATUS_PENDING] ?? 0),
            'approved' => (int) ($statusCounts[Letter::STATUS_APPROVED] ?? 0),
            'issued' => (int) ($statusCounts[Letter::STATUS_ISSUED] ?? 0),
        ];

        return view('letters.index', compact('letters', 'templates', 'stats'));
    }

    public function create(Request $request)
    {
        $this->authorize('create', Letter::class);

        $template = LetterTemplate::findOrFail($request->template_id);
        $students = Student::with(['user', 'classroom'])
            ->get()
            ->sortBy(fn (Student $s) => $s->user?->name ?? '')
            ->values();
        $employees = Employee::with('user')
            ->get()
            ->sortBy(fn (Employee $e) => $e->user?->name ?? '')
            ->values();

        return view('letters.create', compact('template', 'students', 'employees'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Letter::class);

        $template = LetterTemplate::findOrFail($request->template_id);

        $request->validate([
            'issue_date' => 'required|date',
            'student_id' => 'nullable|exists:students,id',
            'employee_id' => 'nullable|exists:employees,id',
            'notes' => 'nullable|string',
            'field_values' => 'nullable|array',
        ]);

        Letter::create([
            'template_id' => $template->id,
            'field_values' => $request->field_values ?? [],
            'student_id' => $request->student_id,
            'employee_id' => $request->employee_id,
            'created_by' => auth()->id(),
            'issue_date' => $request->issue_date,
            'status' => Letter::STATUS_DRAFT,
            'notes' => $request->notes,
        ]);

        return redirect()->route('letters.index')->with('success', 'Draf surat resmi berhasil disimpan!');
    }

    public function edit(Letter $letter)
    {
        $this->authorize('update', $letter);

        $letter->load(['template', 'student.user', 'employee.user']);
        $template = $letter->template;
        $students = Student::with(['user', 'classroom'])
            ->get()
            ->sortBy(fn (Student $s) => $s->user?->name ?? '')
            ->values();
        $employees = Employee::with('user')
            ->get()
            ->sortBy(fn (Employee $e) => $e->user?->name ?? '')
            ->values();

        return view('letters.create', compact('template', 'students', 'employees', 'letter'));
    }

    public function update(Request $request, Letter $letter)
    {
        $this->authorize('update', $letter);

        $validated = $request->validate([
            'issue_date' => 'required|date',
            'student_id' => 'nullable|exists:students,id',
            'employee_id' => 'nullable|exists:employees,id',
            'notes' => 'nullable|string',
            'field_values' => 'nullable|array',
        ]);

        $letter->update([
            'issue_date' => $validated['issue_date'],
            'student_id' => $validated['student_id'] ?? null,
            'employee_id' => $validated['employee_id'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'field_values' => $validated['field_values'] ?? [],
        ]);

        return redirect()->route('letters.show', $letter->id)->with('success', 'Draf surat berhasil diperbarui.');
    }

    public function show(Letter $letter)
    {
        $this->authorize('view', $letter);

        $letter->load([
            'template',
            'student.user',
            'student.classroom',
            'employee.user',
            'creator',
            'approver',
            'workflowRequest.approvals.approver',
        ]);

        $renderedBody = $this->letterService->renderLetterBody($letter);
        $verificationUrl = filled($letter->verification_token)
            ? route('letters.verify', $letter->verification_token)
            : null;

        return view('letters.show', compact('letter', 'renderedBody', 'verificationUrl'));
    }

    public function submitApproval(Request $request, Letter $letter, WorkflowService $workflow)
    {
        $this->authorize('submit', $letter);

        if (! in_array($letter->status, [Letter::STATUS_DRAFT, Letter::STATUS_REVISION], true)) {
            $message = 'Hanya surat berstatus draf atau revisi yang dapat diajukan ke Kepala Sekolah.';
            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 422);
            }

            return redirect()->back()->with('error', $message);
        }

        WorkflowDefinition::firstOrCreate(
            ['code' => 'letter_approval'],
            [
                'name' => 'Persetujuan Surat Resmi',
                'description' => 'Alur persetujuan surat keluar sekolah oleh Kepala Sekolah sebelum diterbitkan Staf TU.',
                'steps' => [
                    ['order' => 1, 'role' => 'kepala_sekolah', 'sla_hours' => 48],
                ],
                'is_active' => true,
            ]
        );

        $notes = $request->input('notes') ?: 'Pengajuan persetujuan surat: '.$letter->template->name;

        DB::transaction(function () use ($letter, $workflow, $request, $notes) {
            $existingWorkflow = $letter->workflowRequest()->first();

            if ($letter->status === Letter::STATUS_REVISION && $existingWorkflow && $existingWorkflow->status === 'revision') {
                $workflow->resubmit($existingWorkflow, $request->user(), $notes);
            } else {
                $workflow->initiate('letter_approval', $request->user(), $letter, $notes);
            }

            $letter->update(['submitted_at' => now()]);
        });

        return redirect()->route('letters.show', $letter->id)
            ->with('success', 'Surat berhasil diajukan untuk persetujuan Kepala Sekolah.');
    }

    public function issue(Request $request, Letter $letter)
    {
        $this->authorize('issue', Letter::class);

        if ($letter->status !== Letter::STATUS_APPROVED) {
            $message = 'Surat harus disetujui Kepala Sekolah terlebih dahulu sebelum diterbitkan.';
            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 422);
            }

            return redirect()->back()->with('error', $message);
        }

        $issued = DB::transaction(function () use ($letter) {
            /** @var Letter $locked */
            $locked = Letter::with('template')->whereKey($letter->id)->lockForUpdate()->firstOrFail();
            if ($locked->status !== Letter::STATUS_APPROVED) {
                return null;
            }

            $number = $this->letterService->generateLetterNumber($locked->template, $locked->issue_date->format('Y-m-d'));
            $token = $locked->verification_token ?: Str::random(40);

            $locked->update([
                'letter_number' => $number,
                'status' => Letter::STATUS_ISSUED,
                'verification_token' => $token,
            ]);

            return $locked->fresh();
        });

        if (! $issued) {
            return redirect()->back()->with('error', 'Status surat berubah sebelum diterbitkan.');
        }

        return redirect()->route('letters.show', $issued->id)
            ->with('success', 'Surat resmi berhasil diterbitkan dengan nomor: '.$issued->letter_number);
    }

    public function revoke(Request $request, Letter $letter)
    {
        $this->authorize('revoke', $letter);

        $validated = $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $noteEntry = 'Dibatalkan oleh '.auth()->user()->name
            .(! empty($validated['reason']) ? ': '.$validated['reason'] : '');

        $letter->update([
            'status' => Letter::STATUS_REVOKED,
            'notes' => trim(($letter->notes ? $letter->notes."\n" : '').$noteEntry),
        ]);

        return redirect()->route('letters.show', $letter->id)
            ->with('success', 'Status surat berhasil dicabut (revoked).');
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

    public function verify(string $token)
    {
        $letter = Letter::with(['template:id,name,code,signer_title,signer_name', 'approver:id,name'])
            ->where('verification_token', $token)
            ->first();

        $state = match ($letter?->status) {
            Letter::STATUS_ISSUED => 'valid',
            Letter::STATUS_REVOKED => 'revoked',
            default => 'invalid',
        };

        return response()->view(
            'letters.verify',
            compact('letter', 'state', 'token'),
            $letter ? 200 : 404
        );
    }

    // =========================================================
    // TEMPLATE MANAGEMENT & BULK GENERATION
    // =========================================================
    public function manageTemplates()
    {
        if (! RoleMiddleware::allowsUser(auth()->user(), ['superadmin', 'admin', 'staf_tu'])) {
            abort(403);
        }

        $templates = LetterTemplate::all();

        return view('letters.templates.index', compact('templates'));
    }

    public function storeTemplate(Request $request)
    {
        if (! RoleMiddleware::allowsUser(auth()->user(), ['superadmin', 'admin', 'staf_tu'])) {
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
