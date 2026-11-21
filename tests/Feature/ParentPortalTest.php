<?php

/** @var TestCase|stdClass $this */

use App\Models\AcademicYear;
use App\Models\Billing;
use App\Models\Classroom;
use App\Models\CounselingSession;
use App\Models\ParentMessage;
use App\Models\PaymentCategory;
use App\Models\Student;
use App\Models\User;
use Tests\TestCase;

beforeEach(function () {
    $this->academicYear = AcademicYear::factory()->create(['is_active' => true]);
});

test('unauthenticated users cannot access the parent portal', function () {
    $this->get(route('parent-portal.dashboard'))->assertRedirect(route('login'));
});

test('non-parent roles cannot access the parent portal dashboard', function () {
    $studentUser = User::factory()->create(['role' => 'student']);
    $this->actingAs($studentUser)
        ->get(route('parent-portal.dashboard'))
        ->assertStatus(403);
});

test('parent can access their portal dashboard with authorized children', function () {
    $parentUser = User::factory()->create(['role' => 'parent']);
    $classroom = Classroom::factory()->create();
    $student = Student::factory()->create([
        'parent_id' => $parentUser->id,
        'classroom_id' => $classroom->id,
    ]);

    $response = $this->actingAs($parentUser)
        ->get(route('parent-portal.dashboard'));

    $response->assertStatus(200);
    $response->assertSee($student->user->name);
});

test('parent without children is redirected to fallback dashboard with error', function () {
    $parentUser = User::factory()->create(['role' => 'parent']);

    $response = $this->actingAs($parentUser)
        ->get(route('parent-portal.dashboard'));

    $response->assertRedirect(route('dashboard'));
    $response->assertSessionHas('error');
});

test('cross-parent access of another child data is strictly blocked with 403', function () {
    $parentA = User::factory()->create(['role' => 'parent']);
    $parentB = User::factory()->create(['role' => 'parent']);

    $classroom = Classroom::factory()->create();
    $childA = Student::factory()->create(['parent_id' => $parentA->id, 'classroom_id' => $classroom->id]);
    $childB = Student::factory()->create(['parent_id' => $parentB->id, 'classroom_id' => $classroom->id]);

    $this->actingAs($parentA);

    // Parent A tries to view child B pages:
    $this->get(route('parent-portal.report-card', $childB))->assertStatus(403);
    $this->get(route('parent-portal.schedule', $childB))->assertStatus(403);
    $this->get(route('parent-portal.attendance', $childB))->assertStatus(403);
    $this->get(route('parent-portal.billings', $childB))->assertStatus(403);
});

test('multi-child switching works correctly using query string and stores in session', function () {
    $parent = User::factory()->create(['role' => 'parent']);
    $classroom = Classroom::factory()->create();
    $child1 = Student::factory()->create(['parent_id' => $parent->id, 'classroom_id' => $classroom->id]);
    $child2 = Student::factory()->create(['parent_id' => $parent->id, 'classroom_id' => $classroom->id]);

    $response = $this->actingAs($parent)
        ->get(route('parent-portal.dashboard', ['child_id' => $child2->id]));

    $response->assertStatus(200);
    expect(session('parent_active_child_id'))->toBe($child2->id);
});

test('confidential counseling sessions are strictly hidden from parent views', function () {
    $parent = User::factory()->create(['role' => 'parent']);
    $classroom = Classroom::factory()->create();
    $student = Student::factory()->create(['parent_id' => $parent->id, 'classroom_id' => $classroom->id]);

    $counselor = User::factory()->create(['role' => 'bk']);

    // Confidential Session
    $sessionConfidential = CounselingSession::create([
        'student_id' => $student->id,
        'counselor_id' => $counselor->id,
        'category' => 'pribadi',
        'is_confidential' => true,
        'status' => 'completed',
        'reason' => 'Rahasia Sangat Penting',
        'scheduled_at' => now(),
    ]);

    // Public Session
    $sessionPublic = CounselingSession::create([
        'student_id' => $student->id,
        'counselor_id' => $counselor->id,
        'category' => 'akademik',
        'is_confidential' => false,
        'status' => 'completed',
        'reason' => 'Konsultasi Belajar Umum',
        'scheduled_at' => now(),
    ]);

    $response = $this->actingAs($parent)
        ->get(route('parent-portal.dashboard'));

    $response->assertStatus(200);
    $response->assertSee('Sesi akademik');
    $response->assertDontSee('Sesi pribadi');
});

test('chat messages between parent and teacher can be fetched, auto-read, and sent', function () {
    $parent = User::factory()->create(['role' => 'parent']);
    $teacherUser = User::factory()->create(['role' => 'guru']);
    $classroom = Classroom::factory()->create(['teacher_id' => $teacherUser->id]);
    $student = Student::factory()->create([
        'parent_id' => $parent->id,
        'classroom_id' => $classroom->id,
    ]);

    // Create an unread message from teacher to parent
    $msgFromTeacher = ParentMessage::create([
        'sender_id' => $teacherUser->id,
        'recipient_id' => $parent->id,
        'student_id' => $student->id,
        'message' => 'Halo Bapak/Ibu, anak Anda sangat berprestasi hari ini.',
        'is_read' => false,
    ]);

    // Parent accesses chat room
    $this->actingAs($parent);
    $response = $this->get(route('parent-portal.messages', ['child_id' => $student->id]));

    $response->assertStatus(200);
    $response->assertSee('anak Anda sangat berprestasi hari ini.');

    // Check message has been marked as read automatically
    $msgFromTeacher->refresh();
    expect($msgFromTeacher->is_read)->toBeTrue();

    // Parent sends a message to the teacher
    $postResponse = $this->post(route('parent-portal.messages.send'), [
        'student_id' => $student->id,
        'message' => 'Terima kasih atas bimbingannya, Pak/Bu.',
    ]);

    $postResponse->assertStatus(302);
    $this->assertDatabaseHas('parent_messages', [
        'sender_id' => $parent->id,
        'recipient_id' => $teacherUser->id,
        'student_id' => $student->id,
        'message' => 'Terima kasih atas bimbingannya, Pak/Bu.',
        'is_read' => false,
    ]);
});

test('parent can checkout billing and successfully confirm via callback simulation', function () {
    // Exercises the local payment simulator (off by default, never in production).
    config(['services.midtrans.simulator' => true]);

    $parent = User::factory()->create(['role' => 'parent']);
    $classroom = Classroom::factory()->create();
    $student = Student::factory()->create([
        'parent_id' => $parent->id,
        'classroom_id' => $classroom->id,
    ]);

    $category = PaymentCategory::factory()->create(['default_amount' => 500000]);
    $billing = Billing::factory()->create([
        'student_id' => $student->id,
        'payment_category_id' => $category->id,
        'amount' => 500000,
        'remaining_balance' => 500000,
        'status' => 'unpaid',
    ]);

    $this->actingAs($parent);

    // Checkout page
    $response = $this->get(route('parent-portal.billings.checkout', $billing->id));
    $response->assertStatus(200);

    // Billing should now have generated snap_token
    $billing->refresh();
    expect($billing->snap_token)->not->toBeNull();

    // Trigger mock callback
    $callbackResponse = $this->post(route('parent-portal.billings.callback', $billing->id));
    $callbackResponse->assertRedirect(route('parent-portal.billings', $student));

    $billing->refresh();
    expect((int) $billing->remaining_balance)->toBe(0);
    expect($billing->status)->toBe('paid');

    $this->assertDatabaseHas('payments', [
        'billing_id' => $billing->id,
        'amount_paid' => 500000,
    ]);
});

test('cross-parent cannot checkout or callback another child billing', function () {
    // Exercises the local payment simulator (off by default, never in production).
    config(['services.midtrans.simulator' => true]);

    $parentA = User::factory()->create(['role' => 'parent']);
    $parentB = User::factory()->create(['role' => 'parent']);
    $classroom = Classroom::factory()->create();
    $childA = Student::factory()->create(['parent_id' => $parentA->id, 'classroom_id' => $classroom->id]);
    $childB = Student::factory()->create(['parent_id' => $parentB->id, 'classroom_id' => $classroom->id]);

    $category = PaymentCategory::factory()->create(['default_amount' => 500000]);
    $billingB = Billing::factory()->create([
        'student_id' => $childB->id,
        'payment_category_id' => $category->id,
        'amount' => 500000,
        'remaining_balance' => 500000,
        'status' => 'unpaid',
    ]);

    $this->actingAs($parentA);

    $this->get(route('parent-portal.billings.checkout', $billingB->id))->assertStatus(403);
    $this->post(route('parent-portal.billings.callback', $billingB->id))->assertStatus(403);
});
