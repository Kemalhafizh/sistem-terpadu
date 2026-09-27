<?php
namespace App\Http\Controllers\Treasury;
use App\Http\Controllers\Controller;
use App\Models\{PettyCashFund, PettyCashTransaction, User};
use App\Services\Treasury\PettyCashService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PettyCashController extends Controller {
    /** Roles that may hold a petty cash fund (students, parents and canteen merchants may not). */
    private const NON_STAFF_ROLES = ['student', 'parent', 'merchant'];

    public function index() {
        $funds = PettyCashFund::with('custodian')->withCount([
                'transactions as pending_count' => fn ($q) => $q->where('approval_status', 'pending'),
                // Approved before PettyCashService: the balance shown doesn't include them yet.
                'transactions as unapplied_count' => fn ($q) => $q->unappliedApproved(),
            ])
            ->orderByDesc('is_active')->orderBy('fund_name')->get();
        return view('treasury.petty-cash.index', compact('funds'));
    }
    public function create() {
        $funds = PettyCashFund::where('is_active', true)->orderBy('fund_name')->get();
        return view('treasury.petty-cash.create', compact('funds'));
    }
    public function store(Request $request) {
        $validated = $request->validate([
            'fund_id' => ['required', Rule::exists('petty_cash_funds', 'id')->where('is_active', true)],
            'type' => ['required', Rule::in(array_keys(PettyCashTransaction::TYPE_LABELS))],
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string',
            'transaction_date' => 'required|date|before_or_equal:today',
            'category' => ['required', Rule::in(array_keys(PettyCashTransaction::CATEGORY_LABELS))],
            'receipt' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);
        unset($validated['receipt']);
        if ($request->hasFile('receipt')) {
            $validated['receipt_path'] = $request->file('receipt')->store('petty-cash-receipts', 'local');
        }
        $validated['requested_by'] = $request->user()->id;
        $validated['approval_status'] = 'pending';
        PettyCashTransaction::create($validated);
        return redirect()->route('treasury.petty-cash.show', $validated['fund_id'])
            ->with('success', 'Transaksi dicatat dan menunggu persetujuan. Saldo dana berubah setelah disetujui.');
    }
    public function show($id) {
        $fund = PettyCashFund::with('custodian')->findOrFail($id);
        $transactions = $fund->transactions()->with(['requester', 'approver'])
            ->orderByDesc('transaction_date')->orderByDesc('id')->get();
        $approved = $transactions->where('approval_status', 'approved');
        $totals = [
            'spent' => (float) $approved->where('type', 'pengeluaran')->sum('amount'),
            'replenished' => (float) $approved->where('type', 'pengisian_ulang')->sum('amount'),
            'pending' => (float) $transactions->where('approval_status', 'pending')->where('type', 'pengeluaran')->sum('amount'),
        ];
        return view('treasury.petty-cash.show', compact('fund', 'transactions', 'totals'));
    }
    public function approvals() {
        $pending = PettyCashTransaction::with(['fund', 'requester'])->where('approval_status', 'pending')
            ->orderBy('transaction_date')->orderBy('id')->get();
        return view('treasury.petty-cash.approval', compact('pending'));
    }
    public function approve(Request $request, $transactionId, PettyCashService $service) {
        $tx = $service->approve(PettyCashTransaction::findOrFail($transactionId), $request->user()->id);
        return back()->with('success', 'Transaksi disetujui. Saldo '.$tx->fund->fund_name.' kini Rp '.number_format($tx->fund->current_balance, 0, ',', '.').'.');
    }
    public function reject(Request $request, $transactionId, PettyCashService $service) {
        $validated = $request->validate(['reason' => 'required|string|max:500'], ['reason.required' => 'Alasan penolakan wajib diisi.']);
        $service->reject(PettyCashTransaction::findOrFail($transactionId), $request->user()->id, $validated['reason']);
        return back()->with('success', 'Transaksi ditolak.');
    }
    public function report(Request $request) {
        $filters = $request->validate([
            'month' => 'nullable|date_format:Y-m',
            'fund_id' => 'nullable|exists:petty_cash_funds,id',
        ]);
        $month = Carbon::createFromFormat('!Y-m', $filters['month'] ?? today()->format('Y-m'));
        $funds = PettyCashFund::orderBy('fund_name')->get();

        $transactions = PettyCashTransaction::with('fund')
            ->where('approval_status', 'approved')
            ->whereDate('transaction_date', '>=', $month->copy()->startOfMonth()->toDateString())
            ->whereDate('transaction_date', '<=', $month->copy()->endOfMonth()->toDateString())
            ->when($filters['fund_id'] ?? null, fn ($q, $fundId) => $q->where('fund_id', $fundId))
            ->orderBy('transaction_date')
            ->get();
        $spending = $transactions->where('type', 'pengeluaran');

        $byCategory = collect(PettyCashTransaction::CATEGORY_LABELS)
            ->map(fn ($label, $key) => [
                'label' => $label,
                'count' => $spending->where('category', $key)->count(),
                'total' => (float) $spending->where('category', $key)->sum('amount'),
            ])
            ->filter(fn ($row) => $row['count'] > 0)
            ->sortByDesc('total');
        $byFund = $spending->groupBy('fund_id')->map(fn ($rows) => [
            'fund' => $rows->first()->fund->fund_name,
            'count' => $rows->count(),
            'total' => (float) $rows->sum('amount'),
        ])->sortByDesc('total');
        $totals = [
            'spent' => (float) $spending->sum('amount'),
            'replenished' => (float) $transactions->where('type', 'pengisian_ulang')->sum('amount'),
        ];

        return view('treasury.petty-cash.report', compact('month', 'funds', 'filters', 'byCategory', 'byFund', 'totals', 'spending'));
    }
    public function createFund() {
        $custodians = $this->custodians();
        return view('treasury.petty-cash.fund-create', compact('custodians'));
    }
    public function storeFund(Request $request) {
        $validated = $request->validate([
            'fund_name' => 'required|string|max:255|unique:petty_cash_funds,fund_name',
            'custodian_id' => ['required', Rule::exists('users', 'id')->whereNotIn('role', self::NON_STAFF_ROLES)],
            'imprest_amount' => 'required|numeric|min:1',
        ]);
        // A new fund starts empty; its first approved replenishment brings it up to the imprest.
        $fund = PettyCashFund::create($validated + ['current_balance' => 0, 'is_active' => true]);
        return redirect()->route('treasury.petty-cash.show', $fund->id)
            ->with('success', 'Dana kas kecil dibuat dengan saldo Rp 0. Ajukan pengisian ulang untuk mengisinya.');
    }

    private function custodians() {
        return User::whereNotIn('role', self::NON_STAFF_ROLES)->orderBy('name')->get(['id', 'name', 'role']);
    }
}
