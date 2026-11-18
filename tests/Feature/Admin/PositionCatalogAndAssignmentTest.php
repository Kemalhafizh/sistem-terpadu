<?php

use App\Models\Position;
use App\Models\PositionAssignment;
use App\Models\User;
use App\Models\WorkflowDefinition;
use App\Models\WorkflowRequest;
use App\Services\WorkflowService;
use App\Support\Access\RouteAccess;

test('migration seeds the 7 default school positions with their permissions', function () {
    $codes = Position::orderBy('code')->pluck('code')->all();

    expect($codes)->toContain(
        'kepala_sekolah',
        'waka_kurikulum',
        'waka_kesiswaan',
        'waka_sarpras',
        'waka_humas',
        'kepala_perpustakaan',
        'kepala_tu'
    );

    $kepsek = Position::where('code', 'kepala_sekolah')->firstOrFail();
    expect($kepsek->is_system)->toBeTrue()
        ->and($kepsek->permissionKeys())->toContain('pimpinan.mengesahkan');
});

test('a teacher assigned as Waka Kurikulum gains both guru and waka_kurikulum access during active period', function () {
    $teacher = User::factory()->create([
        'role' => 'guru',
        'account_type' => 'guru',
        'must_change_password' => false,
    ]);

    $wakaPosition = Position::where('code', 'waka_kurikulum')->firstOrFail();

    PositionAssignment::create([
        'position_id' => $wakaPosition->id,
        'user_id' => $teacher->id,
        'starts_on' => now()->subDays(10)->toDateString(),
        'ends_on' => now()->addDays(30)->toDateString(),
        'decree_number' => 'SK/001/2026',
        'is_acting' => false,
    ]);

    $teacher->refresh();

    expect($teacher->hasPermission('akademik.kelola_struktur'))->toBeTrue()
        ->and($teacher->effectiveRoles())->toContain('guru', 'waka_kurikulum')
        ->and(RouteAccess::allowsRoute($teacher, 'academic.cycle.school-years.store'))->toBeTrue();
});

test('an acting (Plt) position assignment automatically expires after ends_on', function () {
    $teacher = User::factory()->create([
        'role' => 'guru',
        'account_type' => 'guru',
        'must_change_password' => false,
    ]);

    $kepsekPosition = Position::where('code', 'kepala_sekolah')->firstOrFail();

    PositionAssignment::create([
        'position_id' => $kepsekPosition->id,
        'user_id' => $teacher->id,
        'starts_on' => now()->subDays(14)->toDateString(),
        'ends_on' => now()->subDay()->toDateString(),
        'is_acting' => true,
    ]);

    $teacher->refresh();

    expect($teacher->hasPermission('pimpinan.mengesahkan'))->toBeFalse()
        ->and($teacher->effectiveRoles())->not->toContain('kepala_sekolah')
        ->and(PositionAssignment::holderOn('kepala_sekolah', now()->subDays(5))?->id)->toBe($teacher->id)
        ->and(PositionAssignment::holderOn('kepala_sekolah', now())?->id)->toBeNull();
});

test('non-superadmin admin cannot grant or revoke sensitive permissions on a position', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'account_type' => 'karyawan',
        'must_change_password' => false,
    ]);

    $this->actingAs($admin)
        ->post(route('admin.positions.store'), [
            'name' => 'Bendahara Sekolah',
            'code' => 'bendahara_sekolah',
            'allowed_account_types' => ['karyawan', 'guru'],
            'permissions' => ['keuangan.kelola_tagihan'],
        ])
        ->assertForbidden();

    $superadmin = User::factory()->create([
        'role' => 'superadmin',
        'must_change_password' => false,
    ]);

    $this->actingAs($superadmin)
        ->post(route('admin.positions.store'), [
            'name' => 'Bendahara Sekolah',
            'code' => 'bendahara_sekolah',
            'allowed_account_types' => ['karyawan', 'guru'],
            'permissions' => ['keuangan.kelola_tagihan'],
        ])
        ->assertRedirect(route('admin.positions.index'));

    expect(Position::where('code', 'bendahara_sekolah')->exists())->toBeTrue();
});

test('kepala_sekolah system position cannot be deleted, deactivated, or stripped of pimpinan.mengesahkan', function () {
    $superadmin = User::factory()->create([
        'role' => 'superadmin',
        'must_change_password' => false,
    ]);

    $kepsek = Position::where('code', 'kepala_sekolah')->firstOrFail();

    $this->actingAs($superadmin)
        ->delete(route('admin.positions.destroy', $kepsek))
        ->assertSessionHasErrors('position');

    $this->actingAs($superadmin)
        ->put(route('admin.positions.update', $kepsek), [
            'name' => 'Kepala Sekolah',
            'allowed_account_types' => ['guru'],
            'permissions' => ['akademik.kelola_struktur'],
            'is_active' => true,
        ])
        ->assertSessionHasErrors('permissions');

    $this->actingAs($superadmin)
        ->put(route('admin.positions.update', $kepsek), [
            'name' => 'Kepala Sekolah',
            'allowed_account_types' => ['guru'],
            'permissions' => ['pimpinan.mengesahkan'],
            'is_active' => false,
        ])
        ->assertSessionHasErrors('is_active');
});

test('position used in active workflow definition cannot be deactivated', function () {
    $superadmin = User::factory()->create([
        'role' => 'superadmin',
        'must_change_password' => false,
    ]);

    WorkflowDefinition::create([
        'code' => 'leave_approval_test',
        'name' => 'Persetujuan Cuti',
        'description' => 'Alur persetujuan cuti',
        'entity_type' => User::class,
        'steps' => [
            ['step' => 1, 'name' => 'Verifikasi Waka', 'role' => 'waka_kurikulum', 'action_label' => 'Setujui'],
        ],
        'is_active' => true,
    ]);

    $waka = Position::where('code', 'waka_kurikulum')->firstOrFail();

    $this->actingAs($superadmin)
        ->put(route('admin.positions.update', $waka), [
            'name' => 'Waka Kurikulum',
            'allowed_account_types' => ['guru'],
            'permissions' => $waka->permissionKeys(),
            'is_active' => false,
        ])
        ->assertSessionHasErrors('is_active');
});

test('workflow service blocks self-approval even when requester holds the approver position', function () {
    $dualRoleTeacher = User::factory()->create([
        'role' => 'guru',
        'account_type' => 'guru',
        'must_change_password' => false,
    ]);

    $otherWaka = User::factory()->create([
        'role' => 'guru',
        'account_type' => 'guru',
        'must_change_password' => false,
    ]);

    $wakaPosition = Position::where('code', 'waka_kurikulum')->firstOrFail();

    foreach ([$dualRoleTeacher, $otherWaka] as $u) {
        PositionAssignment::create([
            'position_id' => $wakaPosition->id,
            'user_id' => $u->id,
            'starts_on' => now()->subDay()->toDateString(),
        ]);
    }

    WorkflowDefinition::create([
        'code' => 'rkas_test',
        'name' => 'Persetujuan RKAS',
        'description' => 'Tes Persetujuan',
        'entity_type' => User::class,
        'steps' => [
            ['step' => 1, 'name' => 'Persetujuan Waka', 'role' => 'waka_kurikulum', 'action_label' => 'Setujui'],
        ],
        'is_active' => true,
    ]);

    $service = app(WorkflowService::class);
    $request = $service->submit('rkas_test', $dualRoleTeacher, $dualRoleTeacher, 'Pengajuan dari pemegang jabatan ganda');

    expect($request->canBeActedBy($dualRoleTeacher->fresh()))->toBeFalse()
        ->and($request->canBeActedBy($otherWaka->fresh()))->toBeTrue();

    expect(fn () => $service->approve($request, $dualRoleTeacher->fresh(), 'Setuju sendiri'))
        ->toThrow(RuntimeException::class, 'Penyusun atau pengaju tidak diizinkan menyetujui pengajuannya sendiri.');

    $approved = $service->approve($request, $otherWaka->fresh(), 'Disetujui pejabat lain');
    expect($approved->status)->toBe(WorkflowRequest::STATUS_APPROVED);
});
