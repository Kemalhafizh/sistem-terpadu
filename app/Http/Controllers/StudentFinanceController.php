<?php

namespace App\Http\Controllers;

use App\Models\Billing;
use App\Models\Payment;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
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
                    // Buat payload untuk request ke Midtrans Snap API
                    $orderId = 'BILL-'.$billing->id.'-'.time();
                    $payload = [
                        'transaction_details' => [
                            'order_id' => $orderId,
                            'gross_amount' => $grossAmount,
                        ],
                        'customer_details' => [
                            'first_name' => $student->user->name ?? 'Siswa',
                            'email' => $student->user->email ?? 'siswa@sekolah.sch.id',
                        ],
                        'item_details' => [
                            [
                                'id' => 'BILL-'.$billing->id,
                                'price' => $grossAmount,
                                'quantity' => 1,
                                'name' => $billing->category->name ?? 'Iuran Sekolah',
                            ],
                        ],
                    ];

                    $response = Http::withBasicAuth($serverKey, '')
                        ->timeout(5)
                        ->post('https://app.sandbox.midtrans.com/snap/v1/transactions', $payload);

                    if ($response->successful()) {
                        $result = $response->json();
                        $billing->update([
                            'snap_token' => $result['token'],
                            'payment_url' => $result['redirect_url'],
                        ]);
                    }
                } catch (\Exception $e) {
                    // Logging error tapi biarkan fallback bekerja di bawah
                    Log::warning('Gagal terhubung ke Midtrans API: '.$e->getMessage());
                }
            }

            // Fallback Token jika Server Key kosong atau API Midtrans gagal/offline
            if (! $billing->snap_token) {
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
                'created_by' => 1, // System / Admin
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
}
