<?php

namespace Database\Seeders;

use App\Models\CanteenMerchant;
use App\Models\CanteenTransaction;
use App\Models\Student;
use App\Models\User;
use App\Models\WadiahAccount;
use App\Models\WadiahTransaction;
use App\Services\WadiahService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class WadiahAndCanteenSeeder extends Seeder
{
    public function run(): void
    {
        $wadiahService = app(WadiahService::class);
        $students = Student::all();
        $admin = User::where('role', 'admin')->first() ?: User::first();

        if ($students->isEmpty()) {
            return;
        }

        // 1. Create Wadiah accounts for all students
        foreach ($students as $student) {
            $wadiahService->createAccount($student);
        }

        // 2. Set up Canteen Merchants using TU staff users as owners
        $merchantOwners = User::where('role', 'staf_tu')->take(3)->get();
        if ($merchantOwners->count() < 3) {
            // fallback if not enough staf_tu
            $merchantOwners = User::where('role', 'guru')->take(3)->get();
        }

        $merchantNames = [
            'Kantin Amanah Bu Sri',
            'Koperasi Siswa SA-1',
            'Kantin Makanan Sehat YBWSA',
        ];

        $merchants = [];
        foreach ($merchantNames as $idx => $name) {
            $owner = $merchantOwners->get($idx) ?: User::first();

            // Re-role the owner to merchant for demonstration purposes
            if ($owner->role !== 'admin' && $owner->role !== 'superadmin') {
                $owner->update(['role' => 'merchant']);
            }

            $merchants[] = CanteenMerchant::create([
                'name' => $name,
                'owner_user_id' => $owner->id,
                'is_active' => true,
            ]);
        }

        // 3. Seed historical transactions for the past 15 days
        $menuItems = [
            ['name' => 'Nasi Goreng Telur', 'price' => 12000],
            ['name' => 'Mie Goreng Ayam', 'price' => 10000],
            ['name' => 'Soto Ayam', 'price' => 12000],
            ['name' => 'Bakso Kuah', 'price' => 12000],
            ['name' => 'Gorengan Tempe/Tahu', 'price' => 1000],
            ['name' => 'Roti Bakar', 'price' => 6000],
            ['name' => 'Es Teh Manis', 'price' => 3000],
            ['name' => 'Es Jeruk', 'price' => 4000],
            ['name' => 'Air Mineral', 'price' => 3000],
        ];

        // Process seed transactions for 20 random students
        $activeStudents = $students->random(min(20, $students->count()));

        foreach ($activeStudents as $student) {
            $account = WadiahAccount::where('student_id', $student->id)->first();
            if (! $account) {
                continue;
            }

            // Set random pocket money limit
            $account->update([
                'daily_spending_limit' => collect([15000, 20000, 30000, 50000])->random(),
            ]);

            // Seed deposits first to give them balance
            $currentBalance = 0.00;
            $startDate = Carbon::now()->subDays(15);

            // Initial deposit
            $depositAmount = rand(100, 300) * 1000; // Rp 100.000 - Rp 300.000
            $currentBalance += $depositAmount;

            $refNumber = 'DEP-'.$startDate->format('Ymd').'-'.strtoupper(Str::random(6));
            $tx = WadiahTransaction::create([
                'wadiah_account_id' => $account->id,
                'type' => 'deposit',
                'amount' => $depositAmount,
                'previous_balance' => 0.00,
                'current_balance' => $currentBalance,
                'reference_number' => $refNumber,
                'description' => 'Setoran tunai awal tabungan Wadiah',
                'processed_by' => $admin->id,
                'created_at' => $startDate,
                'updated_at' => $startDate,
            ]);

            // Update account balance
            $account->update(['balance' => $currentBalance]);

            // Staggered transactions over 15 days
            for ($day = 14; $day >= 0; $day--) {
                $txDate = Carbon::now()->subDays($day)->setTime(rand(7, 14), rand(0, 59));

                // 40% chance of deposit top-up on any day
                if (rand(1, 10) <= 2) {
                    $topupAmount = rand(20, 100) * 1000; // Rp 20.000 - Rp 100.000
                    $prevBalance = $currentBalance;
                    $currentBalance += $topupAmount;

                    $ref = 'DEP-'.$txDate->format('Ymd').'-'.strtoupper(Str::random(6));
                    WadiahTransaction::create([
                        'wadiah_account_id' => $account->id,
                        'type' => 'deposit',
                        'amount' => $topupAmount,
                        'previous_balance' => $prevBalance,
                        'current_balance' => $currentBalance,
                        'reference_number' => $ref,
                        'description' => 'Setoran tunai tabungan Wadiah',
                        'processed_by' => $admin->id,
                        'created_at' => $txDate,
                        'updated_at' => $txDate,
                    ]);
                    $account->update(['balance' => $currentBalance]);
                }

                // 80% chance of purchase daily
                if (rand(1, 10) <= 8 && $currentBalance > 15000) {
                    // select 1-3 random menu items
                    $itemsOrdered = [];
                    $totalPrice = 0;
                    $itemsCount = rand(1, 3);
                    $menuSelection = collect($menuItems)->random($itemsCount);

                    foreach ($menuSelection as $menu) {
                        $qty = rand(1, 2);
                        $subtotal = $menu['price'] * $qty;

                        // Check if exceeds daily limit or remaining balance
                        if (($totalPrice + $subtotal) > $account->daily_spending_limit || ($currentBalance - ($totalPrice + $subtotal)) < 0) {
                            break;
                        }

                        $itemsOrdered[] = [
                            'name' => $menu['name'],
                            'qty' => $qty,
                            'price' => $menu['price'],
                        ];
                        $totalPrice += $subtotal;
                    }

                    if ($totalPrice > 0) {
                        $prevBalance = $currentBalance;
                        $currentBalance -= $totalPrice;

                        $merchant = collect($merchants)->random();
                        $ref = 'PUR-'.$txDate->format('Ymd').'-'.strtoupper(Str::random(6));

                        $wadiahTx = WadiahTransaction::create([
                            'wadiah_account_id' => $account->id,
                            'type' => 'purchase',
                            'amount' => $totalPrice,
                            'previous_balance' => $prevBalance,
                            'current_balance' => $currentBalance,
                            'reference_number' => $ref,
                            'description' => 'Pembayaran cashless di '.$merchant->name,
                            'processed_by' => $merchant->owner_user_id,
                            'created_at' => $txDate,
                            'updated_at' => $txDate,
                        ]);

                        CanteenTransaction::create([
                            'canteen_merchant_id' => $merchant->id,
                            'wadiah_transaction_id' => $wadiahTx->id,
                            'items_ordered' => $itemsOrdered,
                            'total_amount' => $totalPrice,
                            'created_at' => $txDate,
                            'updated_at' => $txDate,
                        ]);

                        $account->update(['balance' => $currentBalance]);
                    }
                }
            }
        }
    }
}
