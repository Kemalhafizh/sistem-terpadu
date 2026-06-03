<?php

namespace Tests\Feature\EndToEnd;

use App\Models\AcademicYear;
use App\Models\Billing;
use App\Models\Classroom;
use App\Models\Payment;
use App\Models\PaymentCategory;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MainFlowE2ETest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        AcademicYear::create([
            'year' => '2026/2027',
            'semester' => 'Ganjil',
            'is_active' => true,
            'start_date' => '2026-07-01',
            'end_date' => '2026-12-31',
        ]);
    }

    /**
     * Test the full administrative flow: Login -> Create Student -> Create Billing -> Pay Billing
     */
    public function test_full_administrative_financial_flow(): void
    {
        // 1. Create and Login as Admin
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);
        $this->actingAs($admin);

        $response = $this->get('/dashboard');
        $response->assertStatus(200);

        // 2. Prepare master data for Finance
        $classroom = Classroom::create([
            'name' => 'X MIPA 1',
            'grade_level' => 'X',
            'major' => 'mipa',
            'academic_year' => '2026/2027',
        ]);
        $studentUser = User::factory()->create(['role' => 'student']);
        $student = Student::create([
            'user_id' => $studentUser->id,
            'classroom_id' => $classroom->id,
            'nisn' => '1234567890',
            'nis' => '12345',
            'gender' => 'L',
        ]);

        $category = PaymentCategory::create([
            'name' => 'SPP Bulanan',
            'default_amount' => 500000,
            'is_monthly' => true,
        ]);

        // 3. Create a Billing
        $response = $this->post(route('finance.billings.store'), [
            'student_id' => $student->id,
            'payment_category_id' => $category->id,
            'amount' => 500000,
            'due_date' => now()->addDays(10)->format('Y-m-d'),
            'month' => 'Juli',
        ]);
        $response->assertRedirect(route('finance.billings.index'));
        $response->assertSessionHas('success');

        $billing = Billing::first();
        $this->assertNotNull($billing);
        $this->assertEquals(500000, $billing->amount);
        $this->assertEquals('unpaid', $billing->status);

        // 4. Pay the Billing
        $response = $this->post(route('finance.payments.store'), [
            'billing_id' => $billing->id,
            'amount_paid' => 500000,
            'payment_date' => now()->format('Y-m-d'),
            'payment_method' => 'transfer',
            'note' => 'Pembayaran SPP Lunas',
        ]);
        $response->assertRedirect();
        $response->assertSessionHas('success');

        // 5. Verify the updates
        $billing->refresh();
        $this->assertEquals('paid', $billing->status);
        $this->assertEquals(0, $billing->remaining_balance);

        $payment = Payment::first();
        $this->assertNotNull($payment);
        $this->assertEquals(500000, $payment->amount_paid);
    }
}
