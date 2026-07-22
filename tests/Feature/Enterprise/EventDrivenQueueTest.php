<?php

use App\Events\ExamCompleted;
use App\Events\PaymentReceived;
use App\Events\StudentGraduated;
use App\Jobs\GeneratePdfReportJob;
use App\Jobs\ProcessCbtScoreJob;
use App\Jobs\SendHighPriorityNotificationJob;
use App\Listeners\CalculateCbtScore;
use App\Listeners\GenerateAlumniRecord;
use App\Listeners\LogJournalEntry;
use App\Listeners\NotifyParentsViaWhatsApp;
use App\Listeners\UpdateBillingStatus;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->student = User::factory()->create(['role' => 'student']);
});

test('events dispatch to expected listeners', function () {
    Event::fake();

    Event::assertListening(StudentGraduated::class, GenerateAlumniRecord::class);
    Event::assertListening(StudentGraduated::class, NotifyParentsViaWhatsApp::class);
    Event::assertListening(PaymentReceived::class, UpdateBillingStatus::class);
    Event::assertListening(PaymentReceived::class, LogJournalEntry::class);
    Event::assertListening(ExamCompleted::class, CalculateCbtScore::class);
});

test('jobs assign to correct queue priority channels', function () {
    Queue::fake();

    SendHighPriorityNotificationJob::dispatch('0812345678', 'Test WA');
    Queue::assertPushedOn('high', SendHighPriorityNotificationJob::class);

    ProcessCbtScoreJob::dispatch(123);
    Queue::assertPushedOn('default', ProcessCbtScoreJob::class);

    GeneratePdfReportJob::dispatch('Report', 456);
    Queue::assertPushedOn('low', GeneratePdfReportJob::class);
});

test('admin can view queue monitoring dashboard', function () {
    $response = $this->actingAs($this->admin)->get(route('admin.observability.queues.index'));
    $response->assertStatus(200);
    $response->assertSee('Event-Driven Queue');
    $response->assertSee('Dead Letter Queue');
});

test('admin can dispatch demo jobs to queue pipelines', function () {
    Queue::fake();

    $response = $this->actingAs($this->admin)->post(route('admin.observability.queues.dispatch'), [
        'queue_priority' => 'high',
    ]);
    $response->assertStatus(302);
    Queue::assertPushedOn('high', SendHighPriorityNotificationJob::class);
});

test('admin can retry failed job from dead letter queue', function () {
    $failedId = DB::table('failed_jobs')->insertGetId([
        'uuid' => (string) \Illuminate\Support\Str::uuid(),
        'connection' => 'database',
        'queue' => 'high',
        'payload' => json_encode(['job' => 'App\\Jobs\\SendHighPriorityNotificationJob']),
        'exception' => 'Test Exception',
        'failed_at' => now(),
    ]);

    $response = $this->actingAs($this->admin)->post(route('admin.observability.queues.retry', $failedId));
    $response->assertStatus(302);
});

test('admin can delete and flush failed jobs from dead letter queue', function () {
    $failedId = DB::table('failed_jobs')->insertGetId([
        'uuid' => (string) \Illuminate\Support\Str::uuid(),
        'connection' => 'database',
        'queue' => 'low',
        'payload' => json_encode(['job' => 'App\\Jobs\\GeneratePdfReportJob']),
        'exception' => 'Test Exception',
        'failed_at' => now(),
    ]);

    // Delete single job
    $responseDelete = $this->actingAs($this->admin)->delete(route('admin.observability.queues.delete', $failedId));
    $responseDelete->assertStatus(302);
    $this->assertDatabaseMissing('failed_jobs', ['id' => $failedId]);

    // Flush all
    $responseFlush = $this->actingAs($this->admin)->post(route('admin.observability.queues.flush'));
    $responseFlush->assertStatus(302);
    expect(DB::table('failed_jobs')->count())->toBe(0);
});
