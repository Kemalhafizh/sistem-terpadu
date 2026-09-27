<?php
namespace App\Http\Controllers\Treasury;
use App\Http\Controllers\Controller;
use App\Models\{BankAccount, BankStatement, BankTransaction};
use App\Services\Treasury\TreasuryReconciliationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

class ReconciliationController extends Controller {
    public function index() {
        $statements = BankStatement::with('account')->latest('period_end')->get();
        return view('treasury.reconciliation.index', compact('statements'));
    }
    public function importForm() {
        $accounts = BankAccount::all();
        return view('treasury.reconciliation.import', compact('accounts'));
    }
    public function processImport(Request $request, TreasuryReconciliationService $service) {
        $validated = $request->validate([
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'file' => 'required|file|mimes:csv,txt|max:5120',
        ]);

        $path = $request->file('file')->store('bank-statements', 'local');
        try {
            $statement = $service->importBankStatement((int) $validated['bank_account_id'], $path, $request->user()->id);
        } catch (InvalidArgumentException $e) {
            Storage::disk('local')->delete($path);
            return back()->withErrors(['file' => $e->getMessage()])->withInput();
        }

        $result = $service->autoReconcile($statement->id);
        return redirect()->route('treasury.reconciliation.review', $statement->id)
            ->with('success', $this->resultMessage($result));
    }
    public function review($statementId) {
        $statement = BankStatement::with(['account', 'transactions.matchedPayment'])->findOrFail($statementId);
        return view('treasury.reconciliation.review', compact('statement'));
    }
    public function discrepancies(Request $request) {
        $accountId = $request->validate(['bank_account_id' => 'nullable|exists:bank_accounts,id'])['bank_account_id'] ?? null;
        $transactions = BankTransaction::with(['statement.account', 'matchedBilling'])
            ->whereIn('match_status', ['unmatched', 'disputed'])
            ->when($accountId, fn ($q) => $q->whereHas('statement', fn ($s) => $s->where('bank_account_id', $accountId)))
            ->orderByDesc('transaction_date')->orderBy('id')
            ->get();
        $totals = [
            'credit' => (float) $transactions->sum('credit'),
            'debit' => (float) $transactions->sum('debit'),
            'disputed' => $transactions->where('match_status', 'disputed')->count(),
        ];
        $accounts = BankAccount::orderBy('bank_name')->get();
        return view('treasury.reconciliation.discrepancies', compact('transactions', 'totals', 'accounts', 'accountId'));
    }
    public function autoReconcile($statementId, TreasuryReconciliationService $service) {
        $result = $service->autoReconcile((int) $statementId);
        return back()->with('success', $this->resultMessage($result));
    }
    public function manualMatch(Request $request, TreasuryReconciliationService $service) {
        $validated = $request->validate([
            'transaction_id' => 'required|exists:bank_transactions,id',
            'billing_id' => 'required|exists:billings,id',
        ]);
        $service->manualMatch((int) $validated['transaction_id'], (int) $validated['billing_id'], $request->user()->id);
        return back()->with('success', 'Transaksi dicocokkan manual dengan tagihan #'.$validated['billing_id'].'.');
    }

    private function resultMessage(array $result): string {
        return "Rekonsiliasi otomatis: {$result['matched']} dari {$result['total']} transaksi cocok, {$result['unmatched']} belum cocok.";
    }
}
