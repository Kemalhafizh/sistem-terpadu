<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMutationRequest;
use App\Models\Classroom;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\StudentMutation;
use App\Services\StudentMutationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class MutationController extends Controller
{
    public function __construct(
        protected StudentMutationService $mutationService,
    ) {}

    public function index(): View
    {
        $mutations = StudentMutation::query()
            ->with(['student.user', 'classroom', 'schoolYear', 'requester', 'approver'])
            ->latest()
            ->paginate(20);

        return view('students.mutations.index', compact('mutations'));
    }

    public function create(): View
    {
        $students = Student::where('status', 'active')->with(['user', 'classroom'])->orderBy('nis')->get();
        $classrooms = Classroom::orderBy('name')->get();
        $schoolYears = SchoolYear::orderByDesc('name')->get();

        return view('students.mutations.create', compact('students', 'classrooms', 'schoolYears'));
    }

    public function store(StoreMutationRequest $request): RedirectResponse
    {
        $this->mutationService->processMutation(
            $request->validated(),
            $request->file('document'),
            $request->user()
        );

        return redirect()->route('mutations.index')
            ->with('success', __('Permohonan mutasi berhasil dicatat dan diajukan ke Kepala Sekolah.'));
    }

    public function approve(Request $request, StudentMutation $mutation): RedirectResponse
    {
        $validated = $request->validate([
            'remarks' => ['nullable', 'string', 'max:500'],
            'letter_number' => ['nullable', 'string', 'max:120'],
        ]);

        $this->mutationService->approveMutation(
            $mutation,
            $request->user(),
            $validated['remarks'] ?? null,
            $validated['letter_number'] ?? null
        );

        return redirect()->route('mutations.index')
            ->with('success', __('Permohonan mutasi siswa berhasil disetujui dan dieksekusi ke riwayat rombel.'));
    }

    public function reject(Request $request, StudentMutation $mutation): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'min:3', 'max:500'],
        ]);

        $this->mutationService->rejectMutation($mutation, $request->user(), $validated['reason']);

        return redirect()->route('mutations.index')
            ->with('success', __('Permohonan mutasi siswa ditolak.'));
    }

    public function reviewBillings(Request $request, StudentMutation $mutation): RedirectResponse
    {
        $validated = $request->validate([
            'notes' => ['nullable', 'string', 'max:500'],
            'cancel_future_unpaid' => ['nullable', 'boolean'],
        ]);

        $this->mutationService->reviewMutationBillings(
            $mutation,
            $request->user(),
            $validated['notes'] ?? null,
            (bool) ($validated['cancel_future_unpaid'] ?? false)
        );

        return redirect()->route('mutations.index')
            ->with('success', __('Tinjauan tagihan berjalan untuk mutasi keluar berhasil diperbarui.'));
    }

    public function downloadDocument(StudentMutation $mutation)
    {
        $path = $mutation->document_path;
        if (! $path) {
            abort(404);
        }

        if (Storage::disk('local')->exists($path)) {
            return Storage::disk('local')->response($path);
        }

        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->response($path);
        }

        abort(404);
    }
}
