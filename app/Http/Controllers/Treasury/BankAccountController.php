<?php

namespace App\Http\Controllers\Treasury;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\VendorPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class BankAccountController extends Controller
{
    public function index()
    {
        $accounts = BankAccount::with('latestPosition')->orderByDesc('is_active')->orderByDesc('is_primary')->orderBy('bank_name')->get();

        return view('treasury.accounts.index', compact('accounts'));
    }

    public function create()
    {
        return view('treasury.accounts.create', ['account' => new BankAccount(['is_active' => true])]);
    }

    public function store(Request $request)
    {
        $account = DB::transaction(fn () => $this->save(new BankAccount, $this->validated($request)));

        return redirect()->route('treasury.accounts.show', $account)->with('success', 'Rekening bank ditambahkan.');
    }

    public function show(BankAccount $account)
    {
        $account->load('latestPosition');
        $statements = $account->statements()->latest('period_end')->take(10)->get();
        $positions = $account->dailyPositions()->orderByDesc('position_date')->take(14)->get();
        $vendorPayments = VendorPayment::where('bank_account_id', $account->id)->orderByDesc('due_date')->take(10)->get();

        return view('treasury.accounts.show', compact('account', 'statements', 'positions', 'vendorPayments'));
    }

    public function edit(BankAccount $account)
    {
        return view('treasury.accounts.edit', compact('account'));
    }

    public function update(Request $request, BankAccount $account)
    {
        DB::transaction(fn () => $this->save($account, $this->validated($request, $account)));

        return redirect()->route('treasury.accounts.show', $account)->with('success', 'Rekening bank diperbarui.');
    }

    /** Accounts with history are only deactivated: deleting would cascade to statements and cash positions. */
    public function destroy(BankAccount $account)
    {
        $history = array_filter([
            'rekening koran' => $account->statements()->count(),
            'posisi kas harian' => $account->dailyPositions()->count(),
            'pembayaran vendor' => VendorPayment::where('bank_account_id', $account->id)->count(),
        ]);
        if ($history) {
            $used = collect($history)->map(fn ($n, $what) => "{$n} {$what}")->join(', ', ' dan ');

            return back()->withErrors(['account' => "Rekening tidak dapat dihapus karena memiliki {$used}. Nonaktifkan rekening melalui menu Ubah."]);
        }
        $account->delete();

        return redirect()->route('treasury.accounts.index')->with('success', 'Rekening bank dihapus.');
    }

    private function validated(Request $request, ?BankAccount $account = null): array
    {
        $validated = $request->validate([
            'bank_name' => 'required|string|max:100',
            'account_number' => ['required', 'string', 'max:50', Rule::unique('bank_accounts')->where('bank_name', $request->input('bank_name'))->ignore($account?->id)],
            'account_name' => 'required|string|max:255',
            'account_type' => 'required|in:giro,tabungan,virtual_account',
            'current_balance' => 'nullable|numeric|min:0',
        ], [
            'account_number.unique' => 'Nomor rekening ini sudah terdaftar untuk bank yang sama.',
        ]);
        if (($validated['current_balance'] ?? null) === null) {
            unset($validated['current_balance']);
        }
        $validated['is_primary'] = $request->boolean('is_primary');
        $validated['is_active'] = $request->boolean('is_active');
        if ($validated['is_primary'] && ! $validated['is_active']) {
            throw ValidationException::withMessages(['is_primary' => 'Rekening utama harus aktif.']);
        }

        return $validated;
    }

    private function save(BankAccount $account, array $data): BankAccount
    {
        if ($data['is_primary']) {
            BankAccount::where('is_primary', true)->when($account->exists, fn ($q) => $q->whereKeyNot($account->id))->update(['is_primary' => false]);
        }
        $account->fill($data)->save();

        return $account;
    }
}
