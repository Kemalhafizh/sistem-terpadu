<?php

namespace App\Services;

use App\Models\Billing;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

/**
 * Class PaymentGatewayService
 *
 * Mengelola integrasi dengan Payment Gateway (Midtrans).
 * Bertanggung jawab untuk membuat Snap Token dan memproses webhook notifikasi
 * pembayaran otomatis dari sistem Midtrans ke aplikasi lokal.
 */
class PaymentGatewayService
{
    protected $serverKey;

    protected $isProduction;

    protected $snapUrl;

    public function __construct()
    {
        $this->serverKey = config('services.midtrans.server_key');
        $this->isProduction = config('services.midtrans.is_production', false);
        $this->snapUrl = $this->isProduction
            ? 'https://app.midtrans.com/snap/v1/transactions'
            : 'https://app.sandbox.midtrans.com/snap/v1/transactions';
    }

    /**
     * Create a Snap Token for Midtrans payment.
     *
     * Mengirim payload detail tagihan ke API Midtrans untuk mendapatkan URL
     * antarmuka pembayaran atau token unik yang akan dirender di frontend.
     *
     * @param  Billing  $billing  Model tagihan yang akan dibayar
     * @return string|null Token pembayaran (Snap Token) atau null jika gagal
     */
    public function createSnapToken(Billing $billing)
    {
        $payload = [
            'transaction_details' => [
                'order_id' => 'BILL-'.$billing->id.'-'.time(),
                'gross_amount' => (int) $billing->remaining_balance,
            ],
            'customer_details' => [
                'first_name' => $billing->student->user->name,
                'email' => $billing->student->user->email,
                'phone' => $billing->student->parent_phone,
            ],
            'item_details' => [
                [
                    'id' => $billing->category->id,
                    'price' => (int) $billing->remaining_balance,
                    'quantity' => 1,
                    'name' => $billing->category->name.($billing->month ? ' '.$billing->month : ''),
                ],
            ],
        ];

        try {
            $response = Http::withBasicAuth($this->serverKey, '')
                ->post($this->snapUrl, $payload);

            if ($response->successful()) {
                return $response->json()['token'];
            }

            \Log::error('Midtrans Snap Error: '.$response->body());

            return null;
        } catch (\Exception $e) {
            \Log::error('Midtrans Exception: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Handle payment notification from Midtrans.
     *
     * Memvalidasi signature webhook dari Midtrans. Jika pembayaran terverifikasi
     * (capture/settlement), sistem otomatis mencatat pembayaran dan memperbarui
     * status tagihan siswa menjadi Lunas (paid).
     *
     * @param  array  $payload  Payload JSON dari request webhook Midtrans
     * @return bool True jika pembayaran berhasil diproses, false sebaliknya
     */
    public function handleNotification(array $payload)
    {
        $transactionStatus = $payload['transaction_status'];
        $orderId = $payload['order_id'];
        $grossAmount = $payload['gross_amount'];
        $signatureKey = $payload['signature_key'];

        // Verify signature
        $localSignature = hash('sha512', $orderId.$payload['status_code'].$grossAmount.$this->serverKey);
        if ($localSignature !== $signatureKey) {
            \Log::warning('Midtrans Invalid Signature');

            return false;
        }

        // Extract Billing ID from Order ID (BILL-{id}-{time})
        $parts = explode('-', $orderId);
        $billingId = $parts[1];
        $billing = Billing::find($billingId);

        if (! $billing) {
            \Log::error('Billing not found for Order ID: '.$orderId);

            return false;
        }

        if ($transactionStatus == 'capture' || $transactionStatus == 'settlement') {
            DB::transaction(function () use ($billing, $payload) {
                Payment::create([
                    'billing_id' => $billing->id,
                    'created_by' => null, // System / Gateway
                    'amount_paid' => $payload['gross_amount'],
                    'payment_date' => now(),
                    'payment_method' => 'gateway',
                    'receipt_number' => 'GW-'.$payload['transaction_id'],
                    'note' => 'Paid via Payment Gateway ('.$payload['payment_type'].')',
                ]);

                $billing->updateStatus();
            });

            return true;
        }

        return false;
    }
}
