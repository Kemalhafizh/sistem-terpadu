<?php
namespace App\Http\Controllers\Treasury;
use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use Illuminate\Http\Request;

class BankAccountController extends Controller {
    public function index() {
        $accounts = BankAccount::all();
        return view('treasury.accounts.index', compact('accounts'));
    }
    public function create() {
        return view('treasury.accounts.create');
    }
    public function store(Request $request) {
        $validated = $request->validate([
            'bank_name' => 'required',
            'account_number' => 'required',
            'account_name' => 'required',
            'account_type' => 'required'
        ]);
        BankAccount::create($validated);
        return redirect()->route('treasury.accounts.index');
    }
    public function show($id) {
        $account = BankAccount::findOrFail($id);
        return view('treasury.accounts.show', compact('account'));
    }
}