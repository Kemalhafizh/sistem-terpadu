<?php

namespace App\Http\Controllers;

use App\Models\Billing;
use App\Models\Payment;
use App\Models\Student;
use App\Services\PaymentGatewayService;
use App\Services\WadiahService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class StudentFinanceController extends Controller
{
    /**
     * Display a listing of my own billings.
     */
    public function index()
    {
        $student = Student::where('user_id', auth()->id())->first();
        if (! $student && (auth()->user()->role === 'admin' || auth()->user()->role === 'superadmin')) {
            $student = Student::first();
        }
        if (! $student) {
            return back()->with('error', 'Profil siswa tidak ditemukan.');
        }

        $billings = Billing::where('student_id', $student->id)
            ->with(['category', 'academicYear'])
            ->latest()
            ->get();

        return view('finance.student.index', compact('billings'));
    }

    /**
     * Mock Checkout Page.
     */
    public function checkout(Billing $billing)
    {
        // Ownership check: ensure billing belongs to the logged-in student
        $student = Student::where('user_id', auth()->id())->first();
        if (! $student && (auth()->user()->role === 'admin' || auth()->user()->role === 'superadmin')) {
            $student = Student::find($billing->student_id) ?: Student::first();
        }
        if (! $student || $billing->student_id !== $student->id) {
            abort(403, 'Anda tidak memiliki akses ke tagihan ini.');
        }

        // Dapatkan sisa saldo tagihan yang harus dibayar
        $grossAmount = (int) $billing->remaining_balance;

        if ($grossAmount <= 0) {
            return redirect()->route('finance.student.index')->with('error', 'Tagihan ini sudah lunas.');
        }

        // Request token baru jika belum ada atau jika token lama kadaluwarsa/sisa saldo berubah
        $serverKey = config('services.midtrans.server_key');

        if (! $billing->snap_token || $billing->snap_token == '' || str_starts_with($billing->snap_token, 'TKN-DEMO-')) {
            if (! empty($serverKey)) {
                try {
                    $snap = app(PaymentGatewayService::class)->createSnapTransaction(
                        $billing,
                        $student->user->name ?? 'Siswa',
                        $student->user->email ?? 'siswa@sekolah.sch.id'
                    );

                    if ($snap) {
                        $billing->update([
                            'snap_token' => $snap['token'],
                            'payment_url' => $snap['redirect_url'],
                        ]);
                    }
                } catch (\Exception $e) {
                    // Logging error tapi biarkan fallback bekerja di bawah
                    Log::warning('Gagal terhubung ke Midtrans API: '.$e->getMessage());
                }
            }

            // No working Midtrans: the local simulator if it is switched on, otherwise
            // say so instead of handing out a page that marks the bill paid.
            if (! $billing->snap_token || str_starts_with($billing->snap_token, 'TKN-DEMO-')) {
                if (! config('services.midtrans.simulator')) {
                    $billing->update(['snap_token' => null, 'payment_url' => null]);

                    return redirect()->route('finance.student.index')->with('error', __('Pembayaran online belum tersedia. Silakan bayar langsung di Tata Usaha.'));
                }
                $billing->update([
                    'snap_token' => 'TKN-DEMO-'.strtoupper(Str::random(10)),
                    'payment_url' => route('finance.mock-gateway', $billing->id),
                ]);
            }
        }

        return view('finance.student.checkout', compact('billing'));
    }

    /**
     * Mock Gateway Page (Visual representation of Midtrans Snap).
     */
    public function mockGateway(Billing $billing)
    {
        abort_unless(config('services.midtrans.simulator'), 404);

        // Ownership check
        $student = Student::where('user_id', auth()->id())->first();
        if (! $student && (auth()->user()->role === 'admin' || auth()->user()->role === 'superadmin')) {
            $student = Student::find($billing->student_id) ?: Student::first();
        }
        if (! $student || $billing->student_id !== $student->id) {
            abort(403, 'Anda tidak memiliki akses ke tagihan ini.');
        }

        return view('finance.mock-gateway', compact('billing'));
    }

    /**
     * Mock Webhook / Callback.
     */
    public function callback(Request $request, Billing $billing)
    {
        // A pretend payment: only with the local simulator switched on.
        abort_unless(config('services.midtrans.simulator'), 404);

        // Ownership check
        $student = Student::where('user_id', auth()->id())->first();
        if (! $student && (auth()->user()->role === 'admin' || auth()->user()->role === 'superadmin')) {
            $student = Student::find($billing->student_id) ?: Student::first();
        }
        if (! $student || $billing->student_id !== $student->id) {
            abort(403, 'Anda tidak memiliki akses ke tagihan ini.');
        }

        // In a real project, this would be a POST from Midtrans
        // For experiment, we allow a simple trigger
        DB::transaction(function () use ($billing) {
            Payment::create([
                'billing_id' => $billing->id,
                'created_by' => null, // dikonfirmasi payment gateway, bukan petugas
                'amount_paid' => $billing->remaining_balance,
                'payment_date' => now(),
                'payment_method' => 'transfer',
                'receipt_number' => 'AUTO-'.strtoupper(Str::random(8)),
                'note' => 'Pembayaran Otomatis via Gateway (Simulasi)',
            ]);

            $billing->updateStatus();
        });

        return redirect()->route('finance.student.index')->with('success', 'Pembayaran berhasil dikonfirmasi secara otomatis!');
    }

    /**
     * Show student's Wadiah savings/wallet.
     */
    public function wadiahWallet()
    {
        $student = Student::where('user_id', auth()->id())->first();
        if (! $student && (auth()->user()->role === 'admin' || auth()->user()->role === 'superadmin')) {
            $student = Student::first();
        }
        if (! $student) {
            return back()->with('error', 'Profil siswa tidak ditemukan.');
        }

        // Use service to get or create account
        $wadiahService = app(WadiahService::class);
        $account = $wadiahService->createAccount($student);

        // Load transactions
        $transactions = $account->transactions()->latest()->take(20)->get();

        return view('student.wadiah', compact('account', 'transactions'));
    }
}
