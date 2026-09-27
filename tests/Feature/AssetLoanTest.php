<?php

/** @var TestCase|stdClass $this */

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetLoan;
use App\Models\AssetMaintenance;
use App\Models\Room;
use App\Models\User;
use Tests\TestCase;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->wakaSarpras = User::factory()->create(['role' => 'waka_sarpras']);
    $this->kepsek = User::factory()->create(['role' => 'kepala_sekolah']);
    $this->guru = User::factory()->create(['role' => 'guru']);
    $this->stafTu = User::factory()->create(['role' => 'staf_tu']);
    $this->student = User::factory()->create(['role' => 'student']);
    $this->parent = User::factory()->create(['role' => 'parent']);

    $this->room = Room::create([
        'name' => 'Lab Komputer 1',
        'location' => 'Gedung Utara Lantai 3',
    ]);

    $this->category = AssetCategory::create([
        'name' => 'Elektronik',
        'code' => 'ELK',
    ]);

    $this->asset = Asset::create([
        'asset_code' => 'AST-ELK-001',
        'name' => 'Proyektor Epson X50',
        'category_id' => $this->category->id,
        'room_id' => $this->room->id,
        'purchase_date' => now()->subYears(2),
        'purchase_price' => 10000000.00,
        'useful_life_years' => 5,
        'is_borrowable' => true,
        'condition' => 'good',
        'status' => 'available',
        'created_by' => $this->admin->id,
    ]);
});

test('unauthorized roles (students and parents) are blocked from accessing loans and depreciation', function () {
    // Student blocked
    $this->actingAs($this->student)->get(route('assets.loans.index'))->assertStatus(403);
    $this->actingAs($this->student)->get(route('assets.loans.create'))->assertStatus(403);
    $this->actingAs($this->student)->get(route('assets.depreciation'))->assertStatus(403);

    // Parent blocked
    $this->actingAs($this->parent)->get(route('assets.loans.index'))->assertStatus(403);
    $this->actingAs($this->parent)->get(route('assets.depreciation'))->assertStatus(403);
});

test('guru can submit a loan request for a borrowable and available asset', function () {
    $response = $this->actingAs($this->guru)->post(route('assets.loans.store'), [
        'asset_id' => $this->asset->id,
        'expected_return_at' => now()->addDays(5)->format('Y-m-d H:i:s'),
        'purpose' => 'Presentasi materi Fisika di kelas XI IPA 1',
    ]);

    $response->assertRedirect(route('assets.loans.index'));

    $this->assertDatabaseHas('asset_loans', [
        'asset_id' => $this->asset->id,
        'borrower_id' => $this->guru->id,
        'status' => 'pending',
        'purpose' => 'Presentasi materi Fisika di kelas XI IPA 1',
    ]);
});

test('guru cannot borrow a non-borrowable or unavailable asset', function () {
    // Make asset non-borrowable
    $this->asset->update(['is_borrowable' => false]);

    $response = $this->actingAs($this->guru)->post(route('assets.loans.store'), [
        'asset_id' => $this->asset->id,
        'expected_return_at' => now()->addDays(2)->format('Y-m-d H:i:s'),
        'purpose' => 'Test borrow',
    ]);

    $response->assertSessionHas('error', 'Aset saat ini tidak dapat dipinjam.');

    // Restore borrowable but make it unavailable
    $this->asset->update(['is_borrowable' => true, 'status' => 'borrowed']);

    $response2 = $this->actingAs($this->guru)->post(route('assets.loans.store'), [
        'asset_id' => $this->asset->id,
        'expected_return_at' => now()->addDays(2)->format('Y-m-d H:i:s'),
        'purpose' => 'Test borrow 2',
    ]);

    $response2->assertSessionHas('error', 'Aset saat ini tidak dapat dipinjam.');
});

test('waka sarpras can approve a loan and asset status updates to borrowed', function () {
    // Guru requests first
    $loan = AssetLoan::create([
        'asset_id' => $this->asset->id,
        'borrower_id' => $this->guru->id,
        'expected_return_at' => now()->addDays(3),
        'purpose' => 'Belajar kelompok',
        'status' => 'pending',
    ]);

    $response = $this->actingAs($this->wakaSarpras)->post(route('assets.loans.approve', $loan->uuid), [
        'action' => 'approve',
    ]);

    $response->assertRedirect(route('assets.loans.index'));

    $loan->refresh();
    expect($loan->status)->toBe('approved');
    expect($loan->asset->status)->toBe('borrowed');
});

test('waka sarpras can record return of asset and updates condition and status', function () {
    // Create an approved/borrowed loan
    $loan = AssetLoan::create([
        'asset_id' => $this->asset->id,
        'borrower_id' => $this->guru->id,
        'expected_return_at' => now()->addDays(3),
        'purpose' => 'Belajar kelompok',
        'status' => 'approved',
    ]);

    // Explicitly update asset status to borrowed as hooks would do
    $this->asset->update(['status' => 'borrowed']);

    $response = $this->actingAs($this->wakaSarpras)->post(route('assets.loans.return', $loan->uuid), [
        'return_condition' => 'damaged',
        'return_notes' => 'Lensa agak berdebu dan gores tipis',
    ]);

    $response->assertRedirect(route('assets.loans.index'));

    $loan->refresh();
    expect($loan->status)->toBe('returned');
    expect($loan->return_condition)->toBe('damaged');
    expect($loan->asset->status)->toBe('available');
    expect($loan->asset->condition)->toBe('damaged');
});

test('straight-line depreciation correctly calculates asset book value', function () {
    // Total price = 10,000,000
    // Useful life = 5 years (5 * 365 = 1825 days)
    // Purchase date = 2 years ago (2 * 365 = 730 days)
    // Depreciation per day = 10,000,000 / 1825 = 5479.452
    // Accumulated depreciation = 5479.452 * 730 = 3,999,999.96
    // Current value = 10,000,000 - 3,999,999.96 = 6,000,000.04

    $currentVal = $this->asset->current_value;

    // Let's assert that the calculated current value is around 6,000,000 (allowing minor leap year day diffs)
    expect((float) $currentVal)->toBeGreaterThan(5900000.00);
    expect((float) $currentVal)->toBeLessThan(6100000.00);

    // Check when asset is older than useful life
    $oldAsset = Asset::create([
        'asset_code' => 'AST-OLD',
        'name' => 'Old PC',
        'category_id' => $this->category->id,
        'room_id' => $this->room->id,
        'purchase_date' => now()->subYears(6),
        'purchase_price' => 5000000.00,
        'useful_life_years' => 5,
        'is_borrowable' => false,
        'condition' => 'good',
        'status' => 'available',
        'created_by' => $this->admin->id,
    ]);

    expect((float) $oldAsset->current_value)->toBe(0.00);
});

test('active maintenance scheduling shifts parent status to repairing when in_progress', function () {
    $maintenance = AssetMaintenance::create([
        'asset_id' => $this->asset->id,
        'maintenance_date' => now()->format('Y-m-d'),
        'type' => 'preventif',
        'description' => 'Servis berkala lensa proyektor',
        'cost' => 150000.00,
        'vendor' => 'Epson Service Center',
        'status' => 'in_progress',
        'handled_by' => $this->wakaSarpras->id,
    ]);

    $this->asset->refresh();
    expect($this->asset->status)->toBe('repairing');

    // Complete the maintenance
    $maintenance->update([
        'status' => 'completed',
    ]);

    $this->asset->refresh();
    expect($this->asset->status)->toBe('available');
});
