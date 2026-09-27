<?php

namespace App\Http\Controllers\Treasury;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\DailyCashPosition;
use App\Services\Treasury\TreasuryReconciliationService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TreasuryPositionController extends Controller
{
    /** Positions are recomputed from the imported bank transactions each time the page is opened. */
    public function dashboard(Request $request, TreasuryReconciliationService $service)
    {
        $date = Carbon::parse($request->validate(['date' => 'nullable|date'])['date'] ?? now())->toDateString();
        $positions = BankAccount::where('is_active', true)->orderBy('bank_name')->get()
            ->map(fn (BankAccount $account) => $service->calculateTreasuryPosition($account->id, $date)->setRelation('account', $account));

        return view('treasury.position.dashboard', compact('positions', 'date'));
    }

    public function trend()
    {
        $positions = DailyCashPosition::with('account')
            ->whereDate('position_date', '>=', now()->subDays(30)->toDateString())
            ->orderBy('position_date')
            ->get()
            ->groupBy('bank_account_id');

        return view('treasury.position.trend', compact('positions'));
    }
}
