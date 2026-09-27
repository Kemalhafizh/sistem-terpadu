<?php

use App\Models\ImmutableAuditLog;
use App\Models\User;
use App\Services\DataAnonymizerService;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->student = User::factory()->create(['role' => 'student', 'name' => 'John Doe PDP', 'email' => 'john.pdp@smaisa1.sch.id']);
    $this->anonymizer = new DataAnonymizerService;
});

test('data anonymizer service masks nik, phone, and email according to uu pdp rules', function () {
    expect($this->anonymizer->maskNik('3374012304950001'))->toBe('3374**********01');
    expect($this->anonymizer->maskPhone('081234567890'))->toBe('0812****7890');
    expect($this->anonymizer->maskEmail('kemalhafizh@gmail.com'))->toBe('k***h@gmail.com');
});

test('data anonymizer service executes right to be forgotten protocol', function () {
    $success = $this->anonymizer->executeRightToBeForgotten($this->student);

    expect($success)->toBeTrue();
    $this->student->refresh();

    expect($this->student->name)->toBe("ANONYMIZED_USER_#{$this->student->id}");
    expect($this->student->email)->toBe("anonymized_{$this->student->id}@privacy.local");
});

test('admin can view pdpl privacy dashboard', function () {
    $response = $this->actingAs($this->admin)->get(route('admin.security.privacy.index'));
    $response->assertStatus(200);
    $response->assertSee('PDPL Privacy');
});

test('admin can preview pii data masking via controller', function () {
    $response = $this->actingAs($this->admin)
        ->from(route('admin.security.privacy.index'))
        ->post(route('admin.security.privacy.preview'), [
            'nik' => '3374012304950001',
            'phone' => '081234567890',
            'email' => 'test@gmail.com',
        ]);

    $response->assertStatus(302);
    $response->assertRedirect(route('admin.security.privacy.index'));
    $response->assertSessionHas('preview_result');
});

test('admin can execute right to be forgotten protocol via controller and record audit ledger', function () {
    $response = $this->actingAs($this->admin)
        ->from(route('admin.security.privacy.index'))
        ->post(route('admin.security.privacy.right-to-be-forgotten'), [
            'user_id' => $this->student->id,
        ]);

    $response->assertStatus(302);
    $response->assertRedirect(route('admin.security.privacy.index'));

    $this->student->refresh();
    expect($this->student->name)->toContain('ANONYMIZED_USER_#');

    // Verify cryptographic audit log entry recorded
    $log = ImmutableAuditLog::where('event_type', 'RIGHT_TO_BE_FORGOTTEN_EXECUTED')->latest()->first();
    expect($log)->not->toBeNull();
    expect($log->auditable_id)->toBe($this->student->id);
});
