<?php

namespace App\Services;

use App\Models\CharityCampaign;
use App\Models\CharityDonation;
use App\Models\Student;
use App\Models\User;
use App\Models\WadiahAccount;
use App\Support\Access\StudentDataAccess;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CharityService
{
    protected $wadiahService;

    public function __construct(WadiahService $wadiahService)
    {
        $this->wadiahService = $wadiahService;
    }

    /**
     * Create a new charity campaign.
     */
    public function createCampaign(array $data): CharityCampaign
    {
        return CharityCampaign::create([
            'title' => $data['title'],
            'type' => $data['type'],
            'description' => $data['description'],
            'target_amount' => $data['target_amount'] ?? null,
            'collected_amount' => 0.00,
            'end_date' => $data['end_date'] ?? null,
            'is_active' => $data['is_active'] ?? true,
        ]);
    }

    /**
     * Process a new donation.
     */
    public function donate(array $data, int $userId): CharityDonation
    {
        $campaign = CharityCampaign::findOrFail($data['charity_campaign_id']);

        if (! $campaign->is_active) {
            throw new Exception('Kampanye donasi ini sudah tidak aktif.');
        }

        if ($campaign->end_date && $campaign->end_date->isPast()) {
            throw new Exception('Batas waktu kampanye donasi ini telah berakhir.');
        }

        $amount = (float) $data['amount'];
        if ($amount <= 0) {
            throw new Exception('Nominal donasi harus lebih besar dari nol.');
        }

        return DB::transaction(function () use ($campaign, $amount, $data, $userId) {
            $user = User::findOrFail($userId);
            $paymentMethod = $data['payment_method'];

            // Prepare donor name
            $isAnonymous = ! empty($data['is_anonymous']);
            $donorName = $isAnonymous ? 'Hamba Allah' : ($data['donor_name'] ?? $user->name);

            // Generate unique receipt number: ZIS-YYYYMMDD-RAND
            $receiptNumber = 'ZIS-'.date('Ymd').'-'.strtoupper(Str::random(6));

            $donation = new CharityDonation([
                'charity_campaign_id' => $campaign->id,
                'user_id' => $userId,
                'donor_name' => $donorName,
                'amount' => $amount,
                'payment_method' => $paymentMethod,
                'status' => 'pending',
                'is_anonymous' => $isAnonymous,
                'receipt_number' => $receiptNumber,
                'evidence_path' => $data['evidence_path'] ?? null,
            ]);

            // If paying via Wadiah Wallet: only the student's own savings, or a
            // parent's own child's. Anyone else used to debit Student::first(),
            // a stranger's savings, and a parent always their first child's.
            if ($paymentMethod === 'wadiah_wallet') {
                $family = Student::ofFamily($user);
                $student = isset($data['student_id'])
                    ? $family->whereKey($data['student_id'])->first()
                    : ($family->count() === 1 ? $family->first() : null);

                if (! $student) {
                    throw new Exception(__('Pembayaran dari tabungan wadiah hanya untuk siswa sendiri atau orang tuanya. Pilih anak yang tabungannya dipakai.'));
                }

                // Siswa limit donasi mandiri Rp 10.000
                if (in_array($user->role, StudentDataAccess::STUDENT_ROLES, true) && $amount > 10000) {
                    throw new Exception('Batas donasi mandiri siswa via E-Wallet maksimal adalah Rp 10.000 per transaksi.');
                }

                $account = WadiahAccount::where('student_id', $student->id)->first();
                if (! $account) {
                    throw new Exception('Rekening tabungan wadiah siswa belum terdaftar.');
                }

                // Debit using WadiahService withdrawal logic
                $this->wadiahService->withdraw(
                    $account,
                    $amount,
                    $userId,
                    'Donasi '.ucfirst($campaign->type).': '.$campaign->title
                );

                // Auto verify
                $donation->status = 'verified';
                $campaign->increment('collected_amount', $amount);
            }

            $donation->save();

            return $donation;
        });
    }

    /**
     * Verify a pending bank transfer / cash donation.
     */
    public function verifyDonation(int $donationId, int $verifierId): CharityDonation
    {
        return DB::transaction(function () use ($donationId) {
            $donation = CharityDonation::lockForUpdate()->findOrFail($donationId);

            if ($donation->status === 'verified') {
                return $donation;
            }

            $donation->update([
                'status' => 'verified',
            ]);

            // Increment campaign collected amount
            $campaign = CharityCampaign::lockForUpdate()->find($donation->charity_campaign_id);
            $campaign->increment('collected_amount', $donation->amount);

            return $donation;
        });
    }

    /**
     * Cancel a pending donation.
     */
    public function cancelDonation(int $donationId): CharityDonation
    {
        $donation = CharityDonation::findOrFail($donationId);
        if ($donation->status === 'verified') {
            throw new Exception('Donasi yang sudah terverifikasi tidak dapat dibatalkan.');
        }

        $donation->update([
            'status' => 'cancelled',
        ]);

        return $donation;
    }
}
