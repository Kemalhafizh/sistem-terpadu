<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\PaymentCategory;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

class BillingFactory extends Factory
{
    public function definition(): array
    {
        $amount = $this->faker->numberBetween(100000, 5000000);

        return [
            'student_id' => Student::factory(),
            'payment_category_id' => PaymentCategory::factory(),
            'academic_year_id' => AcademicYear::factory(),
            'amount' => $amount,
            'due_date' => $this->faker->dateTimeBetween('now', '+1 month')->format('Y-m-d'),
            'status' => 'unpaid',
        ];
    }
}
