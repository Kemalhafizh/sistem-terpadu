<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\WadiahAccount;
use App\Models\WadiahTransaction;
use App\Services\WadiahService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;

class WadiahController extends Controller
{
    protected $wadiahService;

    public function __construct(WadiahService $wadiahService)
    {
        $this->wadiahService = $wadiahService;
    }

    /**
     * Show general savings dashboard with statistics and recent activity.
     */
    public function dashboard()
    {
        $totalSavings = WadiahAccount::where('is_active', true)->sum('balance');
        $activeAccountsCount = WadiahAccount::where('is_active', true)->count();
        $totalTransactionsCount = WadiahTransaction::count();

        // Recent transactions
        $recentTransactions = WadiahTransaction::with(['account.student.user', 'processedBy'])
            ->latest()
            ->take(10)
            ->get();

        // Chart data: past 7 days deposits vs withdrawals
        $chartLabels = [];
        $depositsData = [];
        $withdrawalsData = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $chartLabels[] = $date->translatedFormat('d M');

            $depositsData[] = (float) WadiahTransaction::where('type', 'deposit')
                ->whereDate('created_at', $date)
                ->sum('amount');

            $withdrawalsData[] = (float) WadiahTransaction::where('type', 'withdrawal')
                ->whereDate('created_at', $date)
                ->sum('amount');
        }

        $chartData = [
            'labels' => $chartLabels,
            'deposits' => $depositsData,
            'withdrawals' => $withdrawalsData,
        ];

        return view('finance.wadiah.dashboard', compact(
            'totalSavings',
            'activeAccountsCount',
            'totalTransactionsCount',
            'recentTransactions',
            'chartData'
        ));
    }

    /**
     * List all wadiah accounts (Admin/TU).
     */
    public function index(Request $request)
    {
        $search = $request->get('search');

        $accounts = WadiahAccount::with(['student.user', 'student.classroom'])
            ->when($search, function ($query, $search) {
                $query->where('account_number', 'like', "%{$search}%")
                    ->orWhereHas('student.user', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('student', function ($q) use ($search) {
                        $q->where('nis', 'like', "%{$search}%");
                    });
            })
            ->latest()
            ->paginate(15);

        return view('finance.wadiah.index', compact('accounts', 'search'));
    }

    /**
     * Show detailed account statement & transactions.
     */
    public function showAccount($id)
    {
        $account = WadiahAccount::with(['student.user', 'student.classroom'])->findOrFail($id);

        $transactions = $account->transactions()
            ->with('processedBy')
            ->latest()
            ->paginate(15);

        return view('finance.wadiah.show', compact('account', 'transactions'));
    }

    /**
     * Show deposit form.
     */
    public function depositForm($accountId)
    {
        $account = WadiahAccount::with('student.user')->findOrFail($accountId);

        return view('finance.wadiah.deposit', compact('account'));
    }

    /**
     * Store deposit transaction.
     */
    public function storeDeposit(Request $request)
    {
        $request->validate([
            'wadiah_account_id' => 'required|exists:wadiah_accounts,id',
            'amount' => 'required|numeric|min:1000',
            'description' => 'nullable|string|max:255',
        ]);

        $account = WadiahAccount::findOrFail($request->wadiah_account_id);

        try {
            $this->wadiahService->deposit(
                $account,
                (float) $request->amount,
                auth()->id(),
                $request->description
            );

            return redirect()
                ->route('admin.finance.wadiah.show', $account->id)
                ->with('success', 'Setoran tunai berhasil disimpan.');
        } catch (Exception $e) {
            return back()->with('error', 'Gagal memproses setoran: '.$e->getMessage())->withInput();
        }
    }

    /**
     * Show withdraw form.
     */
    public function withdrawForm($accountId)
    {
        $account = WadiahAccount::with('student.user')->findOrFail($accountId);

        return view('finance.wadiah.withdraw', compact('account'));
    }

    /**
     * Store withdraw transaction.
     */
    public function storeWithdraw(Request $request)
    {
        $request->validate([
            'wadiah_account_id' => 'required|exists:wadiah_accounts,id',
            'amount' => 'required|numeric|min:1000',
            'description' => 'nullable|string|max:255',
        ]);

        $account = WadiahAccount::findOrFail($request->wadiah_account_id);

        try {
            $this->wadiahService->withdraw(
                $account,
                (float) $request->amount,
                auth()->id(),
                $request->description
            );

            return redirect()
                ->route('admin.finance.wadiah.show', $account->id)
                ->with('success', 'Penarikan tunai berhasil disimpan.');
        } catch (Exception $e) {
            return back()->with('error', 'Gagal memproses penarikan: '.$e->getMessage())->withInput();
        }
    }

    /**
     * Set daily spending limit for a student's account.
     */
    public function setLimit($accountId, Request $request)
    {
        $request->validate([
            'daily_spending_limit' => 'required|numeric|min:0',
        ]);

        $account = WadiahAccount::findOrFail($accountId);

        // Authorization check: only Admin, Staf TU, or Parent of this student can change limit
        $user = auth()->user();
        if ($user->role === 'parent' && $account->student->parent_id !== $user->id) {
            abort(403, 'Aksi tidak diotorisasi.');
        }

        $account->update([
            'daily_spending_limit' => $request->daily_spending_limit,
        ]);

        return back()->with('success', 'Batasan limit jajan harian anak berhasil diperbarui.');
    }

    /**
     * Export account passbook/statement to PDF.
     */
    public function exportPassbook($accountId)
    {
        $account = WadiahAccount::with(['student.user', 'student.classroom'])->findOrFail($accountId);
        $transactions = $account->transactions()->with('processedBy')->oldest()->get();

        $pdf = Pdf::loadView('finance.wadiah.passbook_pdf', compact('account', 'transactions'))
            ->setPaper('a4', 'portrait');

        return $pdf->download("Mutasi_Wadiah_{$account->account_number}.pdf");
    }
}
