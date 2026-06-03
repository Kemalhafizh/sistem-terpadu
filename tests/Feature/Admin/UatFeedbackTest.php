<?php

use App\Models\User;
use App\Models\UatFeedback;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
});

test('guest is redirected to login when submitting feedback', function () {
    $response = $this->post(route('uat.feedback.store'), [
        'rating' => 4,
        'category' => 'bug',
        'description' => 'Guest trying to submit feedback',
        'page_url' => 'http://localhost/dashboard',
    ]);
    
    $response->assertRedirect(route('login'));
});

test('guest is redirected to login when accessing dashboard', function () {
    $response = $this->get(route('admin.uat.feedback.dashboard'));
    $response->assertRedirect(route('login'));
});

test('guest is redirected to login when accessing report', function () {
    $response = $this->get(route('admin.uat.feedback.report'));
    $response->assertRedirect(route('login'));
});

test('any authenticated user (student, parent, teacher, staff) can submit feedback with base64 screenshot', function () {
    $user = User::factory()->create(['role' => 'student', 'must_change_password' => false]);
    
    // Transparent 1x1 base64 png image
    $base64Image = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=';

    $response = $this->actingAs($user)->postJson(route('uat.feedback.store'), [
        'rating' => 5,
        'category' => 'ui_suggestion',
        'description' => 'This is a test feedback containing base64 screenshot.',
        'page_url' => 'http://localhost/some-page',
        'device_info' => ['browser' => 'Chrome', 'os' => 'Windows'],
        'screenshot' => $base64Image
    ]);

    $response->assertOk();
    $response->assertJsonPath('success', true);

    $feedback = UatFeedback::first();
    expect($feedback)->not->toBeNull();
    expect($feedback->rating)->toEqual(5);
    expect($feedback->category)->toEqual('ui_suggestion');
    expect($feedback->user_id)->toEqual($user->id);
    expect($feedback->screenshot_path)->not->toBeNull();

    // Verify file actually exists in storage
    Storage::disk('public')->assertExists($feedback->screenshot_path);
});

test('non-admin user is forbidden from accessing feedback dashboard', function () {
    $user = User::factory()->create(['role' => 'guru', 'must_change_password' => false]);
    $response = $this->actingAs($user)->get(route('admin.uat.feedback.dashboard'));
    $response->assertStatus(403);
});

test('non-admin user is forbidden from accessing feedback report', function () {
    $user = User::factory()->create(['role' => 'orang_tua', 'must_change_password' => false]);
    $response = $this->actingAs($user)->get(route('admin.uat.feedback.report'));
    $response->assertStatus(403);
});

test('non-admin user is forbidden from updating feedback status', function () {
    $user = User::factory()->create(['role' => 'siswa', 'must_change_password' => false]);
    
    $admin = User::factory()->create(['role' => 'admin', 'must_change_password' => false]);
    $feedback = UatFeedback::create([
        'user_id' => $user->id,
        'page_url' => 'http://localhost/page',
        'rating' => 3,
        'category' => 'slow',
        'description' => 'Page is slow',
        'status' => 'open'
    ]);

    $response = $this->actingAs($user)->post(route('admin.uat.feedback.update-status', $feedback->id), [
        'status' => 'fixed',
        'priority' => 'high',
        'developer_notes' => 'Fixed it!'
    ]);

    $response->assertStatus(403);
    expect($feedback->fresh()->status)->toEqual('open');
});

test('admin can access feedback dashboard, search and filter records', function () {
    $admin = User::factory()->create(['role' => 'admin', 'must_change_password' => false]);
    $student = User::factory()->create(['role' => 'student', 'must_change_password' => false]);

    // Create UAT feedbacks
    UatFeedback::create([
        'user_id' => $student->id,
        'page_url' => 'http://localhost/page-a',
        'rating' => 2,
        'category' => 'bug',
        'description' => 'System crashed on page A',
        'status' => 'open',
        'priority' => 'critical'
    ]);

    UatFeedback::create([
        'user_id' => $student->id,
        'page_url' => 'http://localhost/page-b',
        'rating' => 4,
        'category' => 'ui_suggestion',
        'description' => 'Design could be improved on page B',
        'status' => 'fixed',
        'priority' => 'low'
    ]);

    // Test dashboard access
    $response = $this->actingAs($admin)->get(route('admin.uat.feedback.dashboard'));
    $response->assertOk();
    $response->assertSee('System crashed on page A');
    $response->assertSee('Design could be improved on page B');

    // Test filtering by status
    $responseFilter = $this->actingAs($admin)->get(route('admin.uat.feedback.dashboard', ['status' => 'open']));
    $responseFilter->assertOk();
    $responseFilter->assertSee('System crashed on page A');
    $responseFilter->assertDontSee('Design could be improved on page B');
});

test('admin can update feedback status, priority, and developer notes', function () {
    $admin = User::factory()->create(['role' => 'admin', 'must_change_password' => false]);
    $student = User::factory()->create(['role' => 'student', 'must_change_password' => false]);

    $feedback = UatFeedback::create([
        'user_id' => $student->id,
        'page_url' => 'http://localhost/page-x',
        'rating' => 3,
        'category' => 'unclear_feature',
        'description' => 'What does this button do?',
        'status' => 'open',
        'priority' => 'medium'
    ]);

    $response = $this->actingAs($admin)->post(route('admin.uat.feedback.update-status', $feedback->id), [
        'status' => 'in_progress',
        'priority' => 'high',
        'developer_notes' => 'Investigating implementation details.'
    ]);

    $response->assertRedirect();
    
    $freshFeedback = $feedback->fresh();
    expect($freshFeedback->status)->toEqual('in_progress');
    expect($freshFeedback->priority)->toEqual('high');
    expect($freshFeedback->developer_notes)->toEqual('Investigating implementation details.');
});

test('admin can access UAT feedback reports and see aggregated data', function () {
    $admin = User::factory()->create(['role' => 'admin', 'must_change_password' => false]);
    $student = User::factory()->create(['role' => 'student', 'must_change_password' => false]);

    UatFeedback::create([
        'user_id' => $student->id,
        'page_url' => 'http://localhost/page-1',
        'rating' => 5,
        'category' => 'bug',
        'description' => 'Test bug 1',
        'status' => 'open'
    ]);

    UatFeedback::create([
        'user_id' => $student->id,
        'page_url' => 'http://localhost/page-1',
        'rating' => 3,
        'category' => 'slow',
        'description' => 'Test slow 1',
        'status' => 'fixed'
    ]);

    $response = $this->actingAs($admin)->get(route('admin.uat.feedback.report'));
    
    $response->assertOk();
    $response->assertSee('Laporan Kualitatif Pengujian Pengguna (UAT)');
    
    // Assert summary stats are present
    $response->assertSee('Total Laporan Masuk');
    $response->assertSee('Skor Kepuasan Pengguna');
    $response->assertSee('Tingkat Penyelesaian');
});
