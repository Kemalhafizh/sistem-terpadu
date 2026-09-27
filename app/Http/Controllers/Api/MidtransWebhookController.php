<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Billing;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MidtransWebhookController extends Controller
{
    /**
     * Handle webhook callback dari Midtrans (Sandbox & Simulator).
     */
    public function handleWebhook(Request $request)
    {
        $payload = $request->all();
        Log::info('Midtrans Webhook Callback Received:', $payload);

        $orderId = $payload['order_id'] ?? null;
        $statusCode = $payload['status_code'] ?? null;
        $grossAmount = $payload['gross_amount'] ?? null;
        $signatureKey = $payload['signature_key'] ?? null;
        $transactionStatus = $payload['transaction_status'] ?? null;
        $paymentType = $payload['payment_type'] ?? 'bank_transfer';

        if (! $orderId || ! $statusCode || ! $grossAmount || (float) $grossAmount <= 0) {
            return response()->json(['message' => 'Invalid Payload: Order ID, status code, and positive gross amount are required'], 400);
        }

        // Tentukan apakah request berasal dari Simulator Portofolio
        // Hanya izinkan bypass signature simulasi pada lingkungan lokal/testing untuk keamanan
        $isSimulation = isset($payload['is_simulation']) && $payload['is_simulation'] === true && app()->environment('local', 'testing');

        // 1. Verifikasi Tanda Tangan (Signature Key)
        $serverKey = config('services.midtrans.server_key');

        if (! $isSimulation) {
            if (empty($serverKey)) {
                Log::error('Midtrans Webhook: Server key tidak terkonfigurasi pada server production!');
                return response()->json(['message' => 'Payment gateway server key misconfigured'], 500);
            }

            $expectedSignature = hash('sha512', $orderId.$statusCode.$grossAmount.$serverKey);
            if (! hash_equals($expectedSignature, (string) $signatureKey)) {
                Log::warning("Midtrans Webhook Signature Mismatch! Expected: $expectedSignature, Received: $signatureKey");

                return response()->json(['message' => 'Invalid Signature'], 403);
            }
        }

        // 2. Ekstrak Billing ID dari order_id (Format: BILL-[billing_id]-[timestamp/random])
        $billingId = null;
        if (str_starts_with($orderId, 'BILL-')) {
            $parts = explode('-', $orderId);
            $billingId = $parts[1] ?? null;
        }

        if (! $billingId) {
            return response()->json(['message' => 'Billing ID not found in order_id'], 400);
        }

        // 3. Proses Transaksi
        $billing = Billing::find($billingId);
        if (! $billing) {
            return response()->json(['message' => 'Billing record not found'], 404);
        }

        // Jika tagihan sudah lunas, tidak perlu proses ulang
        if ($billing->status === 'paid' && $billing->remaining_balance <= 0) {
            return response()->json(['message' => 'Billing already paid'], 200);
        }

        $success = false;

        if ($transactionStatus === 'settlement' || $transactionStatus === 'capture') {
            DB::transaction(function () use ($billing, $grossAmount, $paymentType, $orderId, &$success) {
                // Kunci data untuk transaksi aman
                $billingLock = Billing::lockForUpdate()->find($billing->id);

                // Tambahkan entri pembayaran baru
                Payment::create([
                    'billing_id' => $billingLock->id,
                    'created_by' => null, // dikonfirmasi payment gateway, bukan petugas
                    'amount_paid' => (float) $grossAmount,
                    'payment_date' => now()->toDateString(),
                    'payment_method' => 'transfer',
                    'receipt_number' => 'MID-'.strtoupper(Str::random(8)),
                    'note' => 'Pembayaran Otomatis via Midtrans ('.ucfirst($paymentType).', Order ID: '.$orderId.')',
                ]);

                // Update status tagihan dan sisa saldo
                $billingLock->updateStatus();
                $success = true;
            });
        } elseif (in_array($transactionStatus, ['expire', 'cancel', 'deny'])) {
            // Hapus token kadaluwarsa agar siswa bisa checkout ulang
            $billing->update([
                'snap_token' => null,
                'payment_url' => null,
            ]);
            $success = true;
        }

        return response()->json([
            'status' => 'success',
            'processed' => $success,
            'billing_status' => $billing->fresh()->status,
        ], 200);
    }
}
