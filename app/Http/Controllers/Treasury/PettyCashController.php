<?php
namespace App\Http\Controllers\Treasury;
use App\Http\Controllers\Controller;
use App\Models\{PettyCashFund, PettyCashTransaction};
use Illuminate\Http\Request;

class PettyCashController extends Controller {
    public function index() {
        $funds = PettyCashFund::with('transactions')->get();
        return view('treasury.petty-cash.index', compact('funds'));
    }
    public function create() {
        $funds = PettyCashFund::all();
        return view('treasury.petty-cash.create', compact('funds'));
    }
    public function store(Request $request) {
        $validated = $request->validate([
            'fund_id' => 'required',
            'type' => 'required',
            'amount' => 'required|numeric',
            'description' => 'required',
            'transaction_date' => 'required|date',
            'category' => 'required'
        ]);
        $validated['requested_by'] = auth()->id() ?? 1;
        PettyCashTransaction::create($validated);
        return redirect()->route('treasury.petty-cash.index');
    }
    public function show($id) {
        $fund = PettyCashFund::findOrFail($id);
        return view('treasury.petty-cash.show', compact('fund'));
    }
    public function approve($transactionId) {
        $tx = PettyCashTransaction::findOrFail($transactionId);
        $tx->update(['approval_status' => 'approved', 'approved_by' => auth()->id()]);
        return back();
    }
    public function reject(Request $request, $transactionId) {
        $tx = PettyCashTransaction::findOrFail($transactionId);
        $tx->update(['approval_status' => 'rejected', 'rejection_reason' => $request->reason]);
        return back();
    }
}