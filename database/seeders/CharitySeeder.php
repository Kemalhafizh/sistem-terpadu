<?php

namespace Database\Seeders;

use App\Models\CharityCampaign;
use App\Models\CharityDonation;
use App\Models\User;
use App\Models\Student;
use App\Models\WadiahAccount;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Carbon\Carbon;

class CharitySeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Campaigns
        $campaigns = [
            [
                'title' => 'Wakaf Pembangunan Masjid Kampus Sultan Agung',
                'type' => 'wakaf',
                'description' => 'Program wakaf modal produktif pembangunan gedung masjid baru lingkungan SMA Islam Sultan Agung 1 Semarang. Dana disalurkan langsung melalui Yayasan Badan Wakaf Sultan Agung (YBWSA).',
                'target_amount' => 50000000.00,
                'end_date' => Carbon::now()->addMonths(6),
                'is_active' => true,
            ],
            [
                'title' => 'Infaq Santunan & Beasiswa Anak Yatim Piatu',
                'type' => 'infaq',
                'description' => 'Penyaluran beasiswa berkala dan santunan biaya pendidikan bagi siswa yatim, piatu, dan dhuafa berprestasi di SMA Islam Sultan Agung 1.',
                'target_amount' => 15000000.00,
                'end_date' => Carbon::now()->addMonths(2),
                'is_active' => true,
            ],
            [
                'title' => 'Shodaqoh Jumat Berkah - Makanan Sehat Siswa',
                'type' => 'shodaqoh',
                'description' => 'Penyediaan konsumsi sarapan sehat gratis setiap hari Jumat bagi seluruh siswa yang melaksanakan sholat dhuha berjamaah dan tadarus pagi.',
                'target_amount' => 5000000.00,
                'end_date' => Carbon::now()->addMonths(1),
                'is_active' => true,
            ],
            [
                'title' => 'Zakat Fitrah & Zakat Mal Civitas Akademika 1447 H',
                'type' => 'zakat',
                'description' => 'Penerimaan dan penyaluran zakat maal serta fitrah menjelang Idul Fitri bagi warga sekitar sekolah yang membutuhkan.',
                'target_amount' => 30000000.00,
                'end_date' => Carbon::now()->addDays(25),
                'is_active' => true,
            ],
        ];

        $campaignModels = [];
        foreach ($campaigns as $camp) {
            $campaignModels[] = CharityCampaign::create($camp);
        }

        // 2. Fetch Users
        $users = User::whereIn('role', ['guru', 'student', 'parent'])->get();
        if ($users->isEmpty()) {
            $users = User::all();
        }

        // 3. Seed 45 Donations over the past 15 days
        for ($i = 0; $i < 45; $i++) {
            $campaign = collect($campaignModels)->random();
            $user = $users->random();

            $date = Carbon::now()->subDays(rand(0, 15))->setTime(rand(7, 18), rand(0, 59));
            $paymentMethod = collect(['transfer', 'wadiah_wallet', 'cash'])->random();
            
            // Adjust amount based on type
            if ($campaign->type === 'wakaf') {
                $amount = rand(5, 50) * 10000; // Rp 50.000 - Rp 500.000
            } elseif ($campaign->type === 'zakat') {
                $amount = rand(3, 15) * 15000; // Rp 45.000 - Rp 225.000
            } else {
                $amount = rand(5, 50) * 1000; // Rp 5.000 - Rp 50.000
            }

            // If method is wadiah_wallet, verify if they have account/balance
            if ($paymentMethod === 'wadiah_wallet') {
                $student = null;
                if ($user->role === 'student') {
                    $student = Student::where('user_id', $user->id)->first();
                    // enforce limit
                    if ($amount > 10000) {
                        $amount = 10000;
                    }
                } elseif ($user->role === 'parent') {
                    $student = Student::where('parent_id', $user->id)->first();
                }

                if ($student) {
                    $account = WadiahAccount::where('student_id', $student->id)->first();
                    if ($account) {
                        // Ensure they have enough balance, otherwise set to transfer method
                        if ($account->balance < $amount) {
                            $paymentMethod = 'transfer';
                        } else {
                            // deduct balance
                            $account->decrement('balance', $amount);
                        }
                    } else {
                        $paymentMethod = 'transfer';
                    }
                } else {
                    $paymentMethod = 'transfer';
                }
            }

            // Status distribution
            $status = 'verified';
            $evidencePath = null;

            if ($paymentMethod === 'transfer') {
                $status = collect(['verified', 'pending', 'cancelled'])->random();
                if ($status === 'pending') {
                    $evidencePath = 'evidence_donations/demo_receipt.jpg';
                }
            } elseif ($paymentMethod === 'cash') {
                $status = collect(['verified', 'pending'])->random();
            }

            $isAnonymous = rand(1, 10) <= 2; // 20% anonymous
            $donorName = $isAnonymous ? 'Hamba Allah' : $user->name;

            $receipt = 'ZIS-' . $date->format('Ymd') . '-' . strtoupper(Str::random(6));

            CharityDonation::create([
                'charity_campaign_id' => $campaign->id,
                'user_id' => $user->id,
                'donor_name' => $donorName,
                'amount' => $amount,
                'payment_method' => $paymentMethod,
                'status' => $status,
                'evidence_path' => $evidencePath,
                'is_anonymous' => $isAnonymous,
                'receipt_number' => $receipt,
                'created_at' => $date,
                'updated_at' => $date,
            ]);

            // If verified, add to campaign total
            if ($status === 'verified') {
                $campaign->increment('collected_amount', $amount);
            }
        }
    }
}
