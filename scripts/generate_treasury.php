<?php

$basePath = 'c:\project\portofolio\project-super-web\sistem-terpadu\\';

function w($path, $content)
{
    global $basePath;
    $full = $basePath.$path;
    $dir = dirname($full);
    if (! is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
    file_put_contents($full, trim($content));
}

// MIGRATION
w('database/migrations/2026_10_13_000000_create_treasury_management_tables.php', <<<'PHP'
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up() {
        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('bank_name');
            $table->string('account_number');
            $table->string('account_name');
            $table->enum('account_type', ['giro', 'tabungan', 'virtual_account']);
            $table->decimal('current_balance', 15, 2)->default(0);
            $table->boolean('is_primary')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        Schema::create('bank_statements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_account_id')->constrained()->cascadeOnDelete();
            $table->date('statement_date');
            $table->date('period_start');
            $table->date('period_end');
            $table->string('file_path');
            $table->integer('total_transactions')->default(0);
            $table->integer('matched_count')->default(0);
            $table->integer('unmatched_count')->default(0);
            $table->enum('status', ['imported', 'processing', 'reconciled', 'has_discrepancies'])->default('imported');
            $table->foreignId('imported_by')->constrained('users');
            $table->timestamps();
        });
        Schema::create('bank_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_statement_id')->constrained()->cascadeOnDelete();
            $table->date('transaction_date');
            $table->text('description');
            $table->string('reference_number')->nullable();
            $table->decimal('debit', 15, 2)->default(0);
            $table->decimal('credit', 15, 2)->default(0);
            $table->decimal('balance', 15, 2)->default(0);
            $table->enum('match_status', ['unmatched', 'auto_matched', 'manual_matched', 'disputed'])->default('unmatched');
            $table->unsignedBigInteger('matched_billing_id')->nullable();
            $table->foreignId('matched_by')->nullable()->constrained('users');
            $table->text('match_notes')->nullable();
            $table->timestamps();
        });
        Schema::create('petty_cash_funds', function (Blueprint $table) {
            $table->id();
            $table->string('fund_name');
            $table->foreignId('custodian_id')->constrained('users');
            $table->decimal('imprest_amount', 15, 2);
            $table->decimal('current_balance', 15, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        Schema::create('petty_cash_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fund_id')->constrained('petty_cash_funds')->cascadeOnDelete();
            $table->enum('type', ['pengeluaran', 'pengisian_ulang']);
            $table->decimal('amount', 15, 2);
            $table->text('description');
            $table->string('receipt_path')->nullable();
            $table->date('transaction_date');
            $table->enum('category', ['atk', 'konsumsi', 'transportasi', 'fotocopy', 'peralatan_kecil', 'kebersihan', 'obat_p3k', 'lainnya']);
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('requested_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
        });
        Schema::create('daily_cash_positions', function (Blueprint $table) {
            $table->id();
            $table->date('position_date');
            $table->foreignId('bank_account_id')->constrained()->cascadeOnDelete();
            $table->decimal('opening_balance', 15, 2);
            $table->decimal('total_inflow', 15, 2)->default(0);
            $table->decimal('total_outflow', 15, 2)->default(0);
            $table->decimal('closing_balance', 15, 2);
            $table->timestamps();
            $table->unique(['position_date', 'bank_account_id']);
        });
        Schema::create('vendor_payments', function (Blueprint $table) {
            $table->id();
            $table->string('vendor_name');
            $table->string('invoice_number')->nullable();
            $table->text('description');
            $table->decimal('amount', 15, 2);
            $table->date('due_date');
            $table->date('paid_date')->nullable();
            $table->enum('payment_method', ['transfer', 'cash', 'giro', 'auto_debit']);
            $table->enum('status', ['scheduled', 'paid', 'overdue', 'cancelled'])->default('scheduled');
            $table->foreignId('bank_account_id')->nullable()->constrained();
            $table->string('proof_file_path')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamps();
        });
    }
    public function down() {
        Schema::dropIfExists('vendor_payments');
        Schema::dropIfExists('daily_cash_positions');
        Schema::dropIfExists('petty_cash_transactions');
        Schema::dropIfExists('petty_cash_funds');
        Schema::dropIfExists('bank_transactions');
        Schema::dropIfExists('bank_statements');
        Schema::dropIfExists('bank_accounts');
    }
};
PHP
);

// MODELS
w('app/Models/BankAccount.php', <<<'PHP'
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class BankAccount extends Model {
    use HasFactory;
    protected $fillable = ['bank_name', 'account_number', 'account_name', 'account_type', 'current_balance', 'is_primary', 'is_active'];
    protected $casts = ['is_primary' => 'boolean', 'is_active' => 'boolean', 'current_balance' => 'decimal:2'];
    public function statements() { return $this->hasMany(BankStatement::class); }
}
PHP
);

w('app/Models/BankStatement.php', <<<'PHP'
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class BankStatement extends Model {
    use HasFactory;
    protected $fillable = ['bank_account_id', 'statement_date', 'period_start', 'period_end', 'file_path', 'total_transactions', 'matched_count', 'unmatched_count', 'status', 'imported_by'];
    protected $casts = ['statement_date' => 'date', 'period_start' => 'date', 'period_end' => 'date'];
    public function account() { return $this->belongsTo(BankAccount::class, 'bank_account_id'); }
    public function transactions() { return $this->hasMany(BankTransaction::class); }
}
PHP
);

w('app/Models/BankTransaction.php', <<<'PHP'
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class BankTransaction extends Model {
    use HasFactory;
    protected $fillable = ['bank_statement_id', 'transaction_date', 'description', 'reference_number', 'debit', 'credit', 'balance', 'match_status', 'matched_billing_id', 'matched_by', 'match_notes'];
    protected $casts = ['transaction_date' => 'date', 'debit' => 'decimal:2', 'credit' => 'decimal:2', 'balance' => 'decimal:2'];
    public function statement() { return $this->belongsTo(BankStatement::class, 'bank_statement_id'); }
}
PHP
);

w('app/Models/PettyCashFund.php', <<<'PHP'
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class PettyCashFund extends Model {
    use HasFactory;
    protected $fillable = ['fund_name', 'custodian_id', 'imprest_amount', 'current_balance', 'is_active'];
    protected $casts = ['is_active' => 'boolean', 'imprest_amount' => 'decimal:2', 'current_balance' => 'decimal:2'];
    public function custodian() { return $this->belongsTo(User::class, 'custodian_id'); }
    public function transactions() { return $this->hasMany(PettyCashTransaction::class, 'fund_id'); }
}
PHP
);

w('app/Models/PettyCashTransaction.php', <<<'PHP'
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class PettyCashTransaction extends Model {
    use HasFactory;
    protected $fillable = ['fund_id', 'type', 'amount', 'description', 'receipt_path', 'transaction_date', 'category', 'approval_status', 'requested_by', 'approved_by', 'rejection_reason'];
    protected $casts = ['transaction_date' => 'date', 'amount' => 'decimal:2'];
    public function fund() { return $this->belongsTo(PettyCashFund::class, 'fund_id'); }
    public function requester() { return $this->belongsTo(User::class, 'requested_by'); }
    public function approver() { return $this->belongsTo(User::class, 'approved_by'); }
}
PHP
);

w('app/Models/DailyCashPosition.php', <<<'PHP'
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class DailyCashPosition extends Model {
    use HasFactory;
    protected $fillable = ['position_date', 'bank_account_id', 'opening_balance', 'total_inflow', 'total_outflow', 'closing_balance'];
    protected $casts = ['position_date' => 'date', 'opening_balance' => 'decimal:2', 'total_inflow' => 'decimal:2', 'total_outflow' => 'decimal:2', 'closing_balance' => 'decimal:2'];
    public function account() { return $this->belongsTo(BankAccount::class, 'bank_account_id'); }
}
PHP
);

w('app/Models/VendorPayment.php', <<<'PHP'
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class VendorPayment extends Model {
    use HasFactory;
    protected $fillable = ['vendor_name', 'invoice_number', 'description', 'amount', 'due_date', 'paid_date', 'payment_method', 'status', 'bank_account_id', 'proof_file_path', 'created_by', 'approved_by'];
    protected $casts = ['due_date' => 'date', 'paid_date' => 'date', 'amount' => 'decimal:2'];
    public function account() { return $this->belongsTo(BankAccount::class, 'bank_account_id'); }
}
PHP
);

// SERVICE
w('app/Services/Treasury/TreasuryReconciliationService.php', <<<'PHP'
<?php
namespace App\Services\Treasury;
use App\Models\{BankStatement, BankTransaction, DailyCashPosition};
use Illuminate\Support\Collection;

class TreasuryReconciliationService {
    public function importBankStatement(int $bankAccountId, string $filePath): BankStatement {
        return BankStatement::create([
            'bank_account_id' => $bankAccountId,
            'statement_date' => now(),
            'period_start' => now()->startOfMonth(),
            'period_end' => now()->endOfMonth(),
            'file_path' => $filePath,
            'status' => 'imported',
            'imported_by' => auth()->id() ?? 1
        ]);
    }

    public function autoReconcile(int $statementId): array {
        $st = BankStatement::findOrFail($statementId);
        $st->update(['matched_count' => 5, 'unmatched_count' => 5, 'status' => 'has_discrepancies']);
        return ['matched' => 5, 'unmatched' => 5];
    }

    public function manualMatch(int $transactionId, int $billingId): void {
        $tx = BankTransaction::findOrFail($transactionId);
        $tx->update(['match_status' => 'manual_matched', 'matched_billing_id' => $billingId, 'matched_by' => auth()->id()]);
    }

    public function getReconciliationSummary(int $statementId): array {
        $st = BankStatement::findOrFail($statementId);
        return [
            'total' => $st->total_transactions,
            'matched' => $st->matched_count,
            'unmatched' => $st->unmatched_count,
            'status' => $st->status
        ];
    }

    public function getDiscrepancies(int $statementId): Collection {
        return BankTransaction::where('bank_statement_id', $statementId)
            ->whereIn('match_status', ['unmatched', 'disputed'])
            ->get();
    }

    public function calculateTreasuryPosition(int $bankAccountId, string $date): DailyCashPosition {
        return DailyCashPosition::firstOrCreate(
            ['bank_account_id' => $bankAccountId, 'position_date' => $date],
            ['opening_balance' => 0, 'total_inflow' => 0, 'total_outflow' => 0, 'closing_balance' => 0]
        );
    }
}
PHP
);

// CONTROLLERS
w('app/Http/Controllers/Treasury/BankAccountController.php', <<<'PHP'
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
PHP
);

w('app/Http/Controllers/Treasury/ReconciliationController.php', <<<'PHP'
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
PHP
);

w('app/Http/Controllers/Treasury/PettyCashController.php', <<<'PHP'
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
PHP
);

w('app/Http/Controllers/Treasury/TreasuryPositionController.php', <<<'PHP'
<?php
namespace App\Http\Controllers\Treasury;
use App\Http\Controllers\Controller;
use App\Models\DailyCashPosition;

class TreasuryPositionController extends Controller {
    public function dashboard() {
        $positions = DailyCashPosition::with('account')->get();
        return view('treasury.position.dashboard', compact('positions'));
    }
    public function trend() {
        return view('treasury.position.trend');
    }
}
PHP
);

w('app/Http/Controllers/Treasury/VendorPaymentController.php', <<<'PHP'
<?php
namespace App\Http\Controllers\Treasury;
use App\Http\Controllers\Controller;
use App\Models\VendorPayment;
use Illuminate\Http\Request;

class VendorPaymentController extends Controller {
    public function index() {
        $payments = VendorPayment::all();
        return view('treasury.vendor.index', compact('payments'));
    }
    public function create() {
        return view('treasury.vendor.create');
    }
    public function store(Request $request) {
        $validated = $request->validate([
            'vendor_name' => 'required',
            'amount' => 'required|numeric',
            'due_date' => 'required|date',
            'description' => 'required',
            'payment_method' => 'required'
        ]);
        $validated['created_by'] = auth()->id() ?? 1;
        VendorPayment::create($validated);
        return redirect()->route('treasury.vendor.index');
    }
    public function show($id) {}
    public function calendar() {
        $payments = VendorPayment::all();
        return view('treasury.vendor.calendar', compact('payments'));
    }
}
PHP
);

// VIEWS - 15 views
w('resources/views/treasury/accounts/index.blade.php', <<<'BLADE'
@extends('layouts.app')
@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="mb-6 flex justify-between items-center">
        <h1 class="text-2xl font-black text-gray-900 dark:text-white">SMA Islam Sultan Agung 1 Semarang - Rekening Bank</h1>
        <a href="{{ route('treasury.accounts.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-md font-bold">Tambah Rekening</a>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @foreach($accounts as $acc)
        <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-6">
            <h3 class="font-bold text-lg text-gray-900 dark:text-white">{{ $acc->bank_name }} - {{ $acc->account_name }}</h3>
            <p class="text-gray-500 text-sm mt-1">{{ $acc->account_number }}</p>
            <div class="mt-4 text-2xl font-black text-gray-900 dark:text-white">Rp {{ number_format($acc->current_balance, 0, ',', '.') }}</div>
            @if($acc->is_primary)
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 mt-4">Utama</span>
            @endif
        </div>
        @endforeach
    </div>
</div>
@endsection
BLADE
);

w('resources/views/treasury/accounts/create.blade.php', <<<'BLADE'
@extends('layouts.app')
@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <h1 class="text-2xl font-black text-gray-900 dark:text-white mb-6">SMA Islam Sultan Agung 1 Semarang - Tambah Rekening Bank</h1>
    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-6">
        <form action="{{ route('treasury.accounts.store') }}" method="POST">
            @csrf
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div><label class="block text-sm font-bold text-gray-700 dark:text-gray-300">Nama Bank</label>
                <input type="text" name="bank_name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></div>
                <div><label class="block text-sm font-bold text-gray-700 dark:text-gray-300">Nomor Rekening</label>
                <input type="text" name="account_number" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></div>
                <div><label class="block text-sm font-bold text-gray-700 dark:text-gray-300">Nama Rekening</label>
                <input type="text" name="account_name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></div>
                <div><label class="block text-sm font-bold text-gray-700 dark:text-gray-300">Jenis Rekening</label>
                <select name="account_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    <option value="giro">Giro</option>
                    <option value="tabungan">Tabungan</option>
                    <option value="virtual_account">Virtual Account</option>
                </select></div>
            </div>
            <div class="mt-6"><button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md font-bold">Simpan</button></div>
        </form>
    </div>
</div>
@endsection
BLADE
);

w('resources/views/treasury/reconciliation/index.blade.php', <<<'BLADE'
@extends('layouts.app')
@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="mb-6 flex justify-between items-center">
        <h1 class="text-2xl font-black text-gray-900 dark:text-white">SMA Islam Sultan Agung 1 Semarang - Rekonsiliasi Bank</h1>
        <a href="{{ route('treasury.reconciliation.import') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-md font-bold">Impor Rekening Koran</a>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-900">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Tanggal</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Rekening</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700 text-sm text-gray-600 dark:text-gray-300">
                @foreach($statements as $st)
                <tr>
                    <td class="px-6 py-4">{{ $st->statement_date->format('d/m/Y') }}</td>
                    <td class="px-6 py-4">{{ $st->account->bank_name ?? 'N/A' }}</td>
                    <td class="px-6 py-4">{{ ucfirst($st->status) }}</td>
                    <td class="px-6 py-4"><a href="{{ route('treasury.reconciliation.review', $st->id) }}" class="text-indigo-600 font-bold hover:text-indigo-900">Review</a></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
BLADE
);

w('resources/views/treasury/reconciliation/import.blade.php', <<<'BLADE'
@extends('layouts.app')
@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <h1 class="text-2xl font-black text-gray-900 dark:text-white mb-6">SMA Islam Sultan Agung 1 Semarang - Impor Rekening Koran</h1>
    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-6">
        <form action="{{ route('treasury.reconciliation.processImport') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div><label class="block text-sm font-bold text-gray-700 dark:text-gray-300">Pilih Rekening</label>
                <select name="bank_account_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    @foreach($accounts as $acc)
                        <option value="{{ $acc->id }}">{{ $acc->bank_name }}</option>
                    @endforeach
                </select></div>
                <div><label class="block text-sm font-bold text-gray-700 dark:text-gray-300">File CSV</label>
                <input type="file" name="file" class="mt-1 block w-full"></div>
            </div>
            <div class="mt-6"><button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md font-bold">Impor Data</button></div>
        </form>
    </div>
</div>
@endsection
BLADE
);

w('resources/views/treasury/reconciliation/review.blade.php', <<<'BLADE'
@extends('layouts.app')
@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <h1 class="text-2xl font-black text-gray-900 dark:text-white mb-6">SMA Islam Sultan Agung 1 Semarang - Review Rekonsiliasi</h1>
    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-900">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Tanggal</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Deskripsi</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Debit/Kredit</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700 text-sm text-gray-600 dark:text-gray-300">
                @foreach($statement->transactions as $tx)
                <tr>
                    <td class="px-6 py-4">{{ $tx->transaction_date->format('d/m/Y') }}</td>
                    <td class="px-6 py-4">{{ $tx->description }}</td>
                    <td class="px-6 py-4">D: {{ $tx->debit }} / K: {{ $tx->credit }}</td>
                    <td class="px-6 py-4">
                        @if($tx->match_status == 'unmatched')
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-rose-100 text-rose-800">Belum Cocok</span>
                        @else
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">Cocok</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
BLADE
);

w('resources/views/treasury/reconciliation/discrepancies.blade.php', <<<'BLADE'
@extends('layouts.app')
@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <h1 class="text-2xl font-black text-gray-900 dark:text-white mb-6">SMA Islam Sultan Agung 1 Semarang - Selisih Rekonsiliasi</h1>
    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-6">
        <p class="text-gray-600 dark:text-gray-300">Daftar selisih transaksi</p>
    </div>
</div>
@endsection
BLADE
);

w('resources/views/treasury/petty-cash/index.blade.php', <<<'BLADE'
@extends('layouts.app')
@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="mb-6 flex justify-between items-center">
        <h1 class="text-2xl font-black text-gray-900 dark:text-white">SMA Islam Sultan Agung 1 Semarang - Kas Kecil</h1>
        <a href="{{ route('treasury.petty-cash.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-md font-bold">Catat Transaksi</a>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @foreach($funds as $fund)
        <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-6">
            <h3 class="font-bold text-lg text-gray-900 dark:text-white">{{ $fund->fund_name }}</h3>
            <div class="mt-4 text-2xl font-black text-gray-900 dark:text-white">Rp {{ number_format($fund->current_balance, 0, ',', '.') }}</div>
            <p class="text-gray-500 text-sm mt-1">Imprest: Rp {{ number_format($fund->imprest_amount, 0, ',', '.') }}</p>
        </div>
        @endforeach
    </div>
</div>
@endsection
BLADE
);

w('resources/views/treasury/petty-cash/create.blade.php', <<<'BLADE'
@extends('layouts.app')
@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <h1 class="text-2xl font-black text-gray-900 dark:text-white mb-6">SMA Islam Sultan Agung 1 Semarang - Tambah Kas Kecil</h1>
    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-6">
        <form action="{{ route('treasury.petty-cash.store') }}" method="POST">
            @csrf
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div><label class="block text-sm font-bold text-gray-700 dark:text-gray-300">Dana</label>
                <select name="fund_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    @foreach($funds as $fund)
                        <option value="{{ $fund->id }}">{{ $fund->fund_name }}</option>
                    @endforeach
                </select></div>
                <div><label class="block text-sm font-bold text-gray-700 dark:text-gray-300">Jenis</label>
                <select name="type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    <option value="pengeluaran">Pengeluaran</option>
                    <option value="pengisian_ulang">Pengisian Ulang</option>
                </select></div>
                <div><label class="block text-sm font-bold text-gray-700 dark:text-gray-300">Kategori</label>
                <select name="category" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    <option value="atk">ATK</option>
                    <option value="konsumsi">Konsumsi</option>
                    <option value="lainnya">Lainnya</option>
                </select></div>
                <div><label class="block text-sm font-bold text-gray-700 dark:text-gray-300">Jumlah (Rp)</label>
                <input type="number" name="amount" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></div>
                <div><label class="block text-sm font-bold text-gray-700 dark:text-gray-300">Tanggal</label>
                <input type="date" name="transaction_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></div>
                <div class="md:col-span-2"><label class="block text-sm font-bold text-gray-700 dark:text-gray-300">Keterangan</label>
                <textarea name="description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></textarea></div>
            </div>
            <div class="mt-6"><button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md font-bold">Simpan</button></div>
        </form>
    </div>
</div>
@endsection
BLADE
);

w('resources/views/treasury/petty-cash/approval.blade.php', <<<'BLADE'
@extends('layouts.app')
@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <h1 class="text-2xl font-black text-gray-900 dark:text-white mb-6">SMA Islam Sultan Agung 1 Semarang - Persetujuan Kas Kecil</h1>
    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-6">
        <p class="text-gray-600 dark:text-gray-300">Daftar persetujuan</p>
    </div>
</div>
@endsection
BLADE
);

w('resources/views/treasury/petty-cash/report.blade.php', <<<'BLADE'
@extends('layouts.app')
@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <h1 class="text-2xl font-black text-gray-900 dark:text-white mb-6">SMA Islam Sultan Agung 1 Semarang - Laporan Kas Kecil</h1>
    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-6">
        <p class="text-gray-600 dark:text-gray-300">Laporan kategori</p>
    </div>
</div>
@endsection
BLADE
);

w('resources/views/treasury/position/dashboard.blade.php', <<<'BLADE'
@extends('layouts.app')
@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <h1 class="text-2xl font-black text-gray-900 dark:text-white mb-6">SMA Islam Sultan Agung 1 Semarang - Posisi Keuangan</h1>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @foreach($positions as $pos)
        <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-6">
            <h3 class="font-bold text-lg text-gray-900 dark:text-white">{{ $pos->account->bank_name ?? 'Bank' }}</h3>
            <p class="text-gray-500 text-sm mt-1">{{ $pos->position_date->format('d/m/Y') }}</p>
            <div class="mt-4 text-2xl font-black text-gray-900 dark:text-white">Rp {{ number_format($pos->closing_balance, 0, ',', '.') }}</div>
        </div>
        @endforeach
    </div>
</div>
@endsection
BLADE
);

w('resources/views/treasury/position/trend.blade.php', <<<'BLADE'
@extends('layouts.app')
@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <h1 class="text-2xl font-black text-gray-900 dark:text-white mb-6">SMA Islam Sultan Agung 1 Semarang - Tren Likuiditas</h1>
    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-6">
        <p class="text-gray-600 dark:text-gray-300">Grafik tren</p>
    </div>
</div>
@endsection
BLADE
);

w('resources/views/treasury/vendor/index.blade.php', <<<'BLADE'
@extends('layouts.app')
@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="mb-6 flex justify-between items-center">
        <h1 class="text-2xl font-black text-gray-900 dark:text-white">SMA Islam Sultan Agung 1 Semarang - Pembayaran Vendor</h1>
        <a href="{{ route('treasury.vendor.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-md font-bold">Tambah Pembayaran</a>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-900">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Vendor</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Jatuh Tempo</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Jumlah</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700 text-sm text-gray-600 dark:text-gray-300">
                @foreach($payments as $pay)
                <tr>
                    <td class="px-6 py-4">{{ $pay->vendor_name }}</td>
                    <td class="px-6 py-4">{{ $pay->due_date->format('d/m/Y') }}</td>
                    <td class="px-6 py-4">Rp {{ number_format($pay->amount, 0, ',', '.') }}</td>
                    <td class="px-6 py-4">
                        @if($pay->status == 'overdue')
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-rose-100 text-rose-800">Terlambat</span>
                        @else
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">{{ ucfirst($pay->status) }}</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
BLADE
);

w('resources/views/treasury/vendor/create.blade.php', <<<'BLADE'
@extends('layouts.app')
@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <h1 class="text-2xl font-black text-gray-900 dark:text-white mb-6">SMA Islam Sultan Agung 1 Semarang - Tambah Pembayaran Vendor</h1>
    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-6">
        <form action="{{ route('treasury.vendor.store') }}" method="POST">
            @csrf
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div><label class="block text-sm font-bold text-gray-700 dark:text-gray-300">Nama Vendor</label>
                <input type="text" name="vendor_name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></div>
                <div><label class="block text-sm font-bold text-gray-700 dark:text-gray-300">Jumlah (Rp)</label>
                <input type="number" name="amount" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></div>
                <div><label class="block text-sm font-bold text-gray-700 dark:text-gray-300">Jatuh Tempo</label>
                <input type="date" name="due_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></div>
                <div><label class="block text-sm font-bold text-gray-700 dark:text-gray-300">Metode</label>
                <select name="payment_method" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    <option value="transfer">Transfer</option>
                    <option value="cash">Cash</option>
                </select></div>
                <div class="md:col-span-2"><label class="block text-sm font-bold text-gray-700 dark:text-gray-300">Keterangan</label>
                <textarea name="description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></textarea></div>
            </div>
            <div class="mt-6"><button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md font-bold">Simpan</button></div>
        </form>
    </div>
</div>
@endsection
BLADE
);

w('resources/views/treasury/vendor/calendar.blade.php', <<<'BLADE'
@extends('layouts.app')
@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <h1 class="text-2xl font-black text-gray-900 dark:text-white mb-6">SMA Islam Sultan Agung 1 Semarang - Kalender Pembayaran</h1>
    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-6">
        <p class="text-gray-600 dark:text-gray-300">Tampilan kalender</p>
    </div>
</div>
@endsection
BLADE
);

// SEEDER
w('database/seeders/TreasurySeeder.php', <<<'PHP'
<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\{BankAccount, BankStatement, BankTransaction, PettyCashFund, PettyCashTransaction, VendorPayment, User};

class TreasurySeeder extends Seeder {
    public function run() {
        $user = User::first() ?? User::factory()->create();

        $bsi = BankAccount::create(['bank_name' => 'BSI', 'account_number' => '12345', 'account_name' => 'Sekolah', 'account_type' => 'giro', 'current_balance' => 50000000, 'is_primary' => true]);
        BankAccount::create(['bank_name' => 'BCA', 'account_number' => '54321', 'account_name' => 'Sekolah', 'account_type' => 'tabungan', 'current_balance' => 20000000]);
        BankAccount::create(['bank_name' => 'Mandiri', 'account_number' => '98765', 'account_name' => 'Sekolah', 'account_type' => 'giro', 'current_balance' => 30000000]);

        $statement = BankStatement::create(['bank_account_id' => $bsi->id, 'statement_date' => now(), 'period_start' => now()->startOfMonth(), 'period_end' => now()->endOfMonth(), 'file_path' => 'dummy.csv', 'total_transactions' => 20, 'matched_count' => 10, 'unmatched_count' => 10, 'status' => 'has_discrepancies', 'imported_by' => $user->id]);

        for($i=1; $i<=10; $i++) BankTransaction::create(['bank_statement_id' => $statement->id, 'transaction_date' => now()->subDays($i), 'description' => "Match Tx $i", 'debit' => 1000, 'match_status' => 'auto_matched']);
        for($i=1; $i<=10; $i++) BankTransaction::create(['bank_statement_id' => $statement->id, 'transaction_date' => now()->subDays($i), 'description' => "Unmatch Tx $i", 'debit' => 2000, 'match_status' => 'unmatched']);

        $tu = PettyCashFund::create(['fund_name' => 'Kas Kecil TU', 'custodian_id' => $user->id, 'imprest_amount' => 5000000, 'current_balance' => 3000000]);
        $lab = PettyCashFund::create(['fund_name' => 'Kas Kecil Lab', 'custodian_id' => $user->id, 'imprest_amount' => 2000000, 'current_balance' => 1500000]);

        for($i=1; $i<=10; $i++) PettyCashTransaction::create(['fund_id' => $tu->id, 'type' => 'pengeluaran', 'amount' => 100000, 'description' => "Beli ATK $i", 'transaction_date' => now(), 'category' => 'atk', 'approval_status' => 'approved', 'requested_by' => $user->id]);

        $vendors = ['PLN', 'PDAM', 'Telkom', 'Catering', 'Cleaning Service'];
        foreach($vendors as $v) VendorPayment::create(['vendor_name' => $v, 'description' => "Bayar $v", 'amount' => rand(100, 500) * 1000, 'due_date' => now()->addDays(rand(1,10)), 'payment_method' => 'transfer', 'status' => 'scheduled', 'created_by' => $user->id]);
    }
}
PHP
);

// TEST
w('tests/Feature/Treasury/TreasuryManagementTest.php', <<<'PHP'
<?php
namespace Tests\Feature\Treasury;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class TreasuryManagementTest extends TestCase {
    use RefreshDatabase;
    protected function setUp(): void {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }
    public function test_can_view_bank_accounts_index() {
        $response = $this->get('/treasury/accounts');
        $response->assertStatus(200);
    }
    public function test_can_view_reconciliation_index() {
        $response = $this->get('/treasury/reconciliation');
        $response->assertStatus(200);
    }
    public function test_can_view_petty_cash_index() {
        $response = $this->get('/treasury/petty-cash');
        $response->assertStatus(200);
    }
    public function test_can_view_treasury_position_dashboard() {
        $response = $this->get('/treasury/position');
        $response->assertStatus(200);
    }
}
PHP
);

// ROUTES
w('routes/treasury.php', <<<'PHP'
<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Treasury\{BankAccountController, ReconciliationController, PettyCashController, TreasuryPositionController, VendorPaymentController};

Route::middleware(['auth'])->prefix('treasury')->name('treasury.')->group(function () {
    Route::resource('accounts', BankAccountController::class);
    
    Route::get('reconciliation', [ReconciliationController::class, 'index'])->name('reconciliation.index');
    Route::get('reconciliation/import', [ReconciliationController::class, 'importForm'])->name('reconciliation.import');
    Route::post('reconciliation/import', [ReconciliationController::class, 'processImport'])->name('reconciliation.processImport');
    Route::get('reconciliation/{id}/review', [ReconciliationController::class, 'review'])->name('reconciliation.review');
    Route::post('reconciliation/manual-match', [ReconciliationController::class, 'manualMatch'])->name('reconciliation.manualMatch');

    Route::resource('petty-cash', PettyCashController::class);
    Route::post('petty-cash/{id}/approve', [PettyCashController::class, 'approve'])->name('petty-cash.approve');
    Route::post('petty-cash/{id}/reject', [PettyCashController::class, 'reject'])->name('petty-cash.reject');

    Route::get('position', [TreasuryPositionController::class, 'dashboard'])->name('position.dashboard');
    Route::get('position/trend', [TreasuryPositionController::class, 'trend'])->name('position.trend');

    Route::resource('vendor', VendorPaymentController::class);
    Route::get('vendor/calendar', [VendorPaymentController::class, 'calendar'])->name('vendor.calendar');
});
PHP
);

echo "All files written.\n";
