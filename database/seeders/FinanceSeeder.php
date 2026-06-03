<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Billing;
use App\Models\Payment;
use App\Models\PaymentCategory;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;

class FinanceSeeder extends Seeder
{
    public function run(): void
    {
        $activeYear = AcademicYear::where('is_active', true)->first();
        $stafTu = User::where('email', 'staf.tu@sultanagung1.sch.id')->first();
        $students = Student::with('classroom')->get();

        // Payment Categories
        $spp = PaymentCategory::create(['name' => 'SPP Bulanan', 'default_amount' => 750000, 'is_monthly' => true]);
        $pangkal = PaymentCategory::create(['name' => 'Uang Pangkal', 'default_amount' => 5000000, 'is_monthly' => false]);
        $osis = PaymentCategory::create(['name' => 'Kegiatan OSIS', 'default_amount' => 200000, 'is_monthly' => false]);

        $months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni'];
        $receiptCounter = 1;

        foreach ($students as $student) {
            // SPP for 6 months
            foreach ($months as $monthIdx => $month) {
                $billing = Billing::create([
                    'student_id' => $student->id,
                    'payment_category_id' => $spp->id,
                    'academic_year_id' => $activeYear->id,
                    'month' => $month,
                    'amount' => 750000,
                    'due_date' => now()->startOfYear()->addMonths($monthIdx)->endOfMonth()->format('Y-m-d'),
                    'status' => 'unpaid',
                ]);

                // Simulate payments: older months more likely paid
                $chance = max(0, 95 - ($monthIdx * 18)); // Jan=95%, Jun=5%
                if (rand(1, 100) <= $chance) {
                    $paymentDate = now()->subDays(rand(0, 13))->format('Y-m-d');
                    Payment::create([
                        'billing_id' => $billing->id,
                        'created_by' => $stafTu->id,
                        'amount_paid' => 750000,
                        'payment_date' => $paymentDate,
                        'payment_method' => ['cash', 'transfer'][rand(0, 1)],
                        'receipt_number' => 'KW-'.date('Ym').'-'.str_pad($receiptCounter++, 5, '0', STR_PAD_LEFT),
                        'note' => 'Pembayaran SPP '.$month,
                    ]);
                    $billing->update(['status' => 'paid']);
                }
            }

            // Uang Pangkal for Kelas X students only
            if ($student->classroom && $student->classroom->grade_level === 'X') {
                $billing = Billing::create([
                    'student_id' => $student->id,
                    'payment_category_id' => $pangkal->id,
                    'academic_year_id' => $activeYear->id,
                    'amount' => 5000000,
                    'due_date' => '2026-02-28',
                    'status' => rand(1, 100) <= 70 ? 'paid' : 'unpaid',
                ]);
                if ($billing->status === 'paid') {
                    Payment::create([
                        'billing_id' => $billing->id,
                        'created_by' => $stafTu->id,
                        'amount_paid' => 5000000,
                        'payment_date' => now()->subDays(rand(5, 30))->format('Y-m-d'),
                        'payment_method' => 'transfer',
                        'receipt_number' => 'KW-'.date('Ym').'-'.str_pad($receiptCounter++, 5, '0', STR_PAD_LEFT),
                        'note' => 'Pelunasan Uang Pangkal',
                    ]);
                }
            }
        }
    }
}
