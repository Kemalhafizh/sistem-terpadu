<?php

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Room;
use App\Models\User;
use App\Models\AssetMaintenanceTicket;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->teacher = User::factory()->create(['role' => 'guru']);

    $room = Room::create([
        'name' => 'Lab Komputer 1',
        'location' => 'Gedung A',
    ]);

    $category = AssetCategory::create([
        'name' => 'Elektronik',
        'code' => 'ELK',
    ]);

    $this->asset = Asset::create([
        'uuid' => (string) \Illuminate\Support\Str::uuid(),
        'asset_code' => 'AST-TEST-999',
        'name' => 'Proyektor BenQ',
        'category_id' => $category->id,
        'room_id' => $room->id,
        'purchase_date' => now()->subYear(),
        'purchase_price' => 5000000.00,
        'useful_life_years' => 5,
        'is_borrowable' => true,
        'condition' => 'good',
        'status' => 'available',
        'created_by' => $this->admin->id,
    ]);
});

test('user can scan asset QR code to view details', function () {
    $response = $this->actingAs($this->teacher)->get(route('admin.assets.maintenance.scan', $this->asset->uuid));
    $response->assertStatus(200);
    $response->assertSee('Proyektor BenQ');
    $response->assertSee('AST-TEST-999');
});

test('user can submit a maintenance ticket report', function () {
    Storage::fake('public');
    $photo = UploadedFile::fake()->image('damage.jpg');

    $response = $this->actingAs($this->teacher)->post(route('admin.assets.maintenance.store'), [
        'asset_id' => $this->asset->id,
        'issue_description' => 'Lampu proyektor redup dan berkedip.',
        'priority' => 'high',
        'photo' => $photo,
    ]);

    $response->assertStatus(302);
    
    $this->assertDatabaseHas('asset_maintenance_tickets', [
        'asset_id' => $this->asset->id,
        'issue_description' => 'Lampu proyektor redup dan berkedip.',
        'priority' => 'high',
        'status' => 'pending',
    ]);

    $ticket = AssetMaintenanceTicket::first();
    expect($ticket->photo_evidence)->not->toBeNull();
    Storage::disk('public')->assertExists($ticket->photo_evidence);
});

test('admin can view kanban board and ticket detail', function () {
    $ticket = AssetMaintenanceTicket::create([
        'asset_id' => $this->asset->id,
        'reported_by' => $this->teacher->id,
        'issue_description' => 'Lampu proyektor mati.',
        'priority' => 'critical',
        'status' => 'pending',
    ]);

    // Check dashboard
    $responseBoard = $this->actingAs($this->admin)->get(route('admin.assets.maintenance.index'));
    $responseBoard->assertStatus(200);
    $responseBoard->assertSee('Lampu proyektor mati');

    // Check detail
    $responseDetail = $this->actingAs($this->admin)->get(route('admin.assets.maintenance.show', $ticket->id));
    $responseDetail->assertStatus(200);
    $responseDetail->assertSee('Lampu proyektor mati');
});

test('admin can resolve a ticket which updates asset condition to good and available', function () {
    $ticket = AssetMaintenanceTicket::create([
        'asset_id' => $this->asset->id,
        'reported_by' => $this->teacher->id,
        'issue_description' => 'Lampu proyektor mati.',
        'priority' => 'critical',
        'status' => 'pending',
    ]);

    // Update status to resolved
    $response = $this->actingAs($this->admin)->post(route('admin.assets.maintenance.update-status', $ticket->id), [
        'status' => 'resolved',
        'resolution_notes' => 'Lampu proyektor diganti baru.',
    ]);

    $response->assertStatus(302);
    $response->assertRedirect(route('admin.assets.maintenance.index'));

    $ticket->refresh();
    expect($ticket->status)->toBe('resolved');
    expect($ticket->resolved_by)->toBe($this->admin->id);
    expect($ticket->resolution_notes)->toBe('Lampu proyektor diganti baru.');

    $this->asset->refresh();
    expect($this->asset->condition)->toBe('good');
    expect($this->asset->status)->toBe('available');
});

test('admin can scrap a ticket which updates asset condition to broken and lost', function () {
    $ticket = AssetMaintenanceTicket::create([
        'asset_id' => $this->asset->id,
        'reported_by' => $this->teacher->id,
        'issue_description' => 'Lampu proyektor meledak.',
        'priority' => 'critical',
        'status' => 'pending',
    ]);

    // Update status to scrapped
    $response = $this->actingAs($this->admin)->post(route('admin.assets.maintenance.update-status', $ticket->id), [
        'status' => 'scrapped',
        'resolution_notes' => 'Aset rusak parah, dibuang.',
    ]);

    $response->assertStatus(302);

    $ticket->refresh();
    expect($ticket->status)->toBe('scrapped');

    $this->asset->refresh();
    expect($this->asset->condition)->toBe('broken');
    expect($this->asset->status)->toBe('lost');
});
