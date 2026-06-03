<?php

namespace App\Http\Controllers;

use App\Exports\BillingsExport;
use App\Http\Requests\StoreBulkBillingRequest;
use App\Jobs\GenerateBulkBillingsJob;
use App\Models\AcademicYear;
use App\Models\Billing;
use App\Models\Classroom;
use App\Models\Payment;
use App\Models\PaymentCategory;
use App\Models\Student;
use App\Notifications\BillingReminderNotification;
use App\Services\FinanceBillingService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class FinanceController extends Controller
{
    /** @var FinanceBillingService */
    protected $billingService;

    public function __construct(FinanceBillingService $billingService)
    {
        $this->billingService = $billingService;
    }

    /**
     * Display a listing of billings with classroom filter.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Billing::class);

        $query = Billing::with(['student.user', 'student.classroom', 'category', 'academicYear']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('student', function ($q) use ($search) {
                $q->whereHas('user', function ($uq) use ($search) {
                    $uq->where('name', 'like', "%{$search}%");
                })->orWhere('nisn', 'like', "%{$search}%")
                    ->orWhere('nis', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('classroom_id')) {
            $query->whereHas('student', fn ($q) => $q->where('classroom_id', $request->classroom_id));
        }

        $billings = $query->latest()->paginate(15)->withQueryString();
        $students = collect();
        if ($request->has('classroom_id') && $request->classroom_id) {
            $students = Student::with('user')->where('classroom_id', $request->classroom_id)->get();
        }
        $categories = PaymentCategory::all();
        $classrooms = Classroom::all();

        return view('finance.billings', compact('billings', 'students', 'categories', 'classrooms'));
    }

    /**
     * Bulk generate billings for a category.
     */
    public function bulkGenerate(StoreBulkBillingRequest $request)
    {
        $this->authorize('create', Billing::class);

        GenerateBulkBillingsJob::dispatch($request->validated());

        return back()->with('success', 'Proses pembuatan tagihan massal telah dimulai di latar belakang. Silakan cek kembali beberapa saat lagi.');
    }

    /**
     * Store a single billing.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Billing::class);

        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'payment_category_id' => 'required|exists:payment_categories,id',
            'amount' => 'required|numeric|min:0',
            'due_date' => 'required|date',
            'month' => 'nullable|string',
        ]);

        $activeYear = AcademicYear::where('is_active', true)->first();
        if (! $activeYear) {
            return back()->with('error', 'Tahun ajaran aktif tidak ditemukan.');
        }

        Billing::create(array_merge($validated, [
            'academic_year_id' => $activeYear->id,
            'status' => 'unpaid',
        ]));

        return redirect()->route('finance.billings.index')->with('success', 'Tagihan berhasil dibuat.');
    }

    /**
     * Store a payment with transaction safety.
     */
    public function storePayment(Request $request)
    {
        $validated = $request->validate([
            'billing_id' => 'required|exists:billings,id',
            'amount_paid' => 'required|numeric|min:1',
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:cash,transfer',
            'note' => 'nullable|string',
        ]);

        $payment = null;
        DB::transaction(function () use ($validated, &$payment) {
            $billing = Billing::lockForUpdate()->findOrFail($validated['billing_id']);

            if ($validated['amount_paid'] > $billing->remaining_balance) {
                abort(422, 'Jumlah bayar melebihi sisa tagihan.');
            }

            $payment = Payment::create([
                'billing_id' => $validated['billing_id'],
                'created_by' => Auth::id(),
                'amount_paid' => $validated['amount_paid'],
                'payment_date' => $validated['payment_date'],
                'payment_method' => $validated['payment_method'],
                'receipt_number' => 'RCP-'.strtoupper(Str::random(8)),
                'note' => $validated['note'] ?? null,
            ]);

            $billing->updateStatus();
        });

        if (! $payment) {
            return back()->with('error', 'Terjadi kesalahan saat memproses pembayaran.');
        }

        return back()->with('success', 'Pembayaran berhasil dicatat. No. Kuitansi: '.$payment->receipt_number);
    }

    /**
     * Re-sync billing status (Audit Tool).
     */
    public function sync(Billing $billing)
    {
        $billing->updateStatus();

        return back()->with('success', 'Status tagihan berhasil disinkronkan.');
    }

    /**
     * Manage Payment Categories.
     */
    public function storeCategory(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'default_amount' => 'required|numeric',
            'is_monthly' => 'boolean',
        ]);

        PaymentCategory::create($request->only(['name', 'default_amount', 'is_monthly']));

        return back()->with('success', 'Kategori iuran berhasil ditambahkan.');
    }

    /**
     * Edit a billing.
     */
    public function editBilling(Billing $billing)
    {
        $this->authorize('update', $billing);

        $billing->load(['student.user', 'category']);
        $categories = PaymentCategory::all();

        return view('finance.edit-billing', compact('billing', 'categories'));
    }

    /**
     * Update a billing.
     */
    public function updateBilling(Request $request, Billing $billing)
    {
        $this->authorize('update', $billing);

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'due_date' => 'nullable|date',
            'month' => 'nullable|string',
        ]);

        $billing->update($validated);
        $billing->updateStatus();

        return redirect()->route('finance.billings.index')->with('success', 'Tagihan berhasil diperbarui.');
    }

    /**
     * Print a payment receipt as PDF.
     */
    public function printReceipt(Payment $payment)
    {
        $payment->load(['billing.student.user', 'billing.student.classroom', 'billing.category', 'creator']);

        $pdf = Pdf::loadView('finance.receipt-pdf', compact('payment'));
        $pdf->setPaper('A5', 'landscape');

        return $pdf->stream('Kuitansi_'.$payment->receipt_number.'.pdf');
    }

    /**
     * Export billings to Excel.
     */
    public function export()
    {
        return Excel::download(new BillingsExport, 'data_tagihan_'.date('Y-m-d').'.xlsx');
    }

    /**
     * Send manual WhatsApp and database billing reminder to parent.
     */
    public function sendWhatsappReminder(Billing $billing)
    {
        $billing->load(['student.parent', 'category']);

        $student = $billing->student;
        if (! $student || ! $student->parent) {
            return back()->with('error', 'Data orang tua/wali murid tidak ditemukan untuk siswa ini.');
        }

        $student->parent->notify(new BillingReminderNotification($billing));

        return back()->with('success', 'Pengingat tagihan berhasil dikirim ke orang tua '.$student->name.'.');
    }
}
