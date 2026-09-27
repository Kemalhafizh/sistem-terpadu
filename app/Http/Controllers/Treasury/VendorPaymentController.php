<?php
namespace App\Http\Controllers\Treasury;
use App\Http\Controllers\Controller;
use App\Models\{BankAccount, VendorPayment};
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class VendorPaymentController extends Controller {
    public function index() {
        $payments = VendorPayment::with('account')->orderByRaw("CASE WHEN status IN ('scheduled', 'overdue') THEN 0 ELSE 1 END")->orderBy('due_date')->get();
        $open = $payments->filter->isOpen();
        $summary = [
            'scheduled' => (float) $open->filter(fn ($p) => $p->effectiveStatus() === 'scheduled')->sum('amount'),
            'overdue' => (float) $open->filter(fn ($p) => $p->effectiveStatus() === 'overdue')->sum('amount'),
            'paid_this_month' => (float) $payments->where('status', 'paid')
                ->filter(fn ($p) => $p->paid_date && $p->paid_date->isSameMonth(today()))->sum('amount'),
        ];
        return view('treasury.vendor.index', compact('payments', 'summary'));
    }
    public function create() {
        $accounts = BankAccount::where('is_active', true)->orderBy('bank_name')->get();
        return view('treasury.vendor.create', compact('accounts'));
    }
    public function store(Request $request) {
        $validated = $request->validate([
            'vendor_name' => 'required|string|max:255',
            'invoice_number' => 'nullable|string|max:100',
            'amount' => 'required|numeric|min:0.01',
            'due_date' => 'required|date',
            'description' => 'required|string',
            'payment_method' => ['required', Rule::in(array_keys(VendorPayment::METHOD_LABELS))],
            'bank_account_id' => ['nullable', Rule::exists('bank_accounts', 'id')->where('is_active', true)],
        ]);
        $validated['created_by'] = $request->user()->id;
        $validated['status'] = 'scheduled';
        $payment = VendorPayment::create($validated);
        return redirect()->route('treasury.vendor.show', $payment)->with('success', 'Pembayaran vendor dijadwalkan.');
    }
    public function show(VendorPayment $vendor) {
        $vendor->load(['account', 'creator', 'approver']);
        $accounts = BankAccount::where('is_active', true)->orderBy('bank_name')->get();
        return view('treasury.vendor.show', ['payment' => $vendor, 'accounts' => $accounts]);
    }
    public function markPaid(Request $request, VendorPayment $vendor) {
        if (! $vendor->isOpen()) {
            return back()->withErrors(['status' => 'Pembayaran ini sudah '.strtolower($vendor->statusLabel()).'.']);
        }
        $validated = $request->validate([
            'paid_date' => 'required|date|before_or_equal:today',
            'payment_method' => ['required', Rule::in(array_keys(VendorPayment::METHOD_LABELS))],
            'bank_account_id' => ['nullable', 'required_unless:payment_method,cash', Rule::exists('bank_accounts', 'id')->where('is_active', true)],
            'proof' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ], [
            'bank_account_id.required_unless' => 'Pilih rekening sumber untuk pembayaran non-tunai.',
        ]);

        $vendor->update([
            'status' => 'paid',
            'paid_date' => $validated['paid_date'],
            'payment_method' => $validated['payment_method'],
            'bank_account_id' => $validated['payment_method'] === 'cash' ? null : $validated['bank_account_id'],
            'proof_file_path' => $request->hasFile('proof') ? $request->file('proof')->store('vendor-proofs', 'local') : $vendor->proof_file_path,
            'approved_by' => $request->user()->id,
        ]);
        return redirect()->route('treasury.vendor.show', $vendor)->with('success', 'Pembayaran vendor dicatat lunas.');
    }
    public function cancel(Request $request, VendorPayment $vendor) {
        if (! $vendor->isOpen()) {
            return back()->withErrors(['status' => 'Hanya pembayaran yang belum dibayar yang dapat dibatalkan.']);
        }
        $vendor->update(['status' => 'cancelled', 'approved_by' => $request->user()->id]);
        return redirect()->route('treasury.vendor.show', $vendor)->with('success', 'Pembayaran vendor dibatalkan.');
    }
    public function proof(VendorPayment $vendor) {
        abort_unless($vendor->proof_file_path && Storage::disk('local')->exists($vendor->proof_file_path), 404);
        return Storage::disk('local')->download($vendor->proof_file_path);
    }
    public function calendar(Request $request) {
        $month = $request->validate(['month' => 'nullable|date_format:Y-m'])['month'] ?? today()->format('Y-m');
        $start = Carbon::createFromFormat('!Y-m', $month)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $payments = VendorPayment::whereDate('due_date', '>=', $start->toDateString())
            ->whereDate('due_date', '<=', $end->toDateString())
            ->orderBy('due_date')->orderBy('vendor_name')
            ->get();
        $byDay = $payments->groupBy(fn ($p) => $p->due_date->toDateString());

        $weeks = collect(CarbonPeriod::create($start->copy()->startOfWeek(Carbon::MONDAY), $end->copy()->endOfWeek(Carbon::SUNDAY)))->chunk(7);
        $totals = [
            'due' => (float) $payments->filter->isOpen()->sum('amount'),
            'paid' => (float) $payments->where('status', 'paid')->sum('amount'),
            'count' => $payments->where('status', '!=', 'cancelled')->count(),
        ];

        return view('treasury.vendor.calendar', compact('start', 'weeks', 'byDay', 'totals'));
    }
}
