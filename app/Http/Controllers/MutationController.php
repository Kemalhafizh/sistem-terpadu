<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMutationRequest;
use App\Models\Student;
use App\Models\StudentMutation;
use App\Services\StudentMutationService;

class MutationController extends Controller
{
    protected $mutationService;

    public function __construct(StudentMutationService $mutationService)
    {
        $this->mutationService = $mutationService;
    }

    public function index()
    {
        $mutations = StudentMutation::with('student.user')->latest()->paginate(20);

        return view('students.mutations.index', compact('mutations'));
    }

    public function create()
    {
        $students = Student::where('status', 'active')->with('user')->get();

        return view('students.mutations.create', compact('students'));
    }

    public function store(StoreMutationRequest $request)
    {
        $this->mutationService->processMutation(
            $request->validated(),
            $request->file('document')
        );

        return redirect()->route('mutations.index')->with('success', 'Data mutasi berhasil dicatat.');
    }
}
