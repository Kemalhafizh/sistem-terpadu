<?php

namespace Database\Seeders;

use App\Models\LibraryBook;
use App\Models\LibraryLoan;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class LibraryLoanSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first();
        $pustakawan = User::where('email', 'pustakawan@sultanagung1.sch.id')->first();
        if (! $pustakawan) {
            $pustakawan = $admin;
        }

        $students = Student::all();
        $books = LibraryBook::all();

        LibraryLoan::truncate();

        // 35 Library Loans
        for ($i = 0; $i < 35; $i++) {
            $student = $students->random();
            $book = $books->random();

            $borrowedAt = now()->subDays(rand(5, 25));
            $dueAt = (clone $borrowedAt)->addDays(7);

            // Determine status
            // 60% chance returned, 20% active (still borrowed and not overdue), 20% overdue
            $rand = rand(1, 100);

            $returnedAt = null;
            $status = 'borrowed';
            $fineAmount = 0;
            $finePaidAt = null;

            if ($rand <= 60) {
                // Returned: 80% on time, 20% late
                $isLate = rand(1, 100) <= 20;
                if ($isLate) {
                    $returnedAt = (clone $dueAt)->addDays(rand(1, 10));
                    $daysLate = $dueAt->diffInDays($returnedAt);
                    $fineAmount = $daysLate * 1000; // Rp 1000 per day fine
                    $finePaidAt = $returnedAt;
                } else {
                    $returnedAt = (clone $borrowedAt)->addDays(rand(1, 6));
                }
                $status = 'returned';
            } elseif ($rand <= 80) {
                // Active (not overdue) - borrow date is recent
                $borrowedAt = now()->subDays(rand(1, 5));
                $dueAt = (clone $borrowedAt)->addDays(7);
                $status = 'borrowed';
            } else {
                // Overdue - borrow date is old and returned_at is null
                $borrowedAt = now()->subDays(rand(10, 20));
                $dueAt = (clone $borrowedAt)->addDays(7);
                $daysLate = $dueAt->diffInDays(now());
                $fineAmount = $daysLate * 1000;
                $status = 'borrowed'; // Standard status remains 'borrowed' until updated, or can be explicitly managed
            }

            LibraryLoan::create([
                'uuid' => (string) Str::uuid(),
                'student_id' => $student->id,
                'library_book_id' => $book->id,
                'borrowed_at' => $borrowedAt,
                'due_at' => $dueAt,
                'returned_at' => $returnedAt,
                'status' => $status,
                'fine_amount' => $fineAmount,
                'fine_paid_at' => $finePaidAt,
                'created_by' => $pustakawan->id,
            ]);
        }
    }
}
