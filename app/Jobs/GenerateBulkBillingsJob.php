<?php

namespace App\Jobs;

use App\Models\Billing;
use App\Models\Student;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateBulkBillingsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $validatedData;

    /**
     * Create a new job instance.
     */
    public function __construct(array $validatedData)
    {
        $this->validatedData = $validatedData;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $paymentCategoryId = $this->validatedData['payment_category_id'];
        $academicYearId = $this->validatedData['academic_year_id'];
        $month = $this->validatedData['month'] ?? null;

        // Optimize: Get all student IDs who ALREADY have this billing
        $existingStudentIds = Billing::where('payment_category_id', $paymentCategoryId)
            ->where('academic_year_id', $academicYearId)
            ->where('month', $month)
            ->pluck('student_id')
            ->toArray();

        // Get students who DON'T have this billing yet
        $studentsToBill = Student::whereNotIn('id', $existingStudentIds)->get();

        $count = 0;
        foreach ($studentsToBill as $student) {
            // Using create() to ensure 'booted' event (remaining_balance) fires
            Billing::create(array_merge($this->validatedData, [
                'student_id' => $student->id,
                'status' => 'unpaid',
            ]));
            $count++;
        }

        Log::info("GenerateBulkBillingsJob completed. Generated $count billings.");
    }
}
