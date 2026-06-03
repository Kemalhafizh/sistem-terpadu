<?php

namespace Tests\Feature\Workflow;

use App\Events\NewNotificationEvent;
use App\Models\User;
use App\Models\WorkflowApproval;
use App\Models\WorkflowDefinition;
use App\Models\WorkflowRequest;
use App\Services\WorkflowService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;

/**
 * Lightweight self-contained Polymorphic Model for testing.
 */
class TestApprovalModel extends Model
{
    protected $table = 'test_approval_models';
    protected $fillable = ['status', 'approved_by'];
}

beforeEach(function () {
    // Fake events to avoid WebSocket Reverb side effects
    Event::fake([NewNotificationEvent::class]);

    // Create the temporary polymorphic table
    if (!Schema::hasTable('test_approval_models')) {
        Schema::create('test_approval_models', function (Blueprint $table) {
            $table->id();
            $table->string('status')->default('draft');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamps();
        });
    }

    // Create default test workflow definition
    $this->definition = WorkflowDefinition::create([
        'name' => 'Leave Approval Test',
        'code' => 'leave_approval_test',
        'description' => 'Test workflow definition for testing multi-step approvals.',
        'steps' => [
            ['order' => 1, 'role' => 'waka_kurikulum'],
            ['order' => 2, 'role' => 'kepala_sekolah'],
        ],
        'is_active' => true,
    ]);

    // Instantiate service
    $this->service = app(WorkflowService::class);
});

test('workflow service can initiate polymorphic request successfully', function () {
    // Create at least one user with 'waka_kurikulum' role so notification event is dispatched
    User::factory()->create(['role' => 'waka_kurikulum']);
    
    $requester = User::factory()->create(['role' => 'guru']);
    $parentModel = TestApprovalModel::create(['status' => 'draft']);

    $request = $this->service->initiate(
        'leave_approval_test',
        $requester,
        $parentModel,
        'Minta cuti 3 hari untuk keperluan keluarga.'
    );

    // Verify workflow request properties
    expect($request)->toBeInstanceOf(WorkflowRequest::class);
    expect($request->status)->toBe('pending');
    expect($request->current_step)->toBe(1);
    expect($request->notes)->toBe('Minta cuti 3 hari untuk keperluan keluarga.');
    expect($request->requestable_id)->toBe($parentModel->id);
    expect($request->requestable_type)->toBe(TestApprovalModel::class);

    // Verify parent model status updated
    $parentModel->refresh();
    expect($parentModel->status)->toBe('pending');

    // Verify workflow approval steps are created
    expect($request->approvals()->count())->toBe(2);
    expect($request->approvals()->where('step_order', 1)->first()->role)->toBe('waka_kurikulum');
    expect($request->approvals()->where('step_order', 1)->first()->decision)->toBe('pending');
    expect($request->approvals()->where('step_order', 2)->first()->role)->toBe('kepala_sekolah');
    expect($request->approvals()->where('step_order', 2)->first()->decision)->toBe('pending');

    // Verify NewNotificationEvent was dispatched for step 1 approvers
    Event::assertDispatched(NewNotificationEvent::class);
});

test('unauthorized users are blocked from approving or rejecting steps', function () {
    $requester = User::factory()->create(['role' => 'guru']);
    $unauthorizedUser = User::factory()->create(['role' => 'student']);
    $parentModel = TestApprovalModel::create(['status' => 'draft']);

    $request = $this->service->initiate('leave_approval_test', $requester, $parentModel, 'Notes');

    // Attempting service approval directly should abort
    expect(fn() => $this->service->approve($request, $unauthorizedUser, 'Remark'))
        ->toThrow(\Symfony\Component\HttpKernel\Exception\HttpException::class);

    // Attempting service rejection directly should abort
    expect(fn() => $this->service->reject($request, $unauthorizedUser, 'Remark'))
        ->toThrow(\Symfony\Component\HttpKernel\Exception\HttpException::class);
});

test('workflow moves sequentially to the next step upon approval', function () {
    $requester = User::factory()->create(['role' => 'guru']);
    $wakaUser = User::factory()->create(['role' => 'waka_kurikulum']);
    $parentModel = TestApprovalModel::create(['status' => 'draft']);

    $request = $this->service->initiate('leave_approval_test', $requester, $parentModel, 'Notes');

    // Verify state before approval
    expect($request->current_step)->toBe(1);
    expect($request->status)->toBe('pending');

    // Approve Step 1
    $this->service->approve($request, $wakaUser, 'Setuju langkah 1.');

    // Refresh state
    $request->refresh();

    // Verify state advanced to step 2
    expect($request->current_step)->toBe(2);
    expect($request->status)->toBe('in_review');

    // Verify Step 1 Approval log is saved
    $step1Log = WorkflowApproval::where('request_id', $request->id)->where('step_order', 1)->first();
    expect($step1Log->decision)->toBe('approved');
    expect($step1Log->approver_id)->toBe($wakaUser->id);
    expect($step1Log->remarks)->toBe('Setuju langkah 1.');
    expect($step1Log->decided_at)->not->toBeNull();

    // Verify Step 2 remains pending
    $step2Log = WorkflowApproval::where('request_id', $request->id)->where('step_order', 2)->first();
    expect($step2Log->decision)->toBe('pending');
});

test('final step approval completes workflow and updates parent status', function () {
    $requester = User::factory()->create(['role' => 'guru']);
    $wakaUser = User::factory()->create(['role' => 'waka_kurikulum']);
    $kepsekUser = User::factory()->create(['role' => 'kepala_sekolah']);
    $parentModel = TestApprovalModel::create(['status' => 'draft']);

    $request = $this->service->initiate('leave_approval_test', $requester, $parentModel, 'Notes');

    // Approve Step 1
    $this->service->approve($request, $wakaUser, 'OK 1');

    // Approve Step 2 (Final step)
    $request = $request->fresh();
    $this->service->approve($request, $kepsekUser, 'Disetujui penuh.');

    // Refresh states
    $request->refresh();
    $parentModel->refresh();

    // Verify request completed
    expect($request->status)->toBe('approved');
    expect($request->current_step)->toBe(2);

    // Verify parent model status updated
    expect($parentModel->status)->toBe('approved');

    // Verify both approval steps marked approved
    $step2Log = WorkflowApproval::where('request_id', $request->id)->where('step_order', 2)->first();
    expect($step2Log->decision)->toBe('approved');
    expect($step2Log->approver_id)->toBe($kepsekUser->id);
});

test('rejection terminates the entire workflow instantly', function () {
    $requester = User::factory()->create(['role' => 'guru']);
    $wakaUser = User::factory()->create(['role' => 'waka_kurikulum']);
    $parentModel = TestApprovalModel::create(['status' => 'draft']);

    $request = $this->service->initiate('leave_approval_test', $requester, $parentModel, 'Notes');

    // Reject Step 1
    $this->service->reject($request, $wakaUser, 'Ditolak, berkas kurang lengkap.');

    // Refresh states
    $request->refresh();
    $parentModel->refresh();

    // Verify request terminated as rejected
    expect($request->status)->toBe('rejected');
    expect($request->current_step)->toBe(1); // Stays on rejected step

    // Verify parent model marked rejected
    expect($parentModel->status)->toBe('rejected');

    // Verify log updated
    $step1Log = WorkflowApproval::where('request_id', $request->id)->where('step_order', 1)->first();
    expect($step1Log->decision)->toBe('rejected');
    expect($step1Log->remarks)->toBe('Ditolak, berkas kurang lengkap.');
});
