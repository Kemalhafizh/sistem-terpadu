<?php
namespace App\Http\Controllers\Treasury;
use App\Http\Controllers\Controller;
use App\Models\{BankStatement, BankAccount};
use App\Services\Treasury\TreasuryReconciliationService;
use Illuminate\Http\Request;

class ReconciliationController extends Controller {
    public function index() {
        $statements = BankStatement::with('account')->get();
        return view('treasury.reconciliation.index', compact('statements'));
    }
    public function importForm() {
        $accounts = BankAccount::all();
        return view('treasury.reconciliation.import', compact('accounts'));
    }
    public function processImport(Request $request, TreasuryReconciliationService $service) {
        $service->importBankStatement($request->bank_account_id, 'dummy/path.csv');
        return redirect()->route('treasury.reconciliation.index');
    }
    public function review($statementId) {
        $statement = BankStatement::with('transactions')->findOrFail($statementId);
        return view('treasury.reconciliation.review', compact('statement'));
    }
    public function manualMatch(Request $request, TreasuryReconciliationService $service) {
        $service->manualMatch($request->transaction_id, $request->billing_id);
        return back();
    }
}