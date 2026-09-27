<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\OsisCandidate;
use App\Models\OsisElection;
use App\Models\OsisMember;
use App\Models\OsisTransaction;
use App\Models\OsisVote;
use App\Models\OsisWorkProgram;
use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class OsisController extends Controller
{
    public function dashboard()
    {
        $transactions = OsisTransaction::with('recorder')
            ->orderBy('transaction_date', 'desc')
            ->paginate(15);

        // Agregasi Keuangan OSIS
        $totalIncome = OsisTransaction::where('type', 'income')->sum('amount');
        $totalExpense = OsisTransaction::where('type', 'expense')->sum('amount');
        $currentBalance = $totalIncome - $totalExpense;

        // Distribusi Kategori Pemasukan
        $categoriesIncome = OsisTransaction::where('type', 'income')
            ->selectRaw('category, sum(amount) as total')
            ->groupBy('category')
            ->pluck('total', 'category')
            ->toArray();

        // Distribusi Kategori Pengeluaran
        $categoriesExpense = OsisTransaction::where('type', 'expense')
            ->selectRaw('category, sum(amount) as total')
            ->groupBy('category')
            ->pluck('total', 'category')
            ->toArray();

        // OSIS enhancements
        $activeYear = AcademicYear::where('is_active', true)->first();
        $members = OsisMember::with('student.user')
            ->where('academic_year_id', $activeYear->id ?? 0)
            ->where('status', 'active')
            ->take(5)
            ->get();

        $programs = OsisWorkProgram::where('academic_year_id', $activeYear->id ?? 0)->latest()->take(5)->get();
        $elections = OsisElection::where('academic_year_id', $activeYear->id ?? 0)->get();

        return view('extracurricular.osis.dashboard', compact(
            'transactions',
            'totalIncome',
            'totalExpense',
            'currentBalance',
            'categoriesIncome',
            'categoriesExpense',
            'members',
            'programs',
            'elections'
        ));
    }

    public function storeTransaction(Request $request)
    {
        $request->validate([
            'type' => 'required|in:income,expense',
            'amount' => 'required|numeric|min:1',
            'description' => 'required|string|max:255',
            'category' => 'required|string|in:Iuran,Sponsor,Kegiatan,Perlengkapan,Lainnya',
            'transaction_date' => 'required|date',
            'receipt' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:10240', // Maks 10MB
        ]);

        $receiptPath = null;
        if ($request->hasFile('receipt')) {
            $receiptPath = $request->file('receipt')->store('uploads/osis/receipts', 'public');
        }

        OsisTransaction::create([
            'type' => $request->type,
            'amount' => $request->amount,
            'description' => $request->description,
            'category' => $request->category,
            'transaction_date' => $request->transaction_date,
            'receipt_path' => $receiptPath,
            'recorded_by' => auth()->id(),
        ]);

        return back()->with('success', 'Transaksi kas OSIS berhasil dicatat.');
    }

    public function editTransaction($id)
    {
        $transaction = OsisTransaction::findOrFail($id);

        return view('extracurricular.osis.edit_transaction', compact('transaction'));
    }

    public function updateTransaction(Request $request, $id)
    {
        $transaction = OsisTransaction::findOrFail($id);

        $validated = $request->validate([
            'type' => 'required|in:income,expense',
            'amount' => 'required|numeric|min:1',
            'description' => 'required|string|max:255',
            'category' => 'required|string|in:Iuran,Sponsor,Kegiatan,Perlengkapan,Lainnya',
            'transaction_date' => 'required|date',
            'receipt' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:10240',
        ]);

        if ($request->hasFile('receipt')) {
            if ($transaction->receipt_path) {
                Storage::disk('public')->delete($transaction->receipt_path);
            }
            $validated['receipt_path'] = $request->file('receipt')->store('uploads/osis/receipts', 'public');
        }
        unset($validated['receipt']);

        $transaction->update($validated);

        return redirect()->route('osis.dashboard')->with('success', 'Transaksi kas OSIS berhasil diperbarui.');
    }

    public function deleteTransaction($id)
    {
        $transaction = OsisTransaction::findOrFail($id);
        if ($transaction->receipt_path) {
            Storage::disk('public')->delete($transaction->receipt_path);
        }
        $transaction->delete();

        return back()->with('success', 'Transaksi kas berhasil dihapus.');
    }

    public function exportReport(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $transactions = OsisTransaction::with('recorder')
            ->whereBetween('transaction_date', [$request->start_date, $request->end_date])
            ->orderBy('transaction_date', 'asc')
            ->get();

        $totalIncome = $transactions->where('type', 'income')->sum('amount');
        $totalExpense = $transactions->where('type', 'expense')->sum('amount');
        $netBalance = $totalIncome - $totalExpense;

        $pdf = Pdf::loadView('extracurricular.osis.pdf_report', [
            'transactions' => $transactions,
            'totalIncome' => $totalIncome,
            'totalExpense' => $totalExpense,
            'netBalance' => $netBalance,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ]);

        return $pdf->setPaper('a4', 'portrait')->download('Laporan_Kas_OSIS_'.$request->start_date.'_to_'.$request->end_date.'.pdf');
    }

    public function members()
    {
        $activeYear = AcademicYear::where('is_active', true)->first();
        $members = OsisMember::with('student.user')
            ->where('academic_year_id', $activeYear->id ?? 0)
            ->where('status', 'active')
            ->get();

        $students = Student::with(['user', 'classroom'])->where('status', 'active')->get();

        return view('extracurricular.osis.members', compact('members', 'students', 'activeYear'));
    }

    public function storeMember(Request $request)
    {
        $activeYear = AcademicYear::where('is_active', true)->first();
        if (! $activeYear) {
            return back()->with('error', 'Tidak ada tahun ajaran aktif.');
        }

        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'position' => 'required|string|max:255',
        ]);

        OsisMember::create(array_merge($validated, [
            'academic_year_id' => $activeYear->id,
            'status' => 'active',
        ]));

        return back()->with('success', 'Pengurus OSIS baru berhasil ditambahkan.');
    }

    public function updateMember(Request $request, OsisMember $member)
    {
        $validated = $request->validate([
            'position' => 'required|string|max:255',
        ]);

        $member->update($validated);

        return back()->with('success', 'Jabatan pengurus berhasil diperbarui.');
    }

    public function removeMember(OsisMember $member)
    {
        $member->update(['status' => 'inactive']);

        return back()->with('success', 'Pengurus OSIS berhasil dinonaktifkan.');
    }

    public function workPrograms()
    {
        $activeYear = AcademicYear::where('is_active', true)->first();
        $programs = OsisWorkProgram::where('academic_year_id', $activeYear->id ?? 0)->get();

        return view('extracurricular.osis.work-programs', compact('programs', 'activeYear'));
    }

    public function storeWorkProgram(Request $request)
    {
        $activeYear = AcademicYear::where('is_active', true)->first();
        if (! $activeYear) {
            return back()->with('error', 'Tidak ada tahun ajaran aktif.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'sekbid' => 'required|string|max:255',
            'description' => 'required|string',
            'budget_plan' => 'required|numeric|min:0',
            'planned_date' => 'nullable|date',
        ]);

        OsisWorkProgram::create(array_merge($validated, [
            'academic_year_id' => $activeYear->id,
            'status' => 'planned',
        ]));

        return back()->with('success', 'Program kerja baru berhasil ditambahkan.');
    }

    public function updateWorkProgram(Request $request, OsisWorkProgram $program)
    {
        $validated = $request->validate([
            'budget_actual' => 'required|numeric|min:0',
            'actual_date' => 'nullable|date',
            'status' => 'required|in:planned,in_progress,completed,cancelled',
            'report' => 'nullable|string',
            'photos' => 'nullable|array',
            'photos.*' => 'required|image|mimes:jpg,jpeg,png|max:4096',
        ]);

        $photoPaths = $program->photo_paths ?? [];
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $photoPaths[] = $photo->store('osis/work_programs', 'public');
            }
        }

        $program->update([
            'budget_actual' => $validated['budget_actual'],
            'actual_date' => $validated['actual_date'],
            'status' => $validated['status'],
            'report' => $validated['report'],
            'photo_paths' => $photoPaths,
        ]);

        return back()->with('success', 'Realisasi program kerja berhasil disimpan.');
    }

    public function elections()
    {
        $activeYear = AcademicYear::where('is_active', true)->first();
        $elections = OsisElection::where('academic_year_id', $activeYear->id ?? 0)->get();

        return view('extracurricular.osis.election.index', compact('elections', 'activeYear'));
    }

    public function storeElection(Request $request)
    {
        $activeYear = AcademicYear::where('is_active', true)->first();
        if (! $activeYear) {
            return back()->with('error', 'Tidak ada tahun ajaran aktif.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'voting_start' => 'required|date',
            'voting_end' => 'required|date|after_or_equal:voting_start',
        ]);

        OsisElection::create(array_merge($validated, [
            'academic_year_id' => $activeYear->id,
            'status' => 'upcoming',
        ]));

        return back()->with('success', 'Pemilihan Ketua OSIS baru berhasil didaftarkan.');
    }

    public function candidates(OsisElection $election)
    {
        $election->load('candidates.student.user');
        $students = Student::with(['user', 'classroom'])->where('status', 'active')->get();

        return view('extracurricular.osis.election.candidates', compact('election', 'students'));
    }

    public function storeCandidate(Request $request, OsisElection $election)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'candidate_number' => 'required|integer|min:1',
            'vision' => 'nullable|string',
            'mission' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:4096',
        ]);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('osis/candidates', 'public');
        }

        OsisCandidate::create([
            'election_id' => $election->id,
            'student_id' => $validated['student_id'],
            'candidate_number' => $validated['candidate_number'],
            'vision' => $validated['vision'],
            'mission' => $validated['mission'],
            'photo_path' => $photoPath,
            'vote_count' => 0,
        ]);

        return back()->with('success', 'Pasangan calon kandidat berhasil ditambahkan.');
    }

    public function votingPage(OsisElection $election)
    {
        $election->load('candidates.student.user');

        $user = auth()->user();
        $student = $user->student;
        $hasVoted = false;

        if ($student) {
            $hasVoted = OsisVote::where('election_id', $election->id)
                ->where('voter_id', $student->id)
                ->exists();
        }

        return view('extracurricular.osis.election.voting', compact('election', 'hasVoted'));
    }

    public function castVote(Request $request, OsisElection $election)
    {
        $user = auth()->user();
        $student = $user->student;

        if (! $student) {
            return back()->with('error', 'Hanya siswa yang berhak memberikan suara.');
        }

        $request->validate([
            'candidate_id' => 'required|exists:osis_candidates,id',
        ]);

        // Prevent double vote
        $exists = OsisVote::where('election_id', $election->id)
            ->where('voter_id', $student->id)
            ->exists();

        if ($exists) {
            return back()->with('error', 'Anda sudah memberikan suara pada pemilihan ini.');
        }

        try {
            DB::beginTransaction();

            OsisVote::create([
                'election_id' => $election->id,
                'voter_id' => $student->id,
                'candidate_id' => $request->candidate_id,
            ]);

            $candidate = OsisCandidate::findOrFail($request->candidate_id);
            $candidate->increment('vote_count');

            DB::commit();

            return back()->with('success', 'Suara Anda berhasil dikirim ke bilik suara digital.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal memproses suara: '.$e->getMessage());
        }
    }

    public function electionResults(OsisElection $election)
    {
        $election->load('candidates.student.user');
        $totalVotes = $election->votes()->count();

        return view('extracurricular.osis.election.results', compact('election', 'totalVotes'));
    }
}
