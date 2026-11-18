<?php

namespace App\Http\Controllers;

use App\Http\Middleware\RoleMiddleware;
use App\Models\QuestionBank;
use App\Models\Subject;
use App\Support\Access\StudentDataAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class QuestionBankController extends Controller
{
    private function authorizeBankManager(?QuestionBank $questionBank = null): void
    {
        $user = auth()->user();
        $allowedRoles = [...StudentDataAccess::TEACHER_ROLES, 'waka_kurikulum', 'staf_tu', 'admin', 'superadmin'];

        if (! $user || ! RoleMiddleware::allowsUser($user, $allowedRoles)) {
            abort(403, 'Anda tidak memiliki akses ke Bank Soal.');
        }

        if ($questionBank && ! RoleMiddleware::allowsUser($user, ['waka_kurikulum', 'admin', 'superadmin']) && $questionBank->created_by !== $user->id) {
            abort(403, 'Anda hanya dapat mengelola butir soal yang Anda buat sendiri.');
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorizeBankManager();

        $subjects = Subject::all();

        $query = QuestionBank::with(['subject', 'creator']);

        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }
        if ($request->filled('difficulty')) {
            $query->where('difficulty', $request->difficulty);
        }
        if ($request->filled('grade_level')) {
            $query->where('grade_level', $request->grade_level);
        }

        $questions = $query->latest()->paginate(15)->withQueryString();

        return view('cbt.question-bank.index', compact('questions', 'subjects'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorizeBankManager();

        $subjects = Subject::all();

        return view('cbt.question-bank.create', compact('subjects'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeBankManager();

        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'question_text' => 'required|string',
            'question_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'type' => 'required|in:multiple_choice,true_false,essay',
            'options' => 'required_if:type,multiple_choice|array',
            'correct_answer' => 'required|string',
            'explanation' => 'nullable|string',
            'difficulty' => 'required|in:easy,medium,hard',
            'competency_code' => 'nullable|string|max:50',
            'grade_level' => 'required|in:X,XI,XII',
        ]);

        $imagePath = null;
        if ($request->hasFile('question_image')) {
            $imagePath = $request->file('question_image')->store('uploads/cbt/questions', 'public');
        }

        $options = null;
        if ($request->type === 'multiple_choice') {
            $options = $request->options;
        } elseif ($request->type === 'true_false') {
            $options = ['true' => 'Benar', 'false' => 'Salah'];
        }

        QuestionBank::create([
            'subject_id' => $request->subject_id,
            'created_by' => auth()->id(),
            'question_text' => $request->question_text,
            'question_image' => $imagePath,
            'type' => $request->type,
            'options' => $options,
            'correct_answer' => $request->correct_answer,
            'explanation' => $request->explanation,
            'difficulty' => $request->difficulty,
            'competency_code' => $request->competency_code,
            'grade_level' => $request->grade_level,
        ]);

        return redirect()->route('cbt.question-banks.index')
            ->with('success', 'Soal berhasil ditambahkan ke Bank Soal.');
    }

    /**
     * Display the specified resource.
     */
    public function show(QuestionBank $questionBank)
    {
        $this->authorizeBankManager();

        $questionBank->load(['subject', 'creator']);

        return view('cbt.question-bank.show', compact('questionBank'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(QuestionBank $questionBank)
    {
        $this->authorizeBankManager($questionBank);

        $subjects = Subject::all();

        return view('cbt.question-bank.edit', compact('questionBank', 'subjects'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, QuestionBank $questionBank)
    {
        $this->authorizeBankManager($questionBank);

        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'question_text' => 'required|string',
            'question_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'type' => 'required|in:multiple_choice,true_false,essay',
            'options' => 'required_if:type,multiple_choice|array',
            'correct_answer' => 'required|string',
            'explanation' => 'nullable|string',
            'difficulty' => 'required|in:easy,medium,hard',
            'competency_code' => 'nullable|string|max:50',
            'grade_level' => 'required|in:X,XI,XII',
        ]);

        $imagePath = $questionBank->question_image;
        if ($request->hasFile('question_image')) {
            if ($imagePath) {
                Storage::disk('public')->delete($imagePath);
            }
            $imagePath = $request->file('question_image')->store('uploads/cbt/questions', 'public');
        }

        $options = null;
        if ($request->type === 'multiple_choice') {
            $options = $request->options;
        } elseif ($request->type === 'true_false') {
            $options = ['true' => 'Benar', 'false' => 'Salah'];
        }

        $questionBank->update([
            'subject_id' => $request->subject_id,
            'question_text' => $request->question_text,
            'question_image' => $imagePath,
            'type' => $request->type,
            'options' => $options,
            'correct_answer' => $request->correct_answer,
            'explanation' => $request->explanation,
            'difficulty' => $request->difficulty,
            'competency_code' => $request->competency_code,
            'grade_level' => $request->grade_level,
        ]);

        return redirect()->route('cbt.question-banks.index')
            ->with('success', 'Soal berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(QuestionBank $questionBank)
    {
        $this->authorizeBankManager($questionBank);

        if ($questionBank->question_image) {
            Storage::disk('public')->delete($questionBank->question_image);
        }

        $questionBank->delete();

        return redirect()->route('cbt.question-banks.index')
            ->with('success', 'Soal berhasil dihapus dari Bank Soal.');
    }
}
